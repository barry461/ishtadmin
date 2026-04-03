<?php

use Grafika\Grafika;

class UploadController extends BackendBaseController
{
    /**
     * 上传文件
     * @throws Exception
     * @author xiongba
     * @date 2019-11-19 17:01:39
     */
    public function uploadAction()
    {
        //_FILES字段的名称
        $fileName = 'file';
        $position = $_POST['position'] ?? 'upload';
        $name = $_FILES[$fileName]['name'];
        // var_dump($name);exit;
        $extension = pathinfo($name, PATHINFO_EXTENSION);
        if (!in_array(strtolower($extension), ['mp4', 'gif', 'png', 'jpeg', 'jpg', 'swf', 'icon', 'm3u8'])) {
            return $this->ajaxError('类型错误', -1);
        }


        $id = uniqid();
        $image_name = $id . "." . $extension;
        $image_path = APP_PATH . '/storage/data/images/';
        // echo $image_path;die();
        $image_file = $image_path . $image_name;
        /** @var LibUpload $uploadObject */
        $uploadObject = new  LibUpload;
        $uploadObject->init($image_path, $fileName, true);
        $uploadObject->setNewName($image_name);
        $result = $uploadObject->doUpload();
        if (!$result || !file_exists($image_file)) {
            return $this->ajaxError('文件上传本地失败', -1);
        }
        if (file_exists($image_file)) {
            list($width, $height) = getimagesize($image_file);
        } else {
            $height = $width = 0;
        }
        $return = LibUpload::upload2Remote($id, $image_file, $position);
        // trigger_log("执行到了这里 1 ".json_encode($return));
        unlink($image_file);
        if ($return['code'] == 1) {
            $cover = $return['msg'];
            $info = array(
                'url' => $cover,
                'src' => url_image($cover),
                'width' => $width,
                'height' => $height,
            );
            return $this->ajaxSuccess($info, 200);
        } else {
            return $this->ajaxError('文件上传服务器失败', -1, $return);
        }
    }


    /**
     * 上传图片到本地服务器
     * @return void
     */
    public function uploadLocalAction()
    {
        try {
        

            $fileName = 'file';
            $name = $_FILES[$fileName]['name'];
            $extension = pathinfo($name, PATHINFO_EXTENSION);
            
            if (!in_array(strtolower($extension), ['png', 'jpeg', 'jpg', 'gif'])) {
                return $this->ajaxError('只允许上传图片文件', -1);
            }

            $datePath = date('Y/m/d');
            $uploadPath = APP_PATH . '/public/upload/' . $datePath . '/';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            $newFileName = md5(uniqid() . microtime()) . '.' . $extension;
            $fullPath = $uploadPath . $newFileName;
            
            if (!move_uploaded_file($_FILES[$fileName]['tmp_name'], $fullPath)) {
                return $this->ajaxError('文件上传失败', -1);
            }

            list($width, $height) = getimagesize($fullPath);
            
            $relativePath = '/upload/' . $datePath . '/' . $newFileName;
            $info = [
                'url' => $relativePath,
                'src' => $relativePath,
                'width' => $width,
                'height' => $height,
                'name' => $name
            ];
             $syncResult = fpm_cluster()->uploadStaticFile($relativePath, $fullPath);
             $info['sync'] = $syncResult;//同步结果

            
           
            return $this->ajaxSuccess($info, 200);
        } catch (\Exception $e) {
            return $this->ajaxError($e->getMessage(), -1);
        }
    }

