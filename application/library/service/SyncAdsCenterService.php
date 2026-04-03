<?php

namespace service;

class SyncAdsCenterService
{

    const GATEWAY_GET_ADS = '/openapi/getAdvertiseList';//网关-广告列表
    const POSITION_ARTICLE_LIST_ADS = 'article_list_ads';
    const CN_ARTICLE_LIST_ADS = '文章列表广告';


    /**
     * 同步或更新广告中心数据到本地数据库
     * @param $result
     * @return false|void
     */
    public static function updateToDB($result)
    {
        /*
          [data] => Array
        (
            [0] => Array
                (
                    [deptCode] => 1990985731052380160
                    [merchantCode] => 1991015570819227648
                    [appCode] => tj_appid01
                    [advertiseLocationCode] => 201
                    [advertiseLocationName] => 内容详情
                    [advertiseLocationRemark] =>
                    [displayMode] => 1//展示模式：0 → 多个轮播 1 → 单体 2 → 三分屏 3 → 四分屏
                    [advertiseHeight] => 140
                    [advertiseWidth] => 700
                    [materialType] => 1
                    [adLimit] => 100
                    [status] => 1
                    [updateTime] => 2025-11-27T19:38:12
                    [adDetailInfoList] => Array
                        (
                            [0] => Array
                                (
                                    [advertiseCode] => tj_69
                                    [customerCode] => 1991016060940427264
                                    [advertiseName] => 约炮
                                    [advertiseUrl] => https://h9qu4.cc
                                    [advertiseIcon] => /hc237/uploads/default/other/2025-11-27/ac7c5fabd9f0e13b670a7e3bb99de2a1.gif
                                    [advertiseType] => 3
                                    [adMode] => CPS
                                    [appCode] => tj_appid01
                                    [advertiseDesc] =>
                                    [status] => 1
                                    [startTimeStamp] => 1713265200000
                                    [endTimeStamp] => 4070880000000
                                    [sort] => 100
                                    [adExtData] => {"redirect_type":{"value":2,"mark":{"1":"内部跳转","2":"外部跳转"}},"url_config":"https:\/\/h9qu4.cc","router":"web\/:url","product_type":{"value":1,"mark":["内部产品","外部产品","其他"]}}
                                    [adExtRaw] =>
                                    [adExtReserve] =>
                                    [updateTime] => 2025-11-27T19:38:16
                                )

         */

        foreach ($result as $item) {
            //广告位广告
            if (!$item['adDetailInfoList']) {
                continue;
            }
            // 处理广告位
            $position_id = $item['advertiseLocationCode'];
            $position_name = $item['advertiseLocationName'];
            $position_val = $item['advertiseLocationCode'];
            $position_size = $item['advertiseLocationRemark'] ?? '';
            $height = $item['advertiseHeight'];
            $width = $item['advertiseWidth'];
            //广告集合
            $list = $item['adDetailInfoList'];

            foreach ($list as $_data) {
                $isNotice = false;//默认广告表 ads
                $adExtReserve = $_data['adExtReserve']??'';
                switch ($adExtReserve){
                    case 'notice':
                        self::updateToNoticeDB($item,$_data);//进入notice 表
                        break;
                    case 'contents':
                        self::updateToContensDB($item,$_data);//进入contents 表
                        break;
                    default:
                        //self::updateToAdsDB($item,$_data);//更新app ads表
                        self::updateToAdvertDB($item,$_data);//更新app ads表
                }

            }

        }
    }

    public static function updateToNoticeDB($item,$_data)
    {

            // 处理广告位
            $position_id = $item['advertiseLocationCode'];
            $position_name = $item['advertiseLocationName'];
            $position_val = $item['advertiseLocationCode'];
            $position_size = $item['advertiseLocationRemark'] ?? '';
            $height = $item['advertiseHeight'];
            $width = $item['advertiseWidth'];
            //广告集合
            $list = $item['adDetailInfoList'];
                if( trim($_data['adExtReserve']) != 'notice'){
                   return ;
                }
                $ads_code = $_data['advertiseCode'];
                $advertiseUrl = $_data['advertiseUrl'];
                $link_url = '';
                 $type = 'url';
                if (stripos($advertiseUrl, 'https://') !== false) {
                    $link_url = $advertiseUrl;
                } elseif (stripos($advertiseUrl, 'http://') !== false) {
                    $link_url = $advertiseUrl;
                }elseif (stripos($advertiseUrl, 'inner://') !== false) {
                    $link_url = ltrim($advertiseUrl,'inner://');
                    $type = 'router';
                } elseif ($advertiseUrl) {
                    $link_url = $advertiseUrl;
                }
        $adExtData = !empty($_data['adExtData']) ? json_decode($_data['adExtData'], true) : [];
        if($adExtData && isset($adExtData['type'])){
            $type = $adExtData['type'];
        }
        $router='';
        if($adExtData && isset($adExtData['router'])){
            $router = $adExtData['router'];
        }
        $aff=0;
        if($adExtData && isset($adExtData['aff'])){
            $aff = $adExtData['aff'];
        }
        $url='';
        if($adExtData && isset($adExtData['url'])){
            $url = $adExtData['url'];
        }
        $visible_type=0;
        if($adExtData && isset($adExtData['visible_type'])){
            $visible_type = $adExtData['visible_type'];
        }
        $updateData = [
            'type'         => $type,
            'aff'          => $aff,
            'url'          => $url?$url:$link_url,
            'title'        => $_data['advertiseName'],
            'content'      => $_data['advertiseDesc'],
            'status'       => $_data['status'],
            'created_at'   => date('Y-m-d H:i:s', strtotime($_data['updateTime'])),
            'img_url'      => $_data['advertiseIcon']??'',
            'router'       => $router,
            'width'        => $width,
            'height'       => $height,
            'visible_type' => $visible_type,
            'pos'          => $position_id,
            'start_at'     => self::msToDate($_data['startTimeStamp']),
            'end_at'       => self::msToDate($_data['endTimeStamp']),
            'sort'         => $_data['sort'],
            'clicked'      => 0,
            'ads_code'     => $ads_code,
        ];
                $hasAds = \NoticeModel::where(['ads_code' => $ads_code])->exists();
                if (!$hasAds) {
                    \NoticeModel::create($updateData);

                } else {
                    unset($updateData['ads_code'], $updateData['created_at'], $updateData['clicked']);
                    \NoticeModel::where(['ads_code' => $ads_code])->update($updateData);
                }
        echo "同步notice {$ads_code} $position_id,$position_name\n";
    }

