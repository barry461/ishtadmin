<?php

namespace plugins\FootMenu;

/**
 * 底部菜单
 *
 * @package FootMenu
 * @author wangyuhang
 * @version 1.0.0
 * @link http://typecho.org
 */
class Plugin extends \plugins\PluginInterface
{
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     */
    public static function activate()
    {
        //Typecho_Plugin::factory('Widget_Archive')->header = ['FootMenu_Plugin', 'importCss'];
        //Typecho_Plugin::factory('Widget_Archive')->footer = ['FootMenu_Plugin', 'render'];
    }

    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     */
    public static function deactivate()
    {
    }

    /**
     * 获取插件配置面板
     *
     * @param Typecho_Widget_Helper_Form $form 配置面板
     */
    public static function config( $form): void
    {
        /** 分类名称 */
        $default_menu = [
            ['name' => '链接1', 'target' => '_blank', 'icon' => '', 'link' => ''],
            ['name' => '链接2', 'target' => '_blank', 'icon' => '', 'link' => '']
        ];
        $default_desc = [
            'name' => '标题111',
            'desc' => '描述',
        ];
        $default_link = [
            ['name' => '链接1', 'target' => '_blank', 'link' => ''],
            ['name' => '链接2', 'target' => '_blank', 'link' => '']
        ];
        $default_contact = [
            ['name' => '链接1', 'target' => '_blank', 'link' => '', 'icon' => ''],
            ['name' => '链接2', 'target' => '_blank', 'link' => '', 'icon' => '']
        ];
//        $menu = new Typecho_Widget_Helper_Form_Element_Textarea('foot_menu', null, json_encode($default_menu), _t('底部菜单'));
//        $form->addInput($menu);
//        $desc = new Typecho_Widget_Helper_Form_Element_Textarea('foot_desc', null, json_encode($default_desc), _t('底部描述'));
//        $form->addInput($desc);
//        $link = new Typecho_Widget_Helper_Form_Element_Textarea('foot_link', null, json_encode($default_link), _t('底部链接'));
//        $form->addInput($link);
//        $contact_link = new Typecho_Widget_Helper_Form_Element_Textarea('contact_link', null, json_encode($default_contact), _t('联系方式'));
//        $form->addInput($contact_link);
    }

    /**
     * 个人用户的配置面板
     *
     * @param Typecho_Widget_Helper_Form $form
     */
    public static function personalConfig($form)
    {
    }

    /**
     * 插件实现方法
     *
     * @access public
     * @return void
     */
    public static function render()
    {
        $options = self::options();
        $menu = $options['foot_menu'];
        $menu = json_decode($menu, true);
        $desc = $options['foot_desc'];
        $desc = json_decode($desc, true);
        $links = $options['foot_link'];
        $links = json_decode($links, true);
        $contact_links = $options['contact_link'];
        $contact_links = json_decode($contact_links, true);
        $str = <<<EOF
            <footer id="foot-menu">
            <div class="container line-container"></div>
            <div class="container">
                <div class="foot-menu">
EOF;
        $default_icon = options('img_zwimg');
        if(is_array($menu)) {
            foreach ($menu as $key => $item) {
                $icon  = CDN_XHOST . parse_url($item['icon'], PHP_URL_PATH);
                $icon2 = isset($item['icon2']) ? CDN_XHOST . parse_url($item['icon2'], PHP_URL_PATH) : $icon;
                $str .= <<<HTML
<a href="{$item['link']}" target="{$item['target']}">
    <img class='mode-dark' src="{$default_icon}" alt="{$item['name']}" id="foot-menu-icon-{$key}">
    <img class='mode-white' src="{$default_icon}" alt="{$item['name']}" id="foot-menu-icon2-{$key}">
    <span>{$item['name']}</span>
    <script type="text/javascript">
    loadImage("{$icon}", "foot-menu-icon-{$key}");loadImage("{$icon2}", "foot-menu-icon2-{$key}");
    </script>
</a>
HTML;

            }
        }

        $str .= '</div>';

        if(is_array($desc)) {
            $str .= <<<EOF
                <div class="footer-desc">
                    <b>{$desc['name']}</b>
                    <p>{$desc['desc']}</p>
                </div>
EOF;
        }

        if(is_array($links)) {
            foreach ($links as $item) {
                $str .= <<<HTML
<div class="footer-link"><a href="{$item['link']}" target="{$item['target']}"><span>{$item['name']}</span></a></div>
HTML;
            }
        }

        if(is_array($contact_links)) {
            $str .= '<div class="contact-link">';
            foreach ($contact_links as $k => $item) {
                $icon2 = CDN_XHOST . parse_url($item['icon'], PHP_URL_PATH);
                $icon3 = isset($item['icon2']) ? CDN_XHOST . parse_url($item['icon2'], PHP_URL_PATH) : $icon2;
                $str .= <<<HTML
<a href="{$item['link']}" target="{$item['target']}">
    <img class='mode-dark' alt="{$item['name']}" src="{$default_icon}" id="foot-contact-icon-{$k}" />
    <img class='mode-white' alt="{$item['name']}" src="{$default_icon}" id="foot-contact-icon2-{$k}" />
    <script type="text/javascript">
    loadImage("{$icon2}", "foot-contact-icon-{$k}");
    loadImage("{$icon3}", "foot-contact-icon2-{$k}");
</script>
</a>
HTML;
            }
            $str .= "</div>";
        }
        echo $str . '</div></footer>';
    }

    public static function importCss(): void
    {
        $url = Helper::options()->pluginUrl . '/FootMenu';
        $url = parse_url($url , PHP_URL_PATH);
        echo <<<EOF
<link rel="stylesheet" type="text/css" href="$url/assets/foot_menu.css?t=20231031" />
EOF;
    }
}
