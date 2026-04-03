<?php

class PageNavigator
{

    protected $currentPage;
    protected $totalPages;
    public $baseUrl;
    protected $homeUrl;

    public function __construct(int $currentPage, int $totalPages, string $baseUrl = '/page', string $homeUrl = '')
    {
        $this->currentPage = max(1, $currentPage);
        $this->totalPages = max(1, $totalPages);
        $this->baseUrl = $baseUrl;
        $this->homeUrl = $homeUrl;
    }

    public function hasPages()
    {
        return $this->currentPage < 0 || $this->currentPage >= $this->totalPages;
    }

    public function render(): string
    {
        if ($this->totalPages <= 1) {
            return '';
        }
        $html = '<ul class="page-navigator">' . PHP_EOL;
        // 上一页
        if ($this->currentPage > 1) {
            // 第2页时，上一页指向首页（不使用 /page/1/ 或 /1/）
            if ($this->currentPage == 2 && !empty($this->homeUrl)) {
                $homeUrl = $this->homeUrl;
                // 确保首页 URL 以 / 结尾（根路径 / 除外，.html 结尾的除外）
                if ($homeUrl !== '/' && substr($homeUrl, -1) !== '/' && !str_ends_with($homeUrl, '.html')) {
                    $homeUrl .= '/';
                }
                $html .= $this->li('prev', $homeUrl, '上一页');
            } else {
                $prev = $this->currentPage - 1;
                $html .= $this->li('prev', $this->url($prev), '上一页');
            }
        }

        // 首页
        if ($this->currentPage > 2) {
            // 如果提供了首页URL，使用首页URL；否则使用 url(1)
            $firstPageUrl = !empty($this->homeUrl) ? $this->homeUrl : $this->url(1);
            // 确保首页 URL 以 / 结尾（根路径 / 除外，.html 结尾的除外）
            if ($firstPageUrl !== '/' && substr($firstPageUrl, -1) !== '/' && !str_ends_with($firstPageUrl, '.html')) {
                $firstPageUrl .= '/';
            }
            $html .= $this->li('', $firstPageUrl, '1');
        }

        // 省略号（前）
        if ($this->currentPage > 3) {
            $html .= '<li><span></span></li>' . PHP_EOL;
        }

        // 当前页
        $html .= $this->li('active', $this->url($this->currentPage), (string)$this->currentPage);

        // 省略号（后）
        if ($this->currentPage < $this->totalPages - 2) {
            $html .= '<li><span></span></li>' . PHP_EOL;
        }

        // 尾页
        if ($this->currentPage < $this->totalPages - 1) {
            $html .= $this->li('', $this->url($this->totalPages), (string)$this->totalPages);
        }
        // 下一页
        if ($this->currentPage < $this->totalPages) {
            $next = $this->currentPage + 1;
            $html .= $this->li('next', $this->url($next), '下一页');
        }

        $html .= '</ul>';

        return $html;
    }

    protected function li(string $class, string $href, string $label): string
    {
        $classAttr = $class ? " class=\"{$class}\"" : '';
        return "<li{$classAttr}><a href=\"{$href}\">{$label}</a></li>" . PHP_EOL;
    }

    protected function url(int $page): string
    {
        $url = str_replace('{page}' , $page , $this->baseUrl);
        // 确保 URL 以 / 结尾（根路径 / 除外）
        if ($url !== '/' && substr($url, -1) !== '/') {
            $url .= '/';
        }
        return $url;
    }

}