    public static function updateToContensDB($item, $_data)
    {
        // 处理广告位
        $position_id = $item['advertiseLocationCode'];
        $position_name = $item['advertiseLocationName'];
        $position_val = $item['advertiseLocationCode'];
        $position_size = $item['advertiseLocationRemark'] ?? '';
        $height = $item['advertiseHeight'];
        $width = $item['advertiseWidth'];
        //广告集合
        $list = $item['adDetailInfoList'];

        if( $position_id != self::POSITION_ARTICLE_LIST_ADS){
            echo "[".date('Y-m-d H:i:s')."] 同步 文章列表 广告错误 ，非文章广告 ".var_export($_data, true)."\n";
            return;
        }

        $adExtData = !empty($_data['adExtData']) ? json_decode($_data['adExtData'], true) : [];
        $authorId = 0;
        if ($adExtData && isset($adExtData['type'])) {
            $authorId = $adExtData['type'] ? $adExtData['type']['authorId'] : 0;
        }

        $product_type = 1;
        if ($adExtData && isset($adExtData['product_type'])) {
            $product_type = $adExtData['product_type'] ? $adExtData['product_type']['value'] : 1;
        }
        $ads_code = $_data['advertiseCode'];
        $advertiseUrl = $_data['advertiseUrl'];
        $link_url = '';
        if (stripos($advertiseUrl, 'https://') !== false) {
            $link_url = $advertiseUrl;
        } elseif (stripos($advertiseUrl, 'http://') !== false) {
            $link_url = $advertiseUrl;
        } elseif (stripos($advertiseUrl, 'inner://') !== false) {
            $link_url = '/'.ltrim($advertiseUrl, 'inner://');
        } elseif ($advertiseUrl) {
            $link_url = $advertiseUrl;
        }
        //兼容老数据
        if ($adExtData && (!empty($adExtData['url_config']))) {
            $link_url = $adExtData['url_config'];
        }

        $id = ltrim($ads_code,'tj_');

        $updateData = [
            'title'            => htmlspecialchars($_data['advertiseName']),
            //'slug'             => $id,
            //'img_url'          => $_data['advertiseIcon']??'',
            //'link'             => $link_url,
            //'position'         => $position_id,
            'status'           => $_data['status']==1 ? 'publish':'removed',
            'created'          => strtotime($_data['updateTime'])-6*3600, // 北京时间
            //'updated_at'         => date('Y-m-d H:i:s', strtotime($_data['updateTime'])),
            //'ads_code'         => $ads_code,
        ];
        if(!empty($authorId)){
            $updateData['authorId'] = $authorId;
        }

        $fieldsData = [];
        if(!empty($_data['advertiseIcon'])){
            $fieldsData['banner'] = $_data['advertiseIcon'];
        }
        if($link_url){
            $fieldsData['redirect'] = $link_url;
        }

        // 1. conetens
        $hasone = \AdsContentsModel::where('ads_code', $ads_code)->first();
        $cid = $hasone->cid??'';
        $is_new = false;
        if (!$cid) {
            $cid = \ContentsModel::insertGetId(array_merge($updateData,[
                'modified' => time(),
                'text' => '<!--markdown-->',
                'order' => 0,
                'authorId' => 96,
                'template' =>'',
                'type' => 'post',
                'is_slice' => 0,
                'is_home' => 1,
                'web_show' => 1,
                'allowPing' => 1,
                'allowFeed' => 1
            ])); 
            if(!$cid){
                echo "[".date('Y-m-d H:i:s')."] 同步ads {$ads_code} $position_id,$position_name\n";
                return false;
            }
            \ContentsModel::where('cid',$cid)->update(['slug'=>$cid]);

            $is_new = true;
            $rs = \AdsContentsModel::insert([
                'ads_code' => $ads_code,
                'cid' => $cid
            ]);
        } else {
            \ContentsModel::where('cid',$cid)->update($updateData);
        }

        // 2. fields
        if( !empty($fieldsData) ){
            $c_fields = [];
            if($is_new){
                $fieldsData = array_merge($fieldsData,[
                    'ads_field' => 1,
                    'contentLang' => 0,
                    'disableDarkMask' =>0,
                    'disableBanner' => 1,
                    'enableFlowChat' =>0,
                    'enableMathJax' =>0,
                    'enableMermaid' =>0,
                    'headTitle' => 0,
                    'hide_list_author' => 1,
                    'hide_list_title' => 1,
                    'hotSearch' => 0,
                    'TOC' => 0,
                    'outjump' => 1
                ]);
            }else{
                $c_fields = \FieldsModel::getNameByCid($cid);
            }
            foreach($fieldsData as $fk => $fv){
                if(in_array($fk, $c_fields)){
                    \FieldsModel::where(['cid' => $cid, 'name' => $fk])->update([
                        'str_value' => $fv
                    ]);
                }else{
                    $rs = \FieldsModel::insert([
                        'cid' => $cid,
                        'name' => $fk,
                        'type' => 'str',
                        'str_value' => $fv
                    ]);
                    if(!$rs){
                        echo "[".date('Y-m-d H:i:s')."] 同步typecho_fields失败 {$ads_code} $position_id,$position_name， {$cid},{$fk}={$fv}\n";
                    }
                }
            }
        }

        cached(sprintf(\FieldsModel::CK_CONTENTS_SLUG, $cid))->clearCached();
        cached('')->clearGroup(\ContentsModel::GP_HOME_CONTENT_LIST);
        cached('')->clearGroup(\ContentsModel::GP_HOME_CONTENT_LIST_COUNT);
        cached('')->clearGroup(\CategoriesModel::GP_CONTENT_CATEGORY_LIST);
        cached('')->clearGroup(\CategoriesModel::GP_CONTENT_CATEGORY_LIST_COUNT);

        echo "[".date('Y-m-d H:i:s')."] 同步ads {$ads_code} $position_id,$position_name\n";
    }

