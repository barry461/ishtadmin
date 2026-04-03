<?php

/**
 * 导入约炮  经纪人  用户  数据
 * Class ImportController
 */
class ImportController extends \Yaf\Controller_Abstract
{

    static $baseDir = APP_PATH . '/storage/logs/';

    public function memberAction()
    {
        $data = file_get_contents(self::$baseDir.'members.log');
        $arrays = explode("\r\n",$data);
        echo count($arrays),"\r\n";
        foreach ($arrays as $array){

            $user = json_decode(base64_decode($array),320);
            echo $user['uuid'],"\r\n";
            $exist = MemberModel::query()->where('uuid',$user['uuid'])->exists();
            if ($user && !$exist){
                // 创建用户
                $member = new MemberModel();
                $member->uuid = $user['uuid'];
                $member->app_version = '1.0.0';
                $member->oauth_type = 'pwa';
                $member->oauth_id = $user['oauth_id'].'1';
                $member->username = $user['username'];
                $member->role_id = $user['role_id'];
                $member->regdate = date('Y-m-d H:i:s', $user['regdate']);
                $member->regip = $user['regip'];
                $member->invited_num = 0;
                $member->thumb = $user['thumb'];
                $member->post_num = 1;
                $member->invited_by = 0;
                $member->channel = 'self';
                $member->nickname = $user['nickname'];
                $member->save();
                // 更新推广码 / 昵称
                $member->aff = $member->uid;
                $member->save();
                echo $member->uid,"\r\n";
            }
        }
    }
    public function girlsAction()
    {
        $members = array("0276fa2ed1ef97c23c1083ed5f7ada51",
            "2863ee354aaf95a59b6261935ba06388",
            "b272645ce2cf0750581a487a0c3793a4",
            "f030371a99f9006af4f92d85466ca0d4",
            "8e93ef7f0c9f7e731389479a04c2c182",
            "9e870c46dc4d337f0a3d109c5e096a6d",
            "bc91ed366176bf1368346e6339a0c514",
            "33b820f57ddd3d60495c66d735596600",
            "1f0b1b71d60cfb72bd66e3be4f87c4d8",
            "6367bb4d23051f1b34051aa801e0e1da",
            "19c8a7385eec6b1005f74079d5677c34",
            "5c4bd57e7802c0714d0dfc7975035664",
            "98c1aa5dd938c69a5ed1d2a87e73acc8",
            "41ef198d413b448212828a3828f22f3b",
            "03f740b84a7f24261b8e39b4610033f2",
            "f0206c61f1d983c786fa03c32eb593b8",
            "7dde0c2dd06491fed4a5cd20e5afab77",
            "80eafc43480b951aabd878cfb685b89d",
            "fa4dd5406a57259610d3cfa3cb44ee4a",
            "1c9fc3a17ad6b2964d9a428c0c17f51f",
            "7092bdc38ec87d6ee73c346d7a2987d6",
            "e4a4f0ad9e28dc4448258a4311945dad",
            "f75b905a3fc1bcc1fc43e623f51cd2a9",
            "69382118066db5532c7f5ac88523e7ab",
            "9c6318eb620df7e08f24dbd1658a7bc7",
            "e6c06b1c4d757ff566d9f84f81a35f81",
            "edf72cdc6c1732e26fd28faa9fa731ee",
            "d63c01e890752ade1cf52afa8598a827",
            "99240726bd86233c3b9334155e030046",
            "80eafc43480b951aabd878cfb685b89d");;
        // 目前只同步了通过认证的高质量信息 auth_status 1 已认证
        $jsq = 0;
        for ($i = 0;$i<106;$i++) {
            $offset = $i * 1000;
            $girls = GirlsModel::query()->whereIn("uuid",$members)->offset($offset)->limit(1000)->get();
            echo $offset,"\r\n";
            foreach ($girls as $girl) {
                $member = MemberModel::query()->where('uuid',$girl->uuid)->first();
                if ($member && $member['role_id'] == 16) {
                    // 基础信息不保存
                    echo  $i,'     ',$jsq,"\r\n";
                    $girlmeet = new GirlMeetModel();
                    $girlmeet->aff = $member->aff;
                    $girlmeet->title = $girl->title;
                    $girlmeet->type = 1;
                    $girlmeet->cover = $girl->cover;
                    $girlmeet->cityCode = $girl->cityCode;
                    $girlmeet->cityName = $girl->cityName;
                    $girlmeet->girl_age = $this->avgValue($girl->age);
                    $girlmeet->girl_height = $this->avgValue($girl->height);
                    $girlmeet->girl_weight = random(41 - 49);
                    $girlmeet->girl_cup = ucfirst(substr($girl->cup, 0, 1) ? substr($girl->cup, 0, 1) : 'C');
                    $girlmeet->girl_price = $this->getPrice($girl);
                    $girlmeet->girl_service_type = $girl->services;
                    $girlmeet->girl_business_hours = $girl->business_time ?: '12:00-24:00';
                    $girlmeet->girl_tags = '巨乳,美腿,女神';
                    $girlmeet->desc = $girl->content;
                    $girlmeet->favorites = 0;
                    $girlmeet->original_price = 0;
                    $girlmeet->buy_count = 0;
                    $girlmeet->comment_count = 0;
                    $girlmeet->view = 0;
                    $girlmeet->cast_way = NULL;
                    $girlmeet->popular = NULL;
                    $girlmeet->mark = 0;
                    $girlmeet->buy_price = 200;
                    $girlmeet->phone = $this->getPhone($girl);
                    $girlmeet->address = $girl->address;
                    $girlmeet->status = 0;
                    $girlmeet->sort = 0;
                    $girlmeet->broker_id = $girl->broker_id;
                    $girlmeet->created_at = date('Y-m-d H:i:s', TIMESTAMP);
                    $girlmeet->updated_at = date('Y-m-d H:i:s', TIMESTAMP);
                    $girlmeet->save();


                    // 图片集保存
                    $girlsPotos = \GirlPhotosModel::query()->where("g_id", $girl->id)->get();
                    foreach ($girlsPotos as $key => $photo) {
                        GirlMeetValueModel::query()->insert([
                            'girl_meet_id' => $girlmeet->id,
                            'video_cover' => '',
                            'url' => $photo->img,
                            'type' => GirlMeetValueModel::TYPE_IMAGE,
                            'sort' => 0,
                            'created_at' => date('Y-m-d H:i:s', TIMESTAMP),
                            'updated_at' => date('Y-m-d H:i:s', TIMESTAMP)
                        ]);
                        if ($key == 0) {
                            $girlmeet->cover = $photo->img;
                            $girlmeet->save();
                        }

                    }
                }
            }
        }
    }

