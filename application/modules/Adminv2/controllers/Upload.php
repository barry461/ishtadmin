<?php

/**
 * 图片上传 API 控制器
 * 复用原Admin模块的上传逻辑，返回格式符合AdminV2标准
 */
class UploadController extends AdminV2BaseController
{
    /**
     * 上传图片到远程服务器
     * POST /adminv2/upload/uploadRemote
     * 
     * 参数:
     * - file: 上传的文件 (multipart/form-data)
     * - position: 存放位置 (可选，默认: upload)
     *   可选值: actors,ads,av,head,icons,lusir,pay,upload,xiao,youtube,im
     * 
     * 支持的文件类型: mp4, gif, png, jpeg, jpg, swf, icon, m3u8
     */
    public function uploadRemoteAction()
    {
        try {
            // 文件字段名称
            $fileName = 'file';

            // 检查文件是否存在
            if (!isset($_FILES[$fileName]) || $_FILES[$fileName]['error'] !== UPLOAD_ERR_OK) {
                return $this->validationError('请选择要上传的文件');
            }

            $position = $this->data['position'] ?? 'upload';
            $name = $_FILES[$fileName]['name'];
            $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));

            // 验证文件类型
            $allowedTypes = ['mp4', 'gif', 'png', 'jpeg', 'jpg', 'swf', 'icon', 'm3u8'];
            if (!in_array($extension, $allowedTypes)) {
                return $this->validationError('不支持的文件类型，仅支持: ' . implode(', ', $allowedTypes));
            }

            // 生成唯一ID和文件名
            $id = uniqid();
            $image_name = $id . "." . $extension;
            $image_path = APP_PATH . '/storage/data/images/';

            // 确保目录存在
            if (!is_dir($image_path)) {
                mkdir($image_path, 0777, true);
            }

            $image_file = $image_path . $image_name;

            // 初始化上传对象
            $uploadObject = new LibUpload();
            $uploadObject->init($image_path, $fileName, true);
            $uploadObject->setNewName($image_name);

            // 执行上传
            $result = $uploadObject->doUpload();
            if (!$result || !file_exists($image_file)) {
                return $this->serverError('文件上传本地失败');
            }

            // 获取图片尺寸
            $width = $height = 0;
            if (file_exists($image_file)) {
                $imageInfo = @getimagesize($image_file);
                if ($imageInfo !== false) {
                    list($width, $height) = $imageInfo;
                }
            }

            // 上传到远程服务器
            $return = LibUpload::upload2Remote($id, $image_file, $position);

            // 删除本地临时文件
            if (file_exists($image_file)) {
                @unlink($image_file);
            }