    public static function updateToAdvertDB($item, $_data)
    {
        // 处理广告位
        $position_id = $item['advertiseLocationCode'];
        $position_name = $item['advertiseLocationName'];
        $position_val = $item['advertiseLocationCode'];
        $position_size = $item['advertiseLocationRemark'] ?? '';
        $height = $item['advertiseHeight'];
        $width = $item['advertiseWidth'];
        //广告集合
        $list = $item['adDetailInfoList'];

        if (isset($_data['adExtReserve']) && trim($_data['adExtReserve']) == 'notice') {
            return;
        }
        $adExtData = !empty($_data['adExtData']) ? json_decode($_data['adExtData'], true) : [];
        $type = 0;
        if ($adExtData && isset($adExtData['type'])) {
            $type = $adExtData['type'] ? $adExtData['type']['value'] : 0;
        }
        if($position_id == \AdvertModel::POSITION_ARTICLE_BOTTOM_BTN){
            if(!$type) $type = 2;
        }
        $product_type = 1;
        if ($adExtData && isset($adExtData['product_type'])) {
            $product_type = $adExtData['product_type'] ? $adExtData['product_type']['value'] : 1;
        }
        $ads_code = $_data['advertiseCode'];
        $advertiseUrl = $_data['advertiseUrl'];
        $link_url = '';
        if (stripos($advertiseUrl, 'https://') !== false) {
            $link_url = $advertiseUrl;
        } elseif (stripos($advertiseUrl, 'http://') !== false) {
            $link_url = $advertiseUrl;
        } elseif (stripos($advertiseUrl, 'inner://') !== false) {
            $link_url = '/'.ltrim($advertiseUrl, 'inner://');
        } elseif ($advertiseUrl) {
            $link_url = $advertiseUrl;
        }
        //兼容老数据
        if ($adExtData && (!empty($adExtData['url_config']))) {
            $link_url = $adExtData['url_config'];
        }
        $router = '';
        if ($adExtData && (!empty($adExtData['router']))) {
            $router = $adExtData['router'];
        }
        $id = ltrim($ads_code,'tj_');

        $updateData = [
            //'id'               => $id,
            'title'            => $_data['advertiseName'],
            //'description'      => $_data['advertiseDesc'],
            //'img_url'          => $_data['advertiseIcon']??'',
            //'link'             => $link_url,
            'position'         => $position_id,
            //'android_down_url' => '',
            //'ios_down_url'     => '',
            //'router'           => $router,
            //'type'             => $type,
            //'product_type'     => $product_type,
            'status'           => $_data['status'],
            //'oauth_type'       => '',
            //'mv_m3u8'          => $adExtData['mv_m3u8'] ?? '',
            //'channel'          => $adExtData['channel'] ?? '',
            //'created_at'       => date('Y-m-d H:i:s', strtotime($_data['updateTime'])),
            'updated_at'         => strtotime($_data['updateTime']),
            //'end_at'           => self::msToDate($_data['endTimeStamp']),
            'sort'             => $_data['sort'],
            //'clicked'          => 0,
            'ads_code'         => $ads_code,
        ];
        if(!empty($_data['advertiseIcon'])){
            $updateData['img_url'] = $_data['advertiseIcon'];
        }
        if($link_url!=""){
            $updateData['link'] = $link_url;
        }

        $hasAds = \AdvertModel::where(['ads_code' => $ads_code])->exists();
        if (!$hasAds) {
            \AdvertModel::create($updateData);
        } else {
            //unset($updateData['ads_code'], $updateData['created_at'], $updateData['clicked'], $updateData['oauth_type'], $updateData['mv_m3u8'], $updateData['channel']);
            unset($updateData['ads_code']);
            \AdvertModel::where(['ads_code' => $ads_code])->update($updateData);
        }

        if($type>0){
            $aid = \AdvertModel::where(['ads_code' => $ads_code])->first();
            $ads_param = ['aid' => $aid->id,'cid' => $type];
            $is_ads_category = \AdsCategoryModel::where($ads_param)->exists();
            if(!$is_ads_category){
                \AdsCategoryModel::create($ads_param);
            }
        }

        cached('')->clearGroup(\AdvertModel::GP_ADVERT_LIST);
        cached('')->clearGroup(\AdsCategoryModel::GP_ADSCATEGORY_LIST);

        echo "[".date("Y-m-d H:i:s")."] 同步ads {$ads_code} $position_id,$position_name\n";
    }
    public static function updateToAdsDB($item, $_data)
    {
        // 处理广告位
        $position_id = $item['advertiseLocationCode'];
        $position_name = $item['advertiseLocationName'];
        $position_val = $item['advertiseLocationCode'];
        $position_size = $item['advertiseLocationRemark'] ?? '';
        $height = $item['advertiseHeight'];
        $width = $item['advertiseWidth'];
        //广告集合
        $list = $item['adDetailInfoList'];

        if (trim($_data['adExtReserve']) == 'notice') {
            return;
        }
        $adExtData = !empty($_data['adExtData']) ? json_decode($_data['adExtData'], true) : [];
        $type = 1;
        if ($adExtData && isset($adExtData['type'])) {
            $type = $adExtData['type'] ? $adExtData['type']['value'] : 1;
        }
        $product_type = 1;
        if ($adExtData && isset($adExtData['product_type'])) {
            $product_type = $adExtData['product_type'] ? $adExtData['product_type']['value'] : 1;
        }
        $ads_code = $_data['advertiseCode'];
        $advertiseUrl = $_data['advertiseUrl'];
        $link_url = '';
        if (stripos($advertiseUrl, 'https://') !== false) {
            $link_url = $advertiseUrl;
        } elseif (stripos($advertiseUrl, 'http://') !== false) {
            $link_url = $advertiseUrl;
        } elseif (stripos($advertiseUrl, 'inner://') !== false) {
            $link_url = ltrim($advertiseUrl, 'inner://');
        } elseif ($advertiseUrl) {
            $link_url = $advertiseUrl;
        }
        //兼容老数据
        if ($adExtData && isset($adExtData['url_config'])) {
            $link_url = $adExtData['url_config'];
        }
        $router = '';
        if ($adExtData && isset($adExtData['router'])) {
            $router = $adExtData['router'];
        }
        $updateData = [
            'title'            => $_data['advertiseName'],
            'description'      => $_data['advertiseDesc'],
            'img_url'          => $_data['advertiseIcon'],
            'url_config'       => $link_url,
            'position'         => $position_id,
            'android_down_url' => '',
            'ios_down_url'     => '',
            'router'           => $router,
            'type'             => $type,
            'product_type'     => $product_type,
            'status'           => $_data['status'],
            'oauth_type'       => '',
            'mv_m3u8'          => $adExtData['mv_m3u8'] ?? '',
            'channel'          => $adExtData['channel'] ?? '',
            'created_at'       => date('Y-m-d H:i:s', strtotime($_data['updateTime'])),
            'start_at'         => self::msToDate($_data['startTimeStamp']),
            'end_at'           => self::msToDate($_data['endTimeStamp']),
            'sort'             => $_data['sort'],
            'clicked'          => 0,
            'ads_code'         => $ads_code,
        ];
        $hasAds = \AdsModel::where(['ads_code' => $ads_code])->exists();
        if (!$hasAds) {
            \AdsModel::create($updateData);

        } else {
            unset($updateData['ads_code'], $updateData['created_at'], $updateData['clicked'], $updateData['oauth_type'], $updateData['mv_m3u8'], $updateData['channel']);
            \AdsModel::where(['ads_code' => $ads_code])->update($updateData);
        }
        echo "同步ads {$ads_code} $position_id,$position_name\n";
    }
    public static function adstest()
    {
        $params = [
            'merchantCode' => config('adscenter.merchan_code'),
            'appCode'      => config('adscenter.app_code'),
            'sinceTimestamp' => (time() - 900) * 1000,//微秒时间戳
        ];
//        print_r($params);die;
        $result = self::getAdvertiseList($params);
        print_r($result);
        echo "获取列表测试\n";
    }