    function getPrice($girl){
        if (trim($girl->price)){
            return $girl->price;
        }
        $string = '';
        if (!empty($girl->package_price1)){
            $string = $girl->package_price1."/p";
        }
        if (!empty($girl->package_price2)){
            $string .= ";".$girl->package_price2."/2p";
        }
        return  $string;
    }

    /**
     * 导入妻友的视频数据
     */
    public function mvAction()
    {

        $i= 1;
        for ($page = 40;$page<=116;$page++){
            $offset = ($page- 1) * 1000;
            $list =[];
            $list = \DB::table('jd_mv')
                ->offset($offset)
                ->limit(1000)
                ->get();
            if($list){
                foreach ($list as $item){
                    $via = '91porn';
                    $exists = MvModel::query()->where('_id',$item->_id)->where('via',$via)->first();
                    if ($exists){
                    }else{

                        $mv = [
                            '_id'=>$item->_id,
                            'title'=>$item->title,
                            'mv_type'=> 2,
                            'source_240'=>$item->source_240,
                            'duration'=>$item->duration,
                            'cover_horizontal'=> $item->screenmode == 2 ? $item->cover_thumb : '',
                            'cover_vertical'=>$item->screenmode == 1 ? $item->cover_thumb : '',
                            'via'=>'91porn',
                            'tags'=>$item->tags,
                            'desc'=>$item->descriptions,
                            'isfree'=> $item->coins>0 ? 0 : 1,
                            'coins'=> $item->coins * 10,
                        ];
                        MvModel::create($mv);
                        $i++;
                    }
                }
            }
            echo 'page:'.$page."\n";

        }
        echo '执行'.$i.'条';
    }
    // 求平均值
    private function avgValue($string){
        $array = explode("-",$string);
        if (count($array) == 2){
            return ceil((intval($array[0]) + intval($array[1]))/2);
        }else{
            return intval($array[0]);
        }
    }

    //
    private function getPhone($girl){
        if (!empty($girl->phone) && strlen($girl->phone)>6){
            return $girl->phone;
        }
        if (!empty($girl->wechat) && strlen($girl->wechat)>6){
            return $girl->wechat;
        }
        if (!empty($girl->potato_contact) && strlen($girl->potato_contact)>6){
            return $girl->potato_contact;
        }
        if (!empty($girl->contact) && strlen($girl->contact)>6){
            return $girl->contact;
        }
        return false;
    }

    //
    public function chatorderAction(){
        // 2022-04-13  14:20
        $orders = \ChatOrderModel::query()->where("status",2)->get();
        foreach ($orders as $order){
            $chatexist = GirlChatModel::query()->where("id",$order['girl_chat_id'])->first();
            if (!$chatexist){
                echo $order->girl_aff,"裸聊信息不存在，\r\n";
            }
            $broker = BrokerModel::query()->where("aff",$order->girl_aff)->first();
            if (!$broker){
                echo $order->girl_aff,"商家不存在，\r\n";
            }
            $broker_price = intval(($broker->percent * $order->buy_price) /100);
            MemberModel::query()->where("aff",$broker->aff)->increment("income_money",$broker_price);
            $order->pay_price = $broker_price;
            $order->offical_percent_price = $order->buy_price - $broker_price;
            $order->save();
            $string = $order->id."     ".$order->buy_price."        ".$broker_price."        ".$order->offical_percent_price."\r\n";
            echo $string;
            error_log($string, 3, self::$baseDir.'order.log');
        }
    }
}