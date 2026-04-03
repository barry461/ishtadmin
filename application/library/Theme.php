<?php

use Illuminate\Support\Str;

class Theme
{
    protected $theme = null;
    /** @var Theme */
    protected static $instance = null;

    public function __construct($theme)
    {
        $this->theme = $theme;
        self::$instance = $this;
    }


    public static function getInstance(): ?Theme
    {
        return self::$instance;
    }

    public function importCss($href , $attr = []): string
    {
        return $this->css($href , $attr);
    }

    public function importJs($src, $attr = []): string
    {
        return self::script($src, $attr);
    }

    public function importImg($src , $alt = '' , $attr = [])
    {
        $src = $this->staticDir($src).$src;

        $alt = htmlspecialchars($alt , ENT_QUOTES);

        $attrs = '';
        foreach ($attr as $k=>$v){
            $attrs .= "{$k}='{$v}' ";
        }
        return sprintf('<img src="%s" alt="%s" %s/>', $src, $alt ?: $src, $attrs);
    }


    public function css($href, $attr = []): string
    {

        if (!Str::startsWith($href , '/')){
            $href = $this->staticDir('css').$href;
        }
        $path = $this->path($href, $attr , '.css');
        return <<<HTML
<link rel="stylesheet" type="text/css" href="$path"/>
HTML;
    }


    public function staticDir($dir): string
    {
        if (preg_match("#^/themes/{$this->theme}/#", $dir) || preg_match("#^/upload/#", $dir)){
            return "";
        }

        if (preg_match('#^/usr/#', $dir)){
            return "/themes/{$this->theme}/";
        }
        $dir = trim($dir);
        $dir = ltrim($dir, '/');
        return "/themes/{$this->theme}/static/{$dir}/";
    }


    public function js($src, $attr = []): string
    {
        return self::script($src, $attr);
    }

    public function script($src, $params = [], $attr = []): string
    {
        if (!Str::startsWith($src , '/')){
            $src = $this->staticDir('js').$src;
        }
        $path = $this->path($src, $params, '.js');
        return <<<HTML
<script type="text/javascript" src="$path"></script>
HTML;
    }

    public function itemprop($src, $attr = []): string
    {
        $path = $this->path($src, $attr , '.png');
        return <<<HTML
<meta itemprop="url" content="$path"/>
HTML;
    }

    public function image($src, $attr = []): string
    {
        $src = trim($src);
        // 已是完整 URL（如外观配置的评论头像），直接返回，避免被拼成 /themes/xxx/static/image/https://...
        if (strpos($src, '://') !== false) {
            return $src;
        }
        if (!preg_match('/\.(png|jpg|jpeg|gif|webp|svg)(\?|$)/i', $src)) {
            $src .= '.png';
        }

        if (!Str::startsWith($src , '/')){
            $src = $this->staticDir('image').$src;
        }

        return $this->path($src, $attr);
    }

    protected function path($path, $attr , $ext = ''): string
    {
        $staticDir = $this->staticDir($path);
        if (Str::endsWith($staticDir,'/')) {
            $path = $staticDir.ltrim($path, '/');
        } else {
            $path = $staticDir.$path;
        }

        if (!Str::endsWith($path, $ext)) {
            $path .= $ext;
        }
        $attr = is_array($attr) ? http_build_query($attr, '', '&') : $attr;
        if (!empty($attr)) {
            $attr = '?'.$attr;
        }

        return $path.$attr;
    }

    public function linkCss($path, $attr = []): string
    {
        $path = $this->path($path, $attr, '.css');
        return <<<HTML
<link rel="stylesheet" type="text/css" href="$path"/>
HTML;
    }

    public function vClass(array $map, string $prepend = ''): string
    {
        $classList = array_keys(array_filter($map));
        if ($prepend) {
            $classList[] = $prepend;
        }
        $classList = array_filter($classList); // 防止空项
        if (empty($classList)) {
            return '';
        }
        return htmlspecialchars(implode(' ', $classList));
    }


}