            // 检查上传结果
            if ($return['code'] == 1) {
                $cover = $return['msg'];
                $info = [
                    'url' => $cover,
                    'src' => url_image($cover),
                    'width' => $width,
                    'height' => $height,
                ];
                return $this->successMsg('上传成功', $info);
            } else {
                $errorMsg = $return['msg'] ?? '文件上传服务器失败';
                return $this->errorJson($errorMsg);
            }
        } catch (\Exception $e) {
            return $this->serverError('上传失败: ' . $e->getMessage());
        }
    }

    /**
     * 上传图片到本地服务器
     * POST /adminv2/upload/upload-local
     * 
     * 参数:
     * - file: 上传的文件 (multipart/form-data)
     * 
     * 支持的文件类型: png, jpeg, jpg, gif
     * 文件会保存到 /public/upload/YYYY/MM/DD/ 目录
     */
    public function uploadLocalAction()
    {
        try {
            $fileName = 'file';

            // 检查文件是否存在
            if (!isset($_FILES[$fileName]) || $_FILES[$fileName]['error'] !== UPLOAD_ERR_OK) {
                return $this->validationError('请选择要上传的文件');
            }

            $name = $_FILES[$fileName]['name'];
            $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));

            // 验证文件类型（仅图片）
            $allowedTypes = ['png', 'jpeg', 'jpg', 'gif'];
            if (!in_array($extension, $allowedTypes)) {
                return $this->validationError('只允许上传图片文件: ' . implode(', ', $allowedTypes));
            }

            // 创建按日期分组的目录
            $datePath = date('Y/m/d');
            $uploadPath = APP_PATH . '/public/upload/' . $datePath . '/';

            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            // 生成唯一文件名
            $newFileName = md5(uniqid() . microtime()) . '.' . $extension;
            $fullPath = $uploadPath . $newFileName;

            // 移动上传的文件
            if (!move_uploaded_file($_FILES[$fileName]['tmp_name'], $fullPath)) {
                return $this->serverError('文件上传失败');
            }

            // 获取图片尺寸
            $width = $height = 0;
            $imageInfo = @getimagesize($fullPath);
            if ($imageInfo !== false) {
                list($width, $height) = $imageInfo;
            }
            
            // 相对路径
            $relativePath = '/upload/' . $datePath . '/' . $newFileName;
            
            // 同步到集群（如果存在）
            $syncResult = null;
            if (function_exists('fpm_cluster')) {
                try {
                    $syncResult = fpm_cluster()->uploadStaticFile($relativePath, $fullPath);

                    // 重试三次 (失败的情况下)
                } catch (\Exception $e) {
                    // 同步失败不影响上传成功
                }
            }

            $baseUrl = trim(config('backend.base_url'),'/');
            
            $info = [
                'url' => $relativePath,
                'src' => $baseUrl.$relativePath.'?v=2',
                'width' => $width,
                'height' => $height,
                'name' => $name,
            ];

            if ($syncResult !== null) {
                $info['sync'] = $syncResult;
            }

            return $this->successMsg('上传成功', $info);
        } catch (\Exception $e) {
            return $this->serverError('上传失败: ' . $e->getMessage());
        }
    }

    /**
     * 批量上传图片
     * POST /adminv2/upload/upload-batch
     * 
     * 参数:
     * - files: 多个文件 (multipart/form-data)
     * - position: 存放位置 (可选，默认: upload)
     * 
     * 返回所有成功上传的文件信息
     */
    public function uploadBatchAction()
    {
        try {
            $position = $this->data['position'] ?? 'upload';
            $results = [];
            $errors = [];

            // 处理多个文件
            if (isset($_FILES['files']) && is_array($_FILES['files']['name'])) {
                // 多文件上传
                $fileCount = count($_FILES['files']['name']);
                for ($i = 0; $i < $fileCount; $i++) {
                    if ($_FILES['files']['error'][$i] !== UPLOAD_ERR_OK) {
                        $errors[] = "文件 " . ($i + 1) . " 上传失败";
                        continue;
                    }

                    $file = [
                        'name' => $_FILES['files']['name'][$i],
                        'type' => $_FILES['files']['type'][$i],
                        'tmp_name' => $_FILES['files']['tmp_name'][$i],
                        'error' => $_FILES['files']['error'][$i],
                        'size' => $_FILES['files']['size'][$i],
                    ];

                    $result = $this->processSingleFile($file, $position);
                    if ($result['success']) {
                        $results[] = $result['data'];
                    } else {
                        $errors[] = "文件 " . ($i + 1) . ": " . $result['error'];
                    }
                }
            } elseif (isset($_FILES['file'])) {
                // 单文件上传（兼容）
                $result = $this->processSingleFile($_FILES['file'], $position);
                if ($result['success']) {
                    $results[] = $result['data'];
                } else {
                    $errors[] = $result['error'];
                }
            } else {
                return $this->validationError('请选择要上传的文件');
            }

            return $this->showJson([
                'results' => $results,
                'errors' => $errors,
                'success_count' => count($results),
                'error_count' => count($errors),
            ]);
        } catch (\Exception $e) {
            return $this->serverError('批量上传失败: ' . $e->getMessage());
        }
    }

    /**
     * 处理单个文件上传
     * @param array $file 文件信息
     * @param string $position 存放位置
     * @return array
     */
    private function processSingleFile($file, $position)
    {
        try {
            $name = $file['name'];
            $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));

            // 验证文件类型
            $allowedTypes = ['mp4', 'gif', 'png', 'jpeg', 'jpg', 'swf', 'icon', 'm3u8'];
            if (!in_array($extension, $allowedTypes)) {
                return [
                    'success' => false,
                    'error' => '不支持的文件类型: ' . $extension
                ];
            }

            // 生成唯一ID和文件名
            $id = uniqid();
            $image_name = $id . "." . $extension;
            $image_path = APP_PATH . '/storage/data/images/';

            if (!is_dir($image_path)) {
                mkdir($image_path, 0777, true);
            }

            $image_file = $image_path . $image_name;

            // 移动文件到临时目录
            if (!move_uploaded_file($file['tmp_name'], $image_file)) {
                return [
                    'success' => false,
                    'error' => '文件移动失败'
                ];
            }

            // 获取图片尺寸
            $width = $height = 0;
            if (file_exists($image_file)) {
                $imageInfo = @getimagesize($image_file);
                if ($imageInfo !== false) {
                    list($width, $height) = $imageInfo;
                }
            }

            // 上传到远程服务器
            $return = LibUpload::upload2Remote($id, $image_file, $position);

            // 删除本地临时文件
            if (file_exists($image_file)) {
                @unlink($image_file);
            }

            if ($return['code'] == 1) {
                $cover = $return['msg'];
                return [
                    'success' => true,
                    'data' => [
                        'url' => $cover,
                        'src' => url_image($cover),
                        'width' => $width,
                        'height' => $height,
                        'name' => $name,
                    ]
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $return['msg'] ?? '文件上传服务器失败'
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}