    //  \service\SyncAdsCenterService::ads_list();
    // 每15分钟执行1次 改方法同步更新
    public static function ads_list()
    {
        $params = [
            'merchantCode' => config('adscenter.merchan_code'),
            'appCode'      => config('adscenter.app_code'),
            'sinceTimestamp' => (time() - 900) * 1000,//微秒时间戳
        ];
        $result = self::getAdvertiseList($params);
        print_r($result);
        echo "获取状态：{$result['code']} 消息：{$result['msg']} 数据：". count($result['data'])."\n";

        if (isset($result['code']) && $result['code'] == 0 && $result['data']) {
            //新增或更新到本地数据库
            return self::updateToDB($result['data']);
        }
        echo "over \n";
    }

    public static function sync_advert()
    {
        //第一次全量导入上架的广告到广告中心
        $data = \AdvertModel::query()->get()->toArray();
        if (empty($data)) {
            return [false . '本地没有可用数据导入'];
        }
        //print_r($data);
        //die;
        $params = [];
        foreach ($data as $k => $ads) {
            if (empty($ads['ads_code'])) {
                echo "ads_code为空 id:{$ads['id']}",PHP_EOL;
                continue;
            }
            $url = $ads['img_url'];
            if (stripos($ads['img_url'], '://') !== false) {
                $url = parse_url($ads['img_url'], PHP_URL_PATH);
            }
            $url = TB_IMG_ADM_US . '/' . trim($url, '/');

            /**
             * 广告链接
             * 协议说明：
             * 协议头://原始协议头/原始路径/参数
             * 分类：
             * 1.   外部跳转：        https://xxx.com 外部链接就用原始协议
             * 2.   内部跳转:          inner://xxxx/read_pido_video_play?id=294352
             * 3.   app下载链接:    downlaod://?android=xxxx&ios=xxxx
             */
            $url_config = $ads['link'];
            if (stripos($url_config, 'https://') !== false) {
                $link_url =$url_config;
            } elseif (stripos($url_config, 'http://') !== false) {
                $link_url = $url_config;
            //} elseif ($url_config && $type == 2) {//内部路由的时候处理
            } elseif ($url_config) {//内部路由的时候处理
                $link_url = "inner://{$url_config}";
            }

            // 查询分类 ID（可能为空）
            $type = \AdsCategoryModel::query()
                ->where('aid', $ads['id'])
                ->value('cid') ?? 0;

            $params[$k] = [
                "merchantCode"            => config('adscenter.merchan_code'),//是,商户CODE
                "appCode"                 => config('adscenter.app_code'),//是,应用CODE
                "customerCode"            => config('adscenter.customer_code'),//是,客户CODE
                "deptCode"                => config('adscenter.department_code'),//是,部门CODE
                "advertiseName"           => $ads['title'],//是,广告名称
                "advertiseUrl"            => $link_url??'',//广告URL 对应原始url_config router 共同确定
                "advertiseCode"           => $ads['ads_code'],//广告code，老项目需要再传
                "advertiseIcon"           => $url,//是,广告图 支持图片URL或base64字符串（需要拼好域名，用于下载后同步到老司机）
                "pcAdvertiseIcon"         => '',//PC广告图 支持图片URL或base64字符串（需要拼好域名，用于下载后同步到老司机）
                "advertiseDesc"           => $ads['title'],//广告描述
                "advertiseType"           => 3,//是,广告类型 1:播放器、2:药台、3:炮台、4:黄游、5:直播、6:BC
                "startTime"               => $ads['updated_at'],//是,开始时间 格式：yyyy-MM-dd HH:mm:ss 东八区
                "endTime"                 => date("Y-m-d H:i:s", strtotime($ads['updated_at']) + 86400*360),//是,结束时间
                "sort"                    => $ads['sort'],//排序
                "advertiseLocationCode"   => $ads['position'],//是,广告位置标识 唯一标识 code
                "advertiseLocationRemark" => \AdvertModel::POSITION_OPT[$ads['position']]??"",//广告位置备注
                "advertiseLocationName"   => \AdvertModel::POSITION_OPT[$ads['position']]??"",//是,广告位置名称
                "status"                  => $ads['status'] == 1 ? 1 : 0,//是,1:开启 0:关闭
                "adMode"                  => '',//广告模式 CPT CPM CPC CPA CPS 等
                "advertiseHeight"         => 0,//广告图高
                "advertiseWidth"          => 0,//广告图宽
                "materialCoverRatio"      => '',//广告图比
                "adExtData"               => json_encode([
                    'type' => [//跳转类型
                        'value' => $type,
                        'mark'  => \AdvertModel::ADVERT_CATEGORY,
                    ],
                    'url_config'    => '',//路由配置
                    'router'        => '',//路由
                    'mv_m3u8'       => '',//视频
                    'channel'       => '',//渠道
                    'product_type'  => [
                        'value' => '',
                        'mark'  => '',
                    ],//产品类型
                ], JSON_UNESCAPED_UNICODE),//扩展字段1
                "adExtRaw"                => '',//扩展字段2
                "adExtReserve"            => '',//扩展字段3
            ];
        }

        $totalNumber = count($params);
        echo "total data number:" . $totalNumber . PHP_EOL;
        $perNumber = 50;

        $to_send_data = array_chunk($params, $perNumber);//每50条一个分片集合同步过去 ,那边容易超时
        $_t = count($to_send_data);
        foreach ($to_send_data as $k => $v) {
            $i = $k;
            $i++;
            $result = self::sync2adsCenter($to_send_data[$k]);
            echo "批次导入上限-{$perNumber}:{$i}/{$_t}\n";
            print_r($result);
            echo "---------\n";
        }
        echo "\nover\n";

    }
    
