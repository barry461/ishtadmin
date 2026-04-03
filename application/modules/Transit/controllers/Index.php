<?php

class IndexController extends WebController
{
     public function getVars(): array
    {
        // $zz_config = options(OptionsModel::ZZ_OPTION_NAME);
        $zz_line = trim( options('zz_line', '') );
        $zz_backup_line = trim( options('zz_backup_line', '') );

        if (strpos($zz_line, ',') !== false) {
            $zz_line = explode(',', $zz_line);
        }
        if (strpos($zz_backup_line, ',') !== false) {
            $zz_backup_line = explode(',', $zz_backup_line);
        }

        return [
            'theme'          => register('site.theme'),
            'app_name'          => register('site.app_name'),
            'zz_title'       => options('zz_title'),
            'zz_keywords'    => options('zz_keywords'),
            'zz_description' => options('zz_description'),
            'zz_logo'        => options('zz_logo'),
            'zz_floor_desp'  => options('zz_floor_desp'),
            'zz_siteUrl'     => options('zz_siteUrl'),
            'zz_line'        => $zz_line,
            'zz_backup_line' => $zz_backup_line,
            'zz_statistical' => options('zz_statistical'),
            'zz_favicon_ico' => options('favicon_ico'),//zz_favicon_ico
            'zz_contact_sites' => [
                ['name' => '回家的路', 'url' => '/ybml.html', 'icon' => 'book.png'],
                ['name' => '官方推特', 'url' => '/twitter.html', 'icon' => 'x.png'],
                ['name' => '官方TG群', 'url' => '/tgqun.html', 'icon' => 'telegram.png'],
                ['name' => '地址发布页', 'url' => '/gitlab.html', 'icon' => 'github.png'],
            ],
            'zz_email' => options('zz_email'),
            'zz_bottom_link' => options('zz_bottom_link'),
        ];
    }
    public function indexAction()
    {
        $data = $this->getVars();
        $data = json_encode($data);
        $data = replace_share($data);
        $data = json_decode($data, true);
        echo $this->getView()->render('index/index.phtml', $data);
    }

    private function getNum($code, $host, $set_host, $success = 0, $error = 0): array
    {
        if (strpos($host, $set_host) !== false) {
            if (strpos($code, 'success') !== false) {
                $success++;
            } else if (strpos($code, 'error') !== false) {
                $error++;
            }
        }
        return [$success, $error];
    }

    public function statAction()
    {
//        try {
//            $data = $_GET['d'] ?? '';
//            $data = $data ? $data : '';
//            $rs = base64_decode($data);
//            trigger_log("transit statAction".var_export($_SERVER,true));
//            test_assert($rs, '无法解码数据');
//            $rs = json_decode($rs, true);
//            test_assert($rs, '无法JSON解码数据');
//            $key = 'transit-' . date('Y-m-d');
//            redis()->sAdd($key, USER_IP);
//            redis()->ttl($key) == -1 && redis()->expire($key, 90000);// 25个小时
//            $main_host  = options('zz_line');
//            $bk_host  = options('zz_backup_line');
//
//            $line_success = 0;
//            $line_error = 0;
//            $backup_line_success = 0;
//            $backup_line_error = 0;
//            foreach ($rs as $v) {
//                $info = parse_url($v['u']);
//                $host = $info['host'] ?? '';
//                list($line_success, $line_error) = $this->getNum($v['t'], $host, $main_host, $line_success, $line_error);
//                list($backup_line_success, $backup_line_error) = $this->getNum($v['t'], $host, $bk_host, $backup_line_success, $backup_line_error);
//            }
//
//            $line_key = 'transit-line-' . $main_host . date('Y-m-d');
//            $backup_line_key = 'transit-line-' . $bk_host . date('Y-m-d');
//            redis()->hIncrBy($line_key, 'success', $line_success);
//            redis()->hIncrBy($line_key, 'error', $line_error);
//            redis()->hIncrBy($backup_line_key, 'success', $backup_line_success);
//            redis()->hIncrBy($backup_line_key, 'error', $backup_line_error);
//            redis()->ttl($line_key) == -1 && redis()->expire($line_key, 90000);// 25个小时
//            redis()->ttl($backup_line_key) == -1 && redis()->expire($backup_line_key, 90000);// 25个小时
//
//        } catch (Throwable $e) {
//            trigger_log($e->getMessage());
//        }
        header('content-type: image/gif');
        exit(base64_decode('R0lGODlhAQABAIABAAAAAP///yH5BAEAAAEALAAAAAABAAEAAAICTAEAOw=='));
    }

    public function robotsAction()
    {
        header('Content-Type: text/plain');
        $host = $_SERVER['HTTP_HOST'];
        $robots = setting('transit_seo_robots', "");
        $text = <<<TXT
User-agent: *
Allow: /

%s

Sitemap: https://%s/sitemap.xml
TXT;
        echo sprintf($text, $robots, $host);
    }

    public function sitemapAction()
    {
        header('Content-Type: text/xml; charset=utf-8');
        $cur_host = $_SERVER['HTTP_HOST'];
        $host = '{HOST}';

        $xml = yac()->fetch('transit_seo_map', function () use ($host) {
            $data = [
                [
                    'loc'      => "https://{HOST}/",
                    'date'     => date('Y-m-d'),
                    'freq'     => 'always',
                    'priority' => '0.8',
                ]
            ];

            $xml = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<?xml-stylesheet type='text/xsl' href='https://$host/sitemap.xsl'?>
<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    {list}
</urlset>
XML;
            $tpl = '<url><loc>{loc}</loc><lastmod>{date}</lastmod><changefreq>{freq}</changefreq><priority>{priority}</priority></url>';
            $tmp = '';
            foreach ($data as $v) {
                $item = $tpl;
                $item = str_replace('{loc}', $v['loc'], $item);
                $item = str_replace('{date}', $v['date'], $item);
                $item = str_replace('{freq}', $v['freq'], $item);
                $item = str_replace('{priority}', $v['priority'], $item);
                $tmp .= $item;
            }
            return str_replace('{list}', $tmp, $xml);
        });

        echo str_replace($host, $cur_host, $xml);
    }
}