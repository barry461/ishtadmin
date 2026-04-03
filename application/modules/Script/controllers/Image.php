<?php

use QL\QueryList;
use tools\HttpCurl;

class ImageController extends \Yaf\Controller_Abstract
{


    public function init()
    {
        if (PHP_SAPI != 'cli') {
            die();
        }
    }
    //每个方法 一个网站规则  喃仁图

    //https://www.nanrentu.cc/sgtp/  采集
    public function nanRenAction()
    {
        //$d = $this->upload("https://img.nanrentu.cc/uploadImg/2018/0919/de9f7e2f25ba420ff82ad9d5362cb6bb.jpg!c230x345");
       /* $d = $this->upload("https://img.nanrentu.cc/listImg/2020/04/22/115/01.jpg");
        print_r($d);
        die;
        return;*/
        $url_kinds = [
            [
                'name'    => '小鲜肉帅哥',
                'tmp_url' => 'https://www.nanrentu.cc/sgtp/xxrsg_{PAGE}.html',
                'page'    => 5,
            ],
            [
                'name'    => '肌肉帅哥',
                'tmp_url' => 'https://www.nanrentu.cc/sgtp/jrsg_{PAGE}.html',
                'page'    => 5,
            ],
            [
                'name'    => '韩国帅哥',
                'tmp_url' => 'https://www.nanrentu.cc/sgtp/hgsg_{PAGE}.html',
                'page'    => 3,
            ],
            [
                'name'    => '欧美帅哥',
                'tmp_url' => 'https://www.nanrentu.cc/sgtp/omsg_{PAGE}.html',
                'page'    => 5,
            ],
            [
                'name'    => '帅哥生活照',
                'tmp_url' => 'https://www.nanrentu.cc/sgtp/sgshz_{PAGE}.html',
                'page'    => 5,
            ],
            [
                'name'    => '男体艺术',
                'tmp_url' => 'https://www.nanrentu.cc/tag/ntys_{PAGE}.html',
                'page'    => 5,
            ],
            [
                'name'    => '同志帅哥',
                'tmp_url' => 'https://www.nanrentu.cc/tag/tzsg_{PAGE}.html',
                'page'    => 6,
            ],
            [
                'name'    => '性感帅哥',
                'tmp_url' => 'https://www.nanrentu.cc/tag/xgsg_{PAGE}.html',
                'page'    => 5,
            ],
            [
                'name'    => '中国男模',
                'tmp_url' => 'https://www.nanrentu.cc/tag/zznm_{PAGE}.html',
                'page'    => 5,
            ],
            [
                'name'    => '搞基',
                'tmp_url' => 'https://www.nanrentu.cc/tag/gaoji_{PAGE}.html',
                'page'    => 3,
            ],
            [
                'name'    => '激凸',
                'tmp_url' => 'https://www.nanrentu.cc/tag/jitu_{PAGE}.html',
                'page'    => 3,
            ],
            [
                'name'    => '欧美男模',
                'tmp_url' => 'https://www.nanrentu.cc/tag/omnm_{PAGE}.html',
                'page'    => 5,
            ],
            [
                'name'    => '帅哥自拍',
                'tmp_url' => 'https://www.nanrentu.cc/tag/sgzp_{PAGE}.html',
                'page'    => 5,
            ],
        ];

        collect($url_kinds)->map(function ($item) {
            for ($i = 1; $i <= $item['page']; $i++) {
                $data = QueryList::getInstance()
                    ->get(str_replace('{PAGE}', $i, $item['tmp_url']))
                    ->rules([
                        'title' => ['a', 'title'],
                        'img'   => ['a>img', 'src'],
                        'href'  => ['a', 'href',],

                    ])->range('.h-piclist>li')
                    ->queryData(function ($item) {

                        $_img = $this->upload($item['img']);
                        if (!$_img) {
                            return $item;
                        }
                        //print_r($item);die;
                        /** @var PictureModel $_model */
                        $_model = PictureModel::create([
                            'title'      => $item['title'] ?: '未知',
                            '_id'        => '',
                            'author'     => '未知',
                            'tags'       => '偷窥,自拍',
                            'thumb'      => $_img,
                            'status'     => 1,
                            'created_at' => date('Y-m-d H:i:s', time()),
                            'updated_at' => date('Y-m-d H:i:s', time())
                        ]);
                        $href = $item['href'];
                        $total_Li = QueryList::getInstance()
                            ->get($href)
                            ->find('.page>ul>li')
                            ->count();
                        if ($total_Li <= 3) {
                            return $item;
                        }
                        $detail_li = null;
                        $detail_li[] = $href;
                        for ($i = 2; $i <= $total_Li - 3; $i++) {
                            $detail_li[] = str_replace(".html", "_{$i}.html", $href);
                        }
                        $item['data'] = collect($detail_li)->map(function ($li_a) use ($_model) {
                            $total_img = QueryList::getInstance()
                                ->get($li_a)
                                ->find('.info-pic-list')
                                ->find('img')
                                ->map(function ($img) {
                                    return $img->src;
                                })->all();
                            $data = [
                                'link'   => $li_a,
                                'images' => $total_img,
                            ];
                            print_r($data);
                            if ($total_img) {
                                foreach ($total_img as $_li_img) {
                                    $_img_upload = $this->upload($_li_img);
                                    if (!$_img_upload) {
                                        continue;
                                    }
                                    PictureValueModel::insert([
                                        'picture_id'   => $_model->id,
                                        'original_url' =>$_img_upload,
                                        'thumb_url'    => $_img_upload,
                                        'created_at'   => date('Y-m-d H:i:s', time()),
                                        'updated_at'   => date('Y-m-d H:i:s', time())
                                    ]);
                                }
                            }
                            usleep(1000);
                            return $data;
                        })->all();
                        return $item;
                    });
                print_r($data);
                //die;
            }
        });

        return;
        $detail = 'https://www.nanrentu.cc/sgtp/38033.html';
        //$detail = 'https://www.nanrentu.cc/sgtp/35372.html';

        // -3;
        $total_Li = QueryList::getInstance()->get($detail)->find('.page>ul>li')->count();
        print_r($total_Li);
        die;

        $detail_li = null;
        $detail_li[] = $detail;
        for ($i = 2; $i <= $total_Li - 3; $i++) {
            $detail_li[] = str_replace(".html", "_{$i}.html", $detail);
        }
        print_r($detail_li);
        die;

        $total_img = QueryList::getInstance()
            ->get($detail)
            ->find('.info-pic-list')
            ->find('img')
            ->map(function ($img) {
                return $img->src;

            })->all();
        print_r($total_img);
        die;


    }