    public static function sync_advert_content()
    {
        //第一次全量导入上架的广告到广告中心
        $ids = \FieldsModel::getAdsID();
        if(!$ids){
            throw new Exception('本地没有可用数据导入1');
        }

        $ads_content_list = \AdsContentsModel::getAdsAll();
        if($ids){
            if( !is_array($ads_content_list) )$ads_content_list=[];
            foreach ($ids as $row_cid){
                $row_ads_code = "tj_".$row_cid;
                if(in_array($row_ads_code, array_keys($ads_content_list))) continue;
                $rs = \AdsContentsModel::insert([
                    'ads_code' => $row_ads_code,
                    'cid' => $row_cid
                ]);
                if(!$rs){
                    throw new Exception('数据导入 文章广告关系失败');
                }
            }
        }

        $data = \ContentsModel::whereIn('cid', $ids)->select(['cid','title','slug','created','modified','authorId','status'])->get();
        $data && $data = $data->toArray();

        $data_fields = \FieldsModel::whereIn('cid', $ids)->get();
        $data_fields && $data_fields = $data_fields->toArray();

        $fields = [];
        foreach($data_fields as $row){
            $fields[$row['cid']][$row['name']]=$row['str_value'];
        }

        if (empty($fields)) {
            throw new Exception('本地没有fields可用数据导入2');
        }
        if (empty($data)) {
            throw new Exception('本地没有contents可用数据导入');
        }
//        print_r($data);print_r($fields);
//        die;

        $params = [];
        foreach ($data as $k => $ads) {
            $ads['ads_code'] = 'tj_'.$ads['cid'];
            $ads['img_url'] = $fields[$ads['cid']]['banner']??'';
            $ads['link'] = $fields[$ads['cid']]['redirect']??'';
            $ads['position'] = self::POSITION_ARTICLE_LIST_ADS;

            if (empty($ads['ads_code'])) {
                echo "ads_code为空 id:{$ads['id']}",PHP_EOL;
                continue;
            }
            $url = $ads['img_url']; // 文章列表是例外 本站图片
            if (stripos($ads['img_url'], '://') !== false) {
                $url = parse_url($ads['img_url'], PHP_URL_PATH);
            }
            $url = TB_IMG_ADM_US . '/' . trim($url, '/');

            /**
             * 广告链接
             * 协议说明：
             * 协议头://原始协议头/原始路径/参数
             * 分类：
             * 1.   外部跳转：        https://xxx.com 外部链接就用原始协议
             * 2.   内部跳转:          inner://xxxx/read_pido_video_play?id=294352
             * 3.   app下载链接:    downlaod://?android=xxxx&ios=xxxx
             */
            $url_config = $ads['link'];
            if (stripos($url_config, 'https://') !== false) {
                $link_url =$url_config;
            } elseif (stripos($url_config, 'http://') !== false) {
                $link_url = $url_config;
            } elseif ($url_config) {//内部路由的时候处理
                $link_url = "inner://{$url_config}";
            }

            $params[$k] = [
                "merchantCode"            => config('adscenter.merchan_code'),//是,商户CODE
                "appCode"                 => config('adscenter.app_code'),//是,应用CODE
                "customerCode"            => config('adscenter.customer_code'),//是,客户CODE
                "deptCode"                => config('adscenter.department_code'),//是,部门CODE
                "advertiseName"           => $ads['title'],//是,广告名称
                "advertiseUrl"            => $link_url??'',//广告URL 对应原始url_config router 共同确定
                "advertiseCode"           => $ads['ads_code'],//广告code，老项目需要再传
                "advertiseIcon"           => $url,//是,广告图 支持图片URL或base64字符串（需要拼好域名，用于下载后同步到老司机）
                "pcAdvertiseIcon"         => '',//PC广告图 支持图片URL或base64字符串（需要拼好域名，用于下载后同步到老司机）
                "advertiseDesc"           => $ads['title'],//广告描述
                "advertiseType"           => 1,//是,广告类型 1:播放器、2:药台、3:炮台、4:黄游、5:直播、6:BC
                "startTime"               => $ads['created'],//是,开始时间 格式：yyyy-MM-dd HH:mm:ss 东八区
                "endTime"                 => date("Y-m-d H:i:s", strtotime($ads['created']) + 86400*360),//是,结束时间
                "sort"                    => 0,//排序
                "advertiseLocationCode"   => $ads['position'],//是,广告位置标识 唯一标识 code
                "advertiseLocationRemark" => self::CN_ARTICLE_LIST_ADS,//广告位置备注
                "advertiseLocationName"   => self::CN_ARTICLE_LIST_ADS,//Advert::POSITION_OPT[$ads['position']]??"",//是,广告位置名称
                "status"                  => $ads['status'] == 'publish' ? 1 : 0,//是,1:开启 0:关闭
                "adMode"                  => '',//广告模式 CPT CPM CPC CPA CPS 等
                "advertiseHeight"         => 0,//广告图高
                "advertiseWidth"          => 0,//广告图宽
                "materialCoverRatio"      => '',//广告图比
                "adExtData"               => json_encode([
                    'type' => [//跳转类型
                        'value' => '',
                        'mark'  => '',
                        'authorId'=> $ads['authorId']
                    ],
                    'url_config'    => '',//路由配置
                    'router'        => '',//路由
                    'mv_m3u8'       => '',//视频
                    'channel'       => '',//渠道
                    'product_type'  => [
                        'value' => '',
                        'mark'  => '',
                    ],//产品类型
                ], JSON_UNESCAPED_UNICODE),//扩展字段1
                "adExtRaw"                => '',//扩展字段2
                "adExtReserve"            => 'contents',//扩展字段3
            ];
        }

        $totalNumber = count($params);
        echo "total data number:" . $totalNumber . PHP_EOL;
        $perNumber = 50;

        $to_send_data = array_chunk($params, $perNumber);//每50条一个分片集合同步过去 ,那边容易超时
        $_t = count($to_send_data);
        foreach ($to_send_data as $k => $v) {
            $i = $k;
            $i++;
            $result = self::sync2adsCenter($to_send_data[$k]);
            echo "批次导入上限-{$perNumber}:{$i}/{$_t}\n";
            print_r($result);
            echo "---------\n";
        }
        echo "\nover\n";

    }