    public function uploadBatAction()
    {
        //_FILES字段的名称
        $fileName = 'file';
        $position = $_POST['position'] ?? 'upload';
        $name = $_FILES[$fileName]['name'];
        $extension = pathinfo($name, PATHINFO_EXTENSION);
        if (!in_array($extension, ['mp4', 'gif', 'png', 'jpeg', 'jpg', 'swf', 'icon', 'm3u8'])) {
            return $this->ajaxError('类型错误', -1);
        }
        $ids[] = $id = uniqid();
        $image_name = $id . "." . $extension;
        $image_path = APP_PATH . '/storage/data/images/';
        $image_file = $image_path . $image_name;
        $cover[] = $this->_upload($image_path, $fileName, $image_name, $id, $image_file, $position);
        if (!$cover[0]) {
            return $this->ajaxError('文件上传服务器失败');
        }
        for ($i = 1; $i <= 2; $i++) {
            $ids[] = $id = uniqid();
            $editor = Grafika::createEditor();
            $editor->open($image, $image_path . $image_name);
            $editor->resizeFit($image, 350 * $i, 350 * $i);
            $editor->save($image, $image_path . $id . '.' . $extension);
            $cover[] = $this->_upload(APP_PATH . '/storage/data/images/', $fileName, $id . "." . $extension, $id, APP_PATH . '/storage/data/images/' . $id . "." . $extension, $position);
        }
        foreach ($ids as $id) {
            unlink(APP_PATH . '/storage/data/images/' . $id . "." . $extension);
        }
        $info = [
            'url' => implode(',', $cover)
        ];
        return $this->ajaxSuccess($info, 200);
    }

    private function _upload($image_path, $fileName, $image_name, $id, $image_file, $position)
    {
        /** @var LibUpload $uploadObject */
        $uploadObject = new  LibUpload;
        $uploadObject->init($image_path, $fileName, true);
        $uploadObject->setNewName($image_name);
        $uploadObject->doUpload();
        $return = LibUpload::upload2Remote($id, $image_file, $position);
        if ($return['code'] == 1) {
            return $return['msg'];
        } else {
            return '';
        }
    }

    public function uploadMp4Action()
    {
        
        $fileName = 'file';
        $name = $_FILES[$fileName]['name'];
        $extension = pathinfo($name, PATHINFO_EXTENSION);

        if (!in_array($extension, ['mp4', 'm3u8'])) {
            return $this->ajaxError('类型错误', -1);
        }
        $id = uniqid();
        $image_name = $id . "." . $extension;
        $image_path = APP_PATH . '/storage/data/video/';
        $image_file = $image_path . $image_name;
        /** @var LibUpload $uploadObject */
        $uploadObject = new  LibUpload;
        $uploadObject->init($image_path, $fileName, false);
        $uploadObject->setNewName($image_name);
        $result = $uploadObject->doUpload();
        if (!$result || !file_exists($image_file)) {
            return $this->ajaxError('文件上传本地失败', -1);
        }
        $return = LibUpload::uploadMp42Remote($id, $image_file);
        unlink($image_file);
        if ($return['code'] == 1) {
            $cover = $return['msg'];
            $info = array(
                'url' => $cover
            );
            return $this->ajaxSuccess($info, 200);
        } else {
            return $this->ajaxError('文件上传服务器失败', -1, $return);
        }
    }


    /**
     * @return bool
     * @throws Exception
     * @author xiongba
     * @date 2020-01-01 16:03:04
     */
    public function upload2Action()
    {
        $_POST['position'] = 'upload';
        $this->uploadAction();
        $data = $this->getResponse()->getBody();
        $data = json_decode($data, 1);

        if ($data['code'] == 200) {
            $url = $data['data']['url'];
            $title = basename($url);
            return $this->ajaxSuccess([
                'src' => url_image($url),
                'title' => $title,
            ], 0);
        } else {
            return $this->ajaxError('上传失败');
        }


    }

    public function upload3Action()
    {
        $_POST['position'] = 'upload';
        $this->uploadAction();
        $data = $this->getResponse()->getBody();
        $data = json_decode($data, 1);

        if ($data['code'] == 200) {
            $url = $data['data']['url'];
            $url = url_image($url);
            $size = getimagesize($url);
            return $this->_jsonResponse([
                'code' => 0,
                'data' => [
                    'url' => $url,
                    'height' => $size[1],
                    'width' => $size[0]
                ],
                'msg'=>'成功',
            ]);
        } else {
            return $this->_jsonResponse([
                'code' => 1,
                'data' => '',
                'msg' => '上传失败',
            ]);
        }
    }

}