<?php

use service\OptionService;

/**
 * 页面设置和导航设置 API 控制器
 */
class PagesetController extends AdminV2BaseController
{
    /**
     * 获取导航设置
     * GET /adminv2/pageset/getNav
     * 
     * 返回所有导航相关的配置项
     */
    public function getNavAction()
    {
        $optionService = new OptionService();

        $data = [
            // 导航限制数量：头部显示最多的分类导航的最大数量（整数，默认 5）
            'max_navbar_menu_num' => $optionService->getSubKey('theme:Mirages', 'maxNavbarMenuNum') ?? 5,
            // 顶部图标导航：网站顶部工具栏图标导航配置（字符串，通常为 JSON 或特定格式）
            'head_nav' => $optionService->getSubKey('theme:Mirages', 'toolbarItems') ?? '',
            // 底部导航图标：网站底部导航图标配置（字符串，通常为 JSON 或特定格式）
            'foot_menu' => $optionService->getSubKey('plugin:FootMenu', 'foot_menu') ?? '',
            // 底部导航：网站底部导航链接配置（字符串，通常为 JSON 或特定格式）
            'foot_link' => $optionService->getSubKey('plugin:FootMenu', 'foot_link') ?? '',
            // 底部联系导航：网站底部联系方式导航配置（字符串，通常为 JSON 或特定格式）
            'contact_link' => $optionService->getSubKey('plugin:FootMenu', 'contact_link') ?? '',
            // 页脚法律声明导航：网站页脚法律声明相关链接配置（字符串，通常为 JSON 或特定格式）
            'legal_links' => $optionService->getSubKey('plugin:FootMenu', 'legal_links') ?? '',
            // 友情推荐链接：友情链接配置（JSON 字符串格式，支持字段：name、link、target、rel）
            'friend_links' => $optionService->getSubKey('plugin:FootMenu', 'friend_links') ?? '',
            // 底部描述：网站底部描述信息（字符串，支持 HTML 标签）
            'foot_desc' => $optionService->getSubKey('plugin:FootMenu', 'foot_desc') ?? '',
        ];

        return $this->showJson($data);
    }