    // \service\SyncAdsCenterService::sync_ads();
    public static function sync_ads()
    {
        //第一次全量导入上架的广告到广告中心
        //$data = \AdsModel::query()->orderBy('id')->get()->toArray();
        $data = \AdsModel::query()->get()->toArray();
        if (empty($data)) {
            return [false . '本地没有可用数据导入'];
        }
        //print_r($data);
        //die;
        $params = [];
        foreach ($data as $k => $ads) {
            if (empty($ads['ads_code'])) {
                echo "ads_code为空 id:{$ads['id']}",PHP_EOL;
                continue;
            }
            $url = '';
            if (stripos($ads['img_url'], '://') !== false) {
                $url = parse_url($ads['img_url'], PHP_URL_PATH);
            }
            $url = TB_IMG_ADM_US . '/' . trim($url, '/');

            /**
             * 广告链接
             * 协议说明：
             * 协议头://原始协议头/原始路径/参数
             * 分类：
             * 1.   外部跳转：        https://xxx.com 外部链接就用原始协议
             * 2.   内部跳转:          inner://xxxx/read_pido_video_play?id=294352
             * 3.   app下载链接:    downlaod://?android=xxxx&ios=xxxx
             */
            $url_config = $ads['url_config'];
            if (stripos($url_config, 'https://') !== false) {
                $link_url =$url_config;
            } elseif (stripos($url_config, 'http://') !== false) {
                $link_url = $url_config;
            } elseif ($url_config && $type == 2) {//内部路由的时候处理
                $link_url = "inner://{$url_config}";
            }

            $params[$k] = [
                "merchantCode"            => config('adscenter.merchan_code'),//是,商户CODE
                "appCode"                 => config('adscenter.app_code'),//是,应用CODE
                "customerCode"            => config('adscenter.customer_code'),//是,客户CODE
                "deptCode"                => config('adscenter.department_code'),//是,部门CODE
                "advertiseName"           => $ads['title'],//是,广告名称
                "advertiseUrl"            => $link_url,//广告URL 对应原始url_config router 共同确定
                "advertiseCode"           => $ads['ads_code'],//广告code，老项目需要再传
                "advertiseIcon"           => $url,//是,广告图 支持图片URL或base64字符串（需要拼好域名，用于下载后同步到老司机）
                "pcAdvertiseIcon"         => '',//PC广告图 支持图片URL或base64字符串（需要拼好域名，用于下载后同步到老司机）
                "advertiseDesc"           => $ads['description'] ?: $ads['title'],//广告描述
                "advertiseType"           => 3,//是,广告类型 1:播放器、2:药台、3:炮台、4:黄游、5:直播、6:BC
                "startTime"               => $ads['start_at'],//是,开始时间 格式：yyyy-MM-dd HH:mm:ss 东八区
                "endTime"                 => $ads['end_at'],//是,结束时间
                "sort"                    => $ads['sort'],//排序
                "advertiseLocationCode"   => $ads['position'],//是,广告位置标识 唯一标识 code
                "advertiseLocationRemark" => \AdsModel::POSITION[$ads['position']],//广告位置备注
                "advertiseLocationName"   => \AdsModel::POSITION[$ads['position']],//是,广告位置名称
                "status"                  => $ads['status'] == 1 ? 1 : 0,//是,1:开启 0:关闭
                "adMode"                  => '',//广告模式 CPT CPM CPC CPA CPS 等
                "advertiseHeight"         => 0,//广告图高
                "advertiseWidth"          => 0,//广告图宽
                "materialCoverRatio"      => \AdsModel::SIZE_TIPS[$ads['position']] ?? '',//广告图比
                "adExtData"               => json_encode([
                    'type' => [//跳转类型
                        'value' => $type,
                        'mark'  => \AdsModel::ADVERT_CATEGORY,
                    ],
                    'url_config'    => $ads['link'],//路由配置
                    'router'        => $ads['router'],//路由
                    'mv_m3u8'       => $ads['mv_m3u8'],//视频
                    'channel'       => $ads['channel'],//渠道
                    'product_type'  => [
                        'value' => $ads['product_type'],
                        'mark'  => \AdsModel::PRODUCT_TYPE_TIPS,
                    ],//产品类型
                ], JSON_UNESCAPED_UNICODE),//扩展字段1
                "adExtRaw"                => '',//扩展字段2
                "adExtReserve"            => '',//扩展字段3
            ];
        }

        $totalNumber = count($params);
        echo "total data number:" . $totalNumber . PHP_EOL;
        $perNumber = 50;

        $to_send_data = array_chunk($params, $perNumber);//每50条一个分片集合同步过去 ,那边容易超时
        $_t = count($to_send_data);
        foreach ($to_send_data as $k => $v) {
            $i = $k;
            $i++;
            $result = self::sync2adsCenter($to_send_data[$k]);
            echo "批次导入上限-{$perNumber}:{$i}/{$_t}\n";
            print_r($result);
            echo "---------\n";
        }
        echo "\nover\n";

    }
    public static function syncnotice()
    {
        //第一次全量导入上架的广告到广告中心
//        ->where(['status' => \NoticeModel::STATUS_SUCCESS])
        $data = \NoticeModel::query()->get()->toArray();
        if (empty($data)) {
            return [false . '本地没有可用数据导入'];
        }
        //print_r($data);die;
        $params = [];
        foreach ($data as $k => $ads) {
            $ads_code = "tj_notice_{$ads['id']}";
            $url = '';
            if (stripos($ads['img_url'], '://') !== false) {
                $url = parse_url($ads['img_url'], PHP_URL_PATH);
            }
            $url = TB_IMG_ADM_US . '/' . trim($url, '/');

            /**
             * 广告链接
             * 协议说明：
             * 协议头://原始协议头/原始路径/参数
             * 分类：
             * 1.   外部跳转：        https://xxx.com 外部链接就用原始协议
             * 2.   内部跳转:          inner://xxxx/read_pido_video_play?id=294352
             * 3.   app下载链接:    downlaod://?android=xxxx&ios=xxxx
             */
            $url_config = $ads['url_str'];
            $type = $ads['type'];
            if (stripos($url_config, 'https://') !== false) {
                $link_url =$url_config;
            } elseif (stripos($url_config, 'http://') !== false) {
                $link_url = $url_config;
            } elseif ($url_config && $type == 'router') {//内部路由的时候处理
                $link_url = "inner://{$url_config}";
            }

            $params[$k] = [
                "merchantCode"            => config('adscenter.merchan_code'),//是,商户CODE
                "appCode"                 => config('adscenter.app_code'),//是,应用CODE
                "customerCode"            => config('adscenter.customer_code'),//是,客户CODE
                "deptCode"                => config('adscenter.department_code'),//是,部门CODE
                "advertiseName"           => $ads['title'],//是,广告名称
                "advertiseUrl"            => $link_url,//广告URL 对应原始url_config router 共同确定
                "advertiseCode"           => $ads_code,//广告code，老项目需要再传
                "advertiseIcon"           => $url,//是,广告图 支持图片URL或base64字符串（需要拼好域名，用于下载后同步到老司机）
                "pcAdvertiseIcon"         => '',//PC广告图 支持图片URL或base64字符串（需要拼好域名，用于下载后同步到老司机）
                "advertiseDesc"           => $ads['title'],//广告描述
                "advertiseType"           => 3,//是,广告类型 1:播放器、2:药台、3:炮台、4:黄游、5:直播、6:BC
                "startTime"               => $ads['start_at'],//是,开始时间 格式：yyyy-MM-dd HH:mm:ss 东八区
                "endTime"                 => $ads['end_at'],//是,结束时间
                "sort"                    => $ads['sort'],//排序
                "advertiseLocationCode"   => $ads['pos'],//是,广告位置标识 唯一标识 code
                "advertiseLocationRemark" => \NoticeModel::POS[$ads['pos']],//广告位置备注
                "advertiseLocationName"   => \NoticeModel::POS[$ads['pos']],//是,广告位置名称
                "status"                  => $ads['status'] == 1 ? 1 : 0,//是,1:开启 0:关闭
                "adMode"                  => '',//广告模式 CPT CPM CPC CPA CPS 等
                "advertiseHeight"         => $ads['height'],//广告图高
                "advertiseWidth"          => $ads['width'],//广告图宽
                "materialCoverRatio"      => "{$ads['width']} X {$ads['height']}",//广告图比
                "adExtData" => json_encode([
                    'type'         => $ads['type'],
                    'router'       => $ads['router'],
                    'aff'          => $ads['aff'],
                    'url'          => $ads['url'],
                    'visible_type' => $ads['visible_type']
                ], JSON_UNESCAPED_UNICODE),//扩展字段1,
                "adExtRaw"                => '',//扩展字段2
                "adExtReserve"            => 'notice',//扩展字段3 pop 标识
            ];
        }

        $totalNumber = count($params);
        echo "total data number:" . $totalNumber . PHP_EOL;
        $perNumber = 50;

        $to_send_data = array_chunk($params, $perNumber);//每50条一个分片集合同步过去 ,那边容易超时
        $_t = count($to_send_data);
        foreach ($to_send_data as $k => $v) {
            $i = $k;
            $i++;
            $result = self::sync2adsCenter($to_send_data[$k]);
            echo "批次导入上限-{$perNumber}:{$i}/{$_t}\n";
            print_r($result);
            echo "---------\n";
        }
        echo "\nover\n";

    }