    private function upload($imgUrl)
    {
        // 图片上传处理
        /*$img = file_get_contents($imgUrl, false, stream_context_create([
            'http' => [
                'method'  => "GET",
                'timeout' => 60,
                'header'=>"Accept-language: zh-CN,zh;q=0.9\r\n" .
                    "Cookie: UM_distinctid=17e8c13bb35246-0f25d8f05a68f4-5c1c3418-1b3720-17e8c13bb361d2; Hm_lvt_01486a794d5cfc87600d8de7781ba5c2=1643034921; Hm_lvt_19af9d48a7e5c5f30f24bf5e03d914e2=1643027612,1643095985; Hm_lpvt_19af9d48a7e5c5f30f24bf5e03d914e2=1643095985\r\n" .  // check function.stream-context-create on php.net
                    "User-Agent: Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/97.0.4692.99 Safari/537.36\r\n"
            ],
            "ssl"  => [
                "verify_peer"      => false,
                "verify_peer_name" => false,
            ]
        ]));*/
        //$img = file_get_contents($imgUrl);
        $img = $this->curl_file_get_contents($imgUrl);
        //var_dump($img);
        if(empty($img)){
            return false;
        }
        $dir = APP_PATH . '/storage/img/' . md5($imgUrl) . '.jpg';
        if(file_exists($dir)){
            unlink($dir);
        }
        file_put_contents($dir, $img);
        $upload['id'] = time();
        $upload['position'] = 'upload';
        $ret = LibUpload::upload2Remote($upload['id'], $dir, $upload['position'],'http://new.ycomesc.live/imgUpload.php');

        //print_r($ret);die;
        trigger_log("图片上传请求--\n" . print_r([$dir, $ret], true));
        if ($ret['code'] == 1) {
            unlink($dir);
            trigger_log("图片上传成功--\n" . print_r($ret, true));
            return $ret['msg'];
        }
        return false;
    }
    function curl_file_get_contents($durl){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // 跳过证书验证（https）的网站无法跳过，会报错
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书验证
        curl_setopt($ch, CURLOPT_URL, $durl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //将获取的信息以字符串形式返回
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);  //指定最多的 HTTP 重定向次数
        curl_setopt($ch,CURLOPT_TIMEOUT,30); //允许 cURL 函数执行的最长秒数
        $r = curl_exec($ch);
        if(curl_errno($ch)){  //如果存在错误，输出错误（超时是不会抛出异常的，需要用这个函数设置自己的处理方式）
            echo 'Curl error: ' . curl_error($ch);
            return false;
        }
        return $r;
    }
}