    /**
     * 设置导航个数
     * POST /adminv2/pageset/setNavbarMenuNum
     * 
     * 参数：
     * - max_navbar_menu_num: 导航限制数量（头部显示最多的分类导航的最大数量，必填，整数，范围 1-20）
     */
    public function setNavbarMenuNumAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->errorJson('请求方式错误');
        }

        $num = isset($this->data['max_navbar_menu_num']) ? (int)$this->data['max_navbar_menu_num'] : 0;

        if ($num <= 0) {
            return $this->validationError('导航个数必须大于 0');
        }

        if ($num > 20) {
            return $this->validationError('导航个数不能超过 20');
        }

        try {
            transaction(function () use ($num) {
                $optionService = new OptionService();
                $optionService->setSubKey('theme:Mirages', 'maxNavbarMenuNum', $num);
            });

            // 清除相关缓存
            yac()->delete("options");
            yac()->delete("options:all");

            return $this->successMsg('设置成功');
        } catch (\Throwable $e) {
            return $this->errorJson('设置失败：' . $e->getMessage());
        }
    }

    /**
     * 保存导航设置
     * POST /adminv2/pageset/saveNav
     * 
     * 参数（全部可选，传哪个改哪个）：
     * - max_navbar_menu_num: 导航限制数量（头部显示最多的分类导航的最大数量）
     * - head_nav: 顶部图标导航
     * - foot_menu: 底部导航图标
     * - foot_link: 底部导航
     * - contact_link: 底部联系导航
     * - legal_links: 页脚法律声明导航
     * - friend_links: 友情推荐链接（JSON格式）
     * - foot_desc: 底部描述（支持HTML）
     */
    public function saveNavAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->errorJson('请求方式错误');
        }

        $params = $this->data ?? [];
        if (empty($params)) {
            return $this->validationError('未传入任何修改参数');
        }

        try {
            transaction(function () use ($params) {
                $optionService = new OptionService();

                if (isset($params['max_navbar_menu_num'])) {
                    $optionService->setSubKey('theme:Mirages', 'maxNavbarMenuNum', (int)$params['max_navbar_menu_num']);
                }

                if (isset($params['head_nav'])) {
                    $optionService->setSubKey('theme:Mirages', 'toolbarItems', trim((string)$params['head_nav']));
                }

                if (isset($params['foot_menu'])) {
                    $optionService->setSubKey('plugin:FootMenu', 'foot_menu', trim((string)$params['foot_menu']));
                }

                if (isset($params['foot_link'])) {
                    $optionService->setSubKey('plugin:FootMenu', 'foot_link', trim((string)$params['foot_link']));
                }

                if (isset($params['contact_link'])) {
                    $optionService->setSubKey('plugin:FootMenu', 'contact_link', trim((string)$params['contact_link']));
                }

                if (isset($params['legal_links'])) {
                    $optionService->setSubKey('plugin:FootMenu', 'legal_links', trim((string)$params['legal_links']));
                }

                if (isset($params['friend_links'])) {
                    // 验证 JSON 格式
                    $friendLinks = trim((string)$params['friend_links']);
                    if ($friendLinks !== '') {
                        $decoded = json_decode($friendLinks, true);
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            throw new \Exception('友情推荐链接必须是有效的 JSON 格式');
                        }
                    }
                    $optionService->setSubKey('plugin:FootMenu', 'friend_links', $friendLinks);
                }

                if (isset($params['foot_desc'])) {
                    $optionService->setSubKey('plugin:FootMenu', 'foot_desc', trim((string)$params['foot_desc']));
                }
            });

            // 清除相关缓存
            yac()->delete("options");
            yac()->delete("options:all");

            return $this->successMsg('保存成功');
        } catch (\Throwable $e) {
            return $this->errorJson('保存失败：' . $e->getMessage());
        }
    }

    /**
     * 获取公共页面设置
     * GET /adminv2/pageset/getPage
     * 
     * 返回所有公共页面相关的配置项
     */
    public function getPageAction()
    {
        $optionService = new OptionService();

        $data = [
            'foot_desc' => $optionService->getSubKey('plugin:FootMenu', 'foot_desc') ?? '',
            'footer_copyright' => $optionService->getSubKey('plugin:FootMenu', 'footer_copyright') ?? '',
            'share_domian' => $optionService->get('share_domian') ?? '',
            'before_append' => $optionService->get('before_append') ?? '',
            'article_bottom_content' => $optionService->get('article_bottom_content') ?? '',
            'content_after' => $optionService->get('content_after') ?? '',
        ];

        return $this->showJson($data);
    }

    /**
     * 保存公共页面设置
     * POST /adminv2/pageset/savePage
     * 
     * 参数（全部可选，传哪个改哪个）：
     * - foot_desc: 首页底部内容（支持HTML）
     * - footer_copyright: 底部版权（支持HTML）
     * - share_domian: 分享文案域名
     * - before_append: 文章详情页顶部追加内容（支持HTML）
     * - article_bottom_content: 文章详情页底部追加内容（支持HTML）
     * - content_after: 文章详情页底部公告内容（支持HTML）
     */
    public function savePageAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->errorJson('请求方式错误');
        }

        $params = $this->data ?? [];
        if (empty($params)) {
            return $this->validationError('未传入任何修改参数');
        }

        try {
            transaction(function () use ($params) {
                $optionService = new OptionService();

                if (isset($params['foot_desc'])) {
                    $optionService->setSubKey('plugin:FootMenu', 'foot_desc', trim((string)$params['foot_desc']));
                }

                if (isset($params['footer_copyright'])) {
                    $optionService->setSubKey('plugin:FootMenu', 'footer_copyright', trim((string)$params['footer_copyright']));
                }

                if (isset($params['share_domian'])) {
                    $optionService->set('share_domian', trim((string)$params['share_domian']));
                }

                if (isset($params['before_append'])) {
                    $optionService->set('before_append', trim((string)$params['before_append']));
                }

                if (isset($params['article_bottom_content'])) {
                    $optionService->set('article_bottom_content', trim((string)$params['article_bottom_content']));
                }

                if (isset($params['content_after'])) {
                    $optionService->set('content_after', trim((string)$params['content_after']));
                }
            });

            // 清除相关缓存
            yac()->delete("options");
            yac()->delete("options:all");
            yac()->delete("site:copyright"); // 清除版权缓存
            redis()->del("options");

            return $this->successMsg('保存成功');
        } catch (\Throwable $e) {
            return $this->errorJson('保存失败：' . $e->getMessage());
        }
    }
}

