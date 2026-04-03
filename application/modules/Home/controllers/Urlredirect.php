<?php

/**
 * UrlredirectController.php 跳转中转页
 * @author  chenmoyuan
 */
class UrlredirectController extends WebController
{
    public function indexAction()
    {
        $rawUrl = $this->getRequest()->get('url');

        // 缺参直接回首页
        if (empty($rawUrl)) {
            return $this->redirect('/');
        }

        // 仅允许 http/https 外链，避免伪协议 / JS 注入
        $rawUrl = trim($rawUrl);
        $parsed = parse_url($rawUrl);
        $scheme = strtolower($parsed['scheme'] ?? '');
        if (!filter_var($rawUrl, FILTER_VALIDATE_URL) || !in_array($scheme, ['http', 'https'], true)) {
            return $this->redirect('/');
        }

        // 站点基础信息
        $brand = options('brand', '') ?: options('title', '007吃瓜');
        $favicon = options('favicon_ico', '/favicon.ico');

        // 渲染视图（Blade 模板：themes/{theme}/urlredirect/index.blade.php）
        $this->display('urlredirect.index', [
            'url' => $rawUrl,
            'brand' => $brand,
            'favicon' => $favicon,
        ]);
        return true;
    }
}