    /**
     * 从广告中心获取数据
     * @param $params
     * @return mixed
     * @throws \Exception
     */
    public static function getAdvertiseList($params)
    {
        $result = self::postData(self::GATEWAY_GET_ADS, $params);
        //print_r($result);die;
        $dataEncryptString = $result['data'] ?? '';
        if (isset($result['code']) && $result['code'] == 0 && $dataEncryptString) {
            //$plaintext = self::decryptToString(self::DECRY_KEY, $dataEncryptString);
            $plaintext = self::decryptToString(config('adscenter.decrypt_key'), $dataEncryptString);
            $data = json_decode($plaintext, true);
            $result['data'] = $data;
        }
        return $result;
    }


    /**
     * 同步数据到-大部门新广告中心
     * @param $params
     * @return mixed
     * @throws \Exception
     */
    public static function sync2adsCenter($params)
    {
        //$result = self::postData(self::GATEWAY_SYNC_ADS, $params);
        echo config('adscenter.sync2center_url')."   --->同步请求链接<---\n";
        $result = self::postData(config('adscenter.sync2center_url'), $params);
        return $result;
        //$dataEncryptString = $result['data'] ?? '';
        if ($result['code'] == 0) {
            return true;
        }
        return false;
        //throw new \Exception($result['msg']);
    }


