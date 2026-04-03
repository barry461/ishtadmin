<?php

use service\OptionService;

/**
 * 主题图片管理 API 控制器
 * 提供logo、占位图、中转页logo、中转页占位图、网站图标、评论头像的保存和获取功能
 */
class ThemeController extends AdminV2BaseController
{
    /**
     * 保存主题图片
     * POST /adminv2/theme/saveImages
     * 
     * 参数（可选，传入哪个就修改哪个，不传入则不修改）:
     * - logo_url: 网站logo图片路径
     * - img_zwimg: 占位图图片路径
     * - zz_logo: 中转页logo图片路径
     * - zz_zwimg: 中转页占位图图片路径
     * - favicon_icon: 网站图标(ico)路径
     * - comment_avatar: 网站评论头像路径
     * 
     * 示例:
     * {
     *   "logo_url": "/upload/2024/01/01/logo.png",
     *   "img_zwimg": "/upload/2024/01/01/placeholder.png"
     * }
     */
    public function saveImagesAction()
    {
        try {
            $params = $this->data;
            
            // 检查是否有传入任何参数
            $imageFields = ['logo_url', 'img_zwimg', 'zz_logo', 'zz_zwimg', 'favicon_icon', 'comment_avatar'];
            $hasParams = false;
            foreach ($imageFields as $field) {
                if (isset($params[$field])) {
                    $hasParams = true;
                    break;
                }
            }
            
            if (!$hasParams) {
                return $this->validationError('请至少传入一个图片字段');
            }

            $optionser = new OptionService();
            $updated = [];
            $errors = [];

            // 使用事务确保数据一致性
            transaction(function () use ($params, $imageFields, $optionser, &$updated, &$errors) {
                foreach ($imageFields as $field) {
                    if (isset($params[$field])) {
                        $value = trim($params[$field]);
                        
                        // 允许空字符串来清空图片
                        if ($value === '') {
                            $value = null;
                        }
                        
                        // 验证图片路径格式（可选，如果传入的是URL也允许）
                        if ($value !== null && !preg_match('#^(/|https?://)#', $value)) {
                            $errors[] = "{$field} 格式不正确，应为相对路径或完整URL";
                            continue;
                        }
                        
                        try {
                            // 保存到options表
                            $result = $optionser->set($field, $value);
                            if ($result !== false) {
                                $updated[$field] = $value;
                            } else {
                                $errors[] = "{$field} 保存失败";
                            }
                        } catch (\Exception $e) {
                            $errors[] = "{$field} 保存失败: " . $e->getMessage();
                        }
                    }
                }
            });

            // 清除主题图片相关缓存
            yac()->delete("options:theme:images");

            if (!empty($errors)) {
                return $this->errorJson('部分图片保存失败', self::STATUS_ERROR, [
                    'updated' => $updated,
                    'errors' => $errors
                ]);
            }

            return $this->successMsg('图片保存成功', [
                'updated' => $updated,
                'count' => count($updated)
            ]);
        } catch (\Exception $e) {
            return $this->serverError('保存失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取主题图片
     * GET /adminv2/theme/getImages
     * 
     * 返回所有已设置的图片路径
     */
    public function getImagesAction()
    {
        try {
            // 使用缓存获取主题图片
            $result = yac()->fetch("options:theme:images", function () {
                $optionser = new OptionService();
                
                $images = [
                    'logo_url' => $optionser->get('logo_url') ?: '',
                    'img_zwimg' => $optionser->get('img_zwimg') ?: '',
                    'zz_logo' => $optionser->get('zz_logo') ?: '',
                    'zz_zwimg' => $optionser->get('zz_zwimg') ?: '',
                    'favicon_icon' => $optionser->get('favicon_icon') ?: '',
                    'comment_avatar' => $optionser->get('comment_avatar') ?: '',
                ];

                // 为每个图片生成完整URL（如果存在）
                $result = [];
                foreach ($images as $key => $value) {
                    $result[$key] = [
                        'url' => $value,
                        'src' => $value ? url_image($value) : '',
                        'exists' => !empty($value)
                    ];
                }

                // 添加字段说明
                $result['_fields'] = [
                    'logo_url' => '网站logo',
                    'img_zwimg' => '占位图',
                    'zz_logo' => '中转页logo',
                    'zz_zwimg' => '中转页占位图',
                    'favicon_icon' => '网站图标',
                    'comment_avatar' => '网站评论头像',
                ];

                return $result;
            });

            return $this->showJson($result);
        } catch (\Exception $e) {
            return $this->serverError('获取图片失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取单个图片
     * GET /adminv2/theme/getImage
     * 
     * 参数:
     * - field: 图片字段名 (logo_url, img_zwimg, zz_logo, zz_zwimg, favicon_icon, comment_avatar)
     */
    public function getImageAction()
    {
        $field = $this->data['field'] ?? '';
        
        $allowedFields = ['logo_url', 'img_zwimg', 'zz_logo', 'zz_zwimg', 'favicon_icon', 'comment_avatar'];
        if (!in_array($field, $allowedFields)) {
            return $this->validationError('无效的字段名，允许的值: ' . implode(', ', $allowedFields));
        }

        try {
            $optionser = new OptionService();
            $value = $optionser->get($field) ?: '';

            return $this->showJson([
                'field' => $field,
                'url' => $value,
                'src' => $value ? url_image($value) : '',
                'exists' => !empty($value)
            ]);
        } catch (\Exception $e) {
            return $this->serverError('获取图片失败: ' . $e->getMessage());
        }
    }

    /**
     * 删除图片（清空图片路径）
     * POST /adminv2/theme/deleteImage
     * 
     * 参数:
     * - field: 图片字段名 (logo_url, img_zwimg, zz_logo, zz_zwimg, favicon_icon, comment_avatar)
     */
    public function deleteImageAction()
    {
        $field = $this->data['field'] ?? '';
        
        $allowedFields = ['logo_url', 'img_zwimg', 'zz_logo', 'zz_zwimg', 'favicon_icon', 'comment_avatar'];
        if (!in_array($field, $allowedFields)) {
            return $this->validationError('无效的字段名，允许的值: ' . implode(', ', $allowedFields));
        }

        try {
            $optionser = new OptionService();
            $result = $optionser->set($field, '');

            if ($result !== false) {
                // 清除主题图片相关缓存
                yac()->delete("options:theme:images");

                return $this->successMsg('图片已删除', [
                    'field' => $field
                ]);
            } else {
                return $this->errorJson('删除失败');
            }
        } catch (\Exception $e) {
            return $this->serverError('删除失败: ' . $e->getMessage());
        }
    }
}

