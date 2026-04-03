<?php

class PreviewController extends WebController
{
    /**
     * 后台文章编辑前台预览页
     * GET /preview/article?token=xxx
     *
     * 说明：
     * - 仅根据后台生成的 token 渲染一次性预览内容，不写入正式文章表
     * - 内容来源为 Adminv2/ContentsController::previewCreateDraftAction 写入的 Redis 数据
     */
    public function indexAction()
    {
        $token = trim((string)($this->getRequest()->getQuery('token') ?? ''));
        if ($token === '') {
            return $this->x404();
        }

        $key = sprintf('admin:preview:article:%s', $token);
        $raw = redis()->get($key);
        error_log(sprintf("预览key:%s,res:%s", $key, $raw),3,APP_PATH . '/storage/logs/preview_log.log');
        if ($raw === false || $raw === null) {
            $this->assign('message', '预览链接已失效或不存在');
            $this->assign('title', '预览已失效');
            $this->assign('html', '<p style="text-align:center;margin:40px 0;">预览链接已失效，请在后台重新生成预览。</p>');
            $this->assign('header', '<title>预览已失效</title>');
            $this->display('preview_article');
            return false;
        }

        $data = json_decode($raw, true);
        if (!is_array($data)) {
            return $this->x404();
        }

        $title = (string)($data['title'] ?? '文章预览');
        $html = (string)($data['html'] ?? '');

        // 简单头部信息，保持为普通文章详情风格
        $siteName = options('brand', '') ?: options('title', '007吃瓜');
        $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $header = sprintf(
            "<title>%s - 预览 - %s</title>",
            $safeTitle,
            htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8')
        );

        $this->assign('title', $title);
        $this->assign('html', $html);
        $this->assign('header', $header);

        $this->display('preview_article');
        return false;
    }
}