    protected static function postData($gateway, $params = [])
    {
        $url = '';
        if (strpos($gateway, 'https') !== false) {
            $url = $gateway;
        } else {
            //$url = self::REQUEST_URL_BASE . $gateway;
            $url = config('adscenter.base_uri') . $gateway;
        }
        echo "url:" . $url . PHP_EOL;
        echo "params:" . json_encode($params, JSON_UNESCAPED_UNICODE) . PHP_EOL;
        $result = self::postJson($url, $params);
        return $result;
    }


    /*** 以下内部方法不用管******************************/
    private const KEY_SIZE = 32; // 32 bytes = AES-256
    private const GCM_IV_LEN = 12; // 12 bytes IV
    private const GCM_TAG_LEN = 16; // 16 bytes tag (128-bit)

    /** 生成 32 字节密钥并以 Base64(无补位) 返回 */
    public static function generateKey(): string
    {
        return self::b64NoPadEncode(random_bytes(self::KEY_SIZE));
    }

    /** 加密：任意数据 -> Base64(无补位) */
    public static function encrypt(string $keyBase64, $data): string
    {
        $key = self::decodeKey($keyBase64);

        $plaintext = is_string($data)
            ? $data
            : json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($plaintext === false) {
            throw new \RuntimeException('JSON encode failed');
        }

        $iv = random_bytes(self::GCM_IV_LEN);
        $tag = '';
        $ciphertext = openssl_encrypt(
            $plaintext,
            'aes-256-gcm',
            $key,
            OPENSSL_RAW_DATA,   // 原始二进制
            $iv,
            $tag,
            '',                 // AAD（无）
            self::GCM_TAG_LEN
        );

        if ($ciphertext === false || strlen($tag) !== self::GCM_TAG_LEN) {
            throw new \RuntimeException('Encryption failed');
        }

        // 与 Java 相同：输出 IV + (CT||TAG)
        $out = $iv . $ciphertext . $tag;
        return self::b64NoPadEncode($out);
    }

    /** 解密：Base64(无补位) -> 明文字符串（JSON 文本） */
    public static function decryptToString(string $keyBase64, string $encryptedBase64): string
    {
        $key = self::decodeKey($keyBase64);
        $data = self::b64NoPadDecode($encryptedBase64);

        $minLen = self::GCM_IV_LEN + self::GCM_TAG_LEN + 1; // 至少还有1字节密文
        if (strlen($data) < $minLen) {
            throw new \InvalidArgumentException('Invalid ciphertext');
        }

        $iv = substr($data, 0, self::GCM_IV_LEN);
        $tag = substr($data, -self::GCM_TAG_LEN);
        $ct = substr($data, self::GCM_IV_LEN, -self::GCM_TAG_LEN);

        $plain = openssl_decrypt(
            $ct,
            'aes-256-gcm',
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        if ($plain === false) {
            throw new \RuntimeException('Decryption failed');
        }
        return $plain; // 返回 JSON 字符串
    }

    /* ----------------- helpers ----------------- */

    private static function decodeKey(string $keyBase64): string
    {
        $key = self::b64NoPadDecode($keyBase64);
        if (strlen($key) !== self::KEY_SIZE) {
            throw new \InvalidArgumentException('Key must be 32 bytes for AES-256');
        }
        return $key;
    }

    // Base64 去补位编码（等价 Java withoutPadding）
    private static function b64NoPadEncode(string $bin): string
    {
        return rtrim(base64_encode($bin), '=');
    }

    // Base64 去补位解码（自动补齐到4的倍数）
    private static function b64NoPadDecode(string $b64): string
    {
        $pad = (4 - (strlen($b64) % 4)) % 4;
        return base64_decode($b64 . str_repeat('=', $pad), true);
    }

    protected static function postJson(string $url, array $data, array $header = [])
    {
        $ch = curl_init();

        // 默认 header
        $baseHeader = [
            'Content-Type: application/json',
        ];
        $header = array_merge($baseHeader, $header);

        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTPHEADER     => $header,
            CURLOPT_POSTFIELDS     => json_encode($data, JSON_UNESCAPED_UNICODE),
            CURLOPT_HEADER         => false,
            // 如果需要调试可暂时关闭 SSL 验证
            // CURLOPT_SSL_VERIFYPEER => false,
            // CURLOPT_SSL_VERIFYHOST => false,
        ]);

        $result = curl_exec($ch);
        $errNo = curl_errno($ch);
        $errMsg = curl_error($ch);
        curl_close($ch);

        if ($errNo) {
            error_log("CURL Error ($errNo): $errMsg");
            return '';
        }

        return !empty($result) ? json_decode($result, true) : [];
    }

    private static function msToDate($ms)
    {
        if (empty($ms)) {
            return date("Y-m-d H:i:s");
        }
        // 转为秒时间戳
        $sec = intval($ms / 1000);

        // 格式化
        return date("Y-m-d H:i:s", $sec);
    }

}

