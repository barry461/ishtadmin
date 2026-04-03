<?php

namespace service;

use AdsModel;
use Carbon\Carbon;
use GirlChatAdditionMapModel;
use GirlChatMoneyLogModel;
use GirlChatOrderAdditionModel;
use GirlChatOrderModel;
use MemberModel;
use MoneyLogModel;
use ProductPrivilegeModel;
use UserPrivilegeModel;

class GirlChatService
{
    public $member;

    function __construct($member)
    {
        $this->member = $member;
    }


    public function pre_list()
    {
        $return["tips"] = setting("girl_chat_tips", "购买会员前，请注册账号，妥善保存，以防账号遗失");
        $return["banner"] = CommonService::getADsByPosition(AdsModel::POSITION_GIRL_CHAT);

        $tags = [
            [
                "id"    => -1,
                "title" => "热门"
            ],
            [
                "id"    => -2,
                "title" => "最新"
            ]
        ];

        $return["nav_list"] = array_merge($tags, \GirlChatTagModel::getTags());

        return $return;
    }

    public function listGirl($page = 1, $limit = 24, $tag_id = 0)
    {
        return \GirlChatModel::getList($page, $limit, $tag_id);
    }

    public function search($word, $page, $limit)
    {
        return \GirlChatModel::search($word, $page, $limit);
    }

    public function detailGirl($id)
    {
        return \GirlChatModel::getDetail($id, $this->member->aff);
    }

    public function toggleFavorite($girl_chat_id)
    {
        $aff = $this->member->aff;
        $redisKey = \GirlChatFavoriteLogModel::REDIS_KEY_FAVORITE_LIST . ":" . $aff;

        // find favorite
        $exist = \GirlChatFavoriteLogModel::query()
            ->where([
                "aff"          => $this->member->aff,
                "girl_chat_id" => $girl_chat_id
            ])->first();

        if (!$exist) {
            $hasPrivilege= MemberModel::hasFavoritePrivilege(USER_PRIVILEGE, $this->member->aff);
            if(!$hasPrivilege){
                $count = \GirlChatFavoriteLogModel::where("aff", $this->member->aff)->count();
                if($count >= MemberModel::FAVORITE_LIMIT){
                    test_assert(false, "err", 422);
                }
            }

            $data = [
                'aff'          => $aff,
                'girl_chat_id' => $girl_chat_id,
                'created_at'   => \Carbon\Carbon::now(),
                'updated_at'   => \Carbon\Carbon::now(),
            ];
            $isOk = \GirlChatFavoriteLogModel::create($data);
            test_assert($isOk, "系统异常");


            // add favorite list
            redis()->sAdd($redisKey, $girl_chat_id);
            \GirlChatModel::where("id", $girl_chat_id)->increment("favorites", 1);

            \GirlChatModel::clearDetail($girl_chat_id);
            \GirlChatFavoriteLogModel::clearMyList($this->member->aff);
            return ['is_favorite' => 1, 'msg' => '收藏成功'];
        } else {
            $isOk = $exist->delete();
            test_assert($isOk, "系统异常");


            // remove favorite list
            redis()->sRem($redisKey, $girl_chat_id);
            \GirlChatModel::where("id", $girl_chat_id)->decrement("favorites", 1);

            \GirlChatModel::clearDetail($girl_chat_id);
            \GirlChatFavoriteLogModel::clearMyList($this->member->aff);
            return ['is_favorite' => 0, 'msg' => '取消收藏成功'];
        }
    }

    public function favoriteList($page, $limit)
    {
        return \GirlChatFavoriteLogModel::getMyFavorite($this->member->aff, $page, $limit);
    }

    public function commentList($girl_chat_id, $page = 1, $limit = 24)
    {
        return \GirlChatCommentModel::getCommentByGirlChatId($girl_chat_id, $page, $limit);
    }

    public function pre_release()
    {
        $return["girl_tags"] = \GirlChatTagModel::getTags();
        $return["girl_cup"] = json_decode(setting("girl_cup", ""));
        $return["services"] = \GirlChatServiceItemModel::getAllServiceItems();
        $return["addition_items"] = \GirlChatAdditionModel::getAllAdditionItem();

        return $return;
    }


    public function release($title, $girl_tag_ids, $girl_age, $height, $weight, $girl_cup, $price_per_minute,
                            $phone, $show_face, array $service_ids, array $addition_item_ids, array $photos)
    {
        $member = $this->member;
        return transaction(function () use ($member, $title, $girl_tag_ids, $girl_age, $height, $weight, $girl_cup, $price_per_minute, $phone, $show_face, $service_ids, $addition_item_ids, $photos) {
            $services = \GirlChatServiceItemModel::select("title")->whereIn("id", $service_ids)->get()->toArray();
            $girl_tags = \GirlChatTagModel::select("title")->whereIn("id", $girl_tag_ids)->get()->toArray();
            $data["aff"] = $member->aff;
            $data["cover"] = $photos[0]["url"] ?? "";
            $data["title"] = $title;
            $data["girl_tags"] = implode(',', array_column($girl_tags, 'title'));
            $data["girl_age"] = $girl_age;
            $data["girl_height"] = $height;
            $data["girl_weight"] = $weight;
            $data["girl_cup"] = $girl_cup;
            $data["price_per_minute"] = $price_per_minute;
            $data["phone"] = $phone;
            $data["show_face"] = $show_face;
            $data["girl_service_type"] = implode(',', array_column($services, 'title'));
            $data["status"] = \GirlChatModel::STATUS_INIT;

            $create = \GirlChatModel::create($data);
            test_assert($create, '系统异常,异常码:1001', 422);

            // add service items
            $additionItems = \GirlChatServiceItemModel::whereIn("id", $addition_item_ids)->get();
            $addition_maps = [];
            if ($additionItems) {
                foreach ($additionItems as $additionItem) {
                    $addition_maps[] = [
                        'girl_chat_id'          => $create->id,
                        'girl_chat_addition_id' => $additionItem->id
                    ];
                }

                if (!empty($addition_maps)) {
                    $save = GirlChatAdditionMapModel::query()
                        ->upsert($addition_maps, ["girl_chat_id", "girl_chat_addition_id"]);
                    test_assert($save, '系统异常,异常码:1001', 422);
                }
            }

            // store girl chat images
            $resource = [];
            if (!empty($photos)) {
                $sort = count($photos);
                foreach ($photos as $photo) {
                    $resource[] = [
                        'girl_chat_id' => $create->id,
                        'url'          => $photo["url"],
                        'width'        => $photo["width"] ?? 0,
                        'height'       => $photo["height"] ?? 0,
                        'type'         => \GirlChatValueModel::TYPE_IMG,
                        'sort'         => $sort,
                    ];
                    $sort--;
                }

                if (!empty($resource)) {
                    $save = \GirlChatValueModel::insert($resource);
                    test_assert($save, '系统异常,异常码:1001', 422);
                }
            }

            // clear my list
            \GirlChatModel::clearMyList($member->aff);
            return true;
        });
    }

    public function my($cate, $page = 1, $limit = 24)
    {
        return \GirlChatModel::getListByAff($this->member->aff, $cate, $page, $limit);
    }

    public function buy($girl_chat_id, $time_set_id, $addition_ids, $user_contact)
    {
        return transaction(function () use ($girl_chat_id, $time_set_id, $addition_ids, $user_contact) {
            /** @var \GirlChatModel $girl_chat */
            $girl_chat = \GirlChatModel::where(["id" => $girl_chat_id, "status" => \GirlChatModel::STATUS_PASS])->first();
            test_assert($girl_chat, "无效的女孩聊天 ID!", 422);

            /** @var \MemberModel $member */
            $member = \MemberModel::where("aff", $this->member->aff)->lockForUpdate()->first();
            test_assert($member, "无效用户!", 422);

            /** @var \GirlChatSetModel $time_set */
            $time_set = \GirlChatSetModel::query()->where("id", $time_set_id)->first();
            test_assert($time_set, "设置的时间无效!", 422);

            $additions = \GirlChatAdditionModel::whereIn("id", $addition_ids)->get();
            test_assert($additions->count() == count($addition_ids), "添加项目出了问题!", 422);

            $needCoin = $time_set->time * $girl_chat->price_per_minute;
            $additionAmount = 0;
            if ($additions->count() > 0) {
                foreach ($additions as $addition) {
                    /** @var \GirlChatAdditionModel $addition */
                    $additionAmount += $addition->coin;
                    $needCoin += $addition->coin;
                }
            }

            $discountPercent = \GirlChatModel::getDiscount(USER_PRIVILEGE);
            $needCoinAfterDiscount = $needCoin * $discountPercent;
            if ($member->money < $needCoinAfterDiscount) {
                test_assert(false, "金币不足", 422);
            }

            (new UserService($member))->updateMoney(
                $member->aff,
                MoneyLogModel::TYPE_SUB,
                MoneyLogModel::SOURCE_BUY_CHAT,
                $needCoinAfterDiscount,
                $girl_chat_id,
                "买女孩聊天"
            );

            $order = GirlChatOrderModel::create([
                'aff'              => $member->aff,
                'order_id'         => uniqid(),
                'girl_chat_id'     => $girl_chat->id,
                'price_per_minute' => $girl_chat->price_per_minute,
                'addition_amount'  => $additionAmount,
                'total_amount'     => $needCoinAfterDiscount,
                'discount_amount'  => $needCoin == $needCoinAfterDiscount ? 0 : ($needCoin - $needCoinAfterDiscount),
                'girl_chat_set_id' => $time_set->id,
                'user_contact'     => $user_contact,
                'desc'             => "{$girl_chat->title} | {$time_set->title} | " . (\GirlChatModel::SHOW_FACE[$girl_chat->show_face] ?? ""),
                'time'             => $time_set->time,
                'status'           => GirlChatOrderModel::STATUS_INIT
            ]);
            test_assert($time_set, "系统错误!");
            $additionName = '';
            if ($additions->count() > 0) {
                foreach ($additions as $addition) {
                    GirlChatOrderAdditionModel::create([
                        'order_id'    => $order->id,
                        'addition_id' => $addition->id,
                        'gold'        => $addition->coin,
                        'name'        => $addition->name
                    ]);
                    $additionName = $additionName ? $additionName . ',' . $addition->name : $addition->name;
                }
            }

            $girl_chat->buy_count += 1;
            $girl_chat->save();

            // send potato & tg message
            if (!is_null($order)) {
                // bot 派单扫描
                \BotModel::addBotData(\BotModel::TYPE_CHAT, $order->getAttributes());
            }

            GirlChatOrderModel::clearOrderList($girl_chat->aff);
            GirlChatOrderModel::clearMyOrderList($member->aff);
        });
    }

    public function orderList($cate, $page, $limit)
    {
        return GirlChatOrderModel::getOrderList($cate, $this->member->aff, $page, $limit);
    }

    public function myOrder($cate, $page, $limit)
    {
        return GirlChatOrderModel::getMyOrder($cate, $this->member->aff, $page, $limit);
    }

    public function confirmOrder($orderId)
    {
        $aff = $this->member->aff;

        /** @var GirlChatOrderModel $order */
        $order = GirlChatOrderModel::query()
            ->where(["aff" => $aff, "id" => $orderId, "status" => GirlChatOrderModel::STATUS_INIT])
            ->first();
        test_assert($order, "订单无效!", 422);

        $girl_chat = \GirlChatModel::find($order->girl_chat_id);
        test_assert($girl_chat, "订单无效!", 422);

        return transaction(function () use ($order, $girl_chat, $aff) {
            $order->status = GirlChatOrderModel::STATUS_FINISH;
            $order->save();

            /** @var \MemberModel $member */
            $member = UserService::getUserByAff($girl_chat->aff);
           if(!is_null($member)){
               $shareProfit = (\MemberModel::SHARE_GIRL_CHAT[$member->auth_status] ?? 0);
               $shareAmount = $order->total_amount * $shareProfit;
               (new UserService($member))->updateMoney(
                   $member->aff,
                   MoneyLogModel::TYPE_ADD,
                   MoneyLogModel::SOURCE_BUY_CHAT,
                   $shareAmount,
                   $aff,
                   "用户购买聊天 #{$order->id} 分享: ". ($shareProfit * 100) . "%"
               );
           }

            GirlChatOrderModel::clearMyOrderList($aff);
            GirlChatOrderModel::clearOrderList($girl_chat->aff);

            return true;
        });
    }


    public function comment($id, $girl_face, $girl_service, $comment)
    {
        return transaction(function () use ($id, $girl_face, $girl_service, $comment) {
            // create comment
            \GirlChatCommentModel::create([
                "aff"          => $this->member->aff,
                "girl_chat_id" => $id,
                "face"         => $girl_face,
                "service"      => $girl_service,
                "comment"      => $comment,
                "status"       => \GirlChatCommentModel::STATUS_WAIT
            ]);

            \GirlChatCommentModel::clearCommentList($id);
            return true;
        });
    }

    public function complaint($id, $types, $content, $img, $city_name)
    {
        $order = \InfoVipModel::query()->find($id);
        test_assert($order, "帖子不存在", 422);

        $create = \GirlChatComplaintModel::query()->create([
            "aff"                => $this->member->aff,
            "girl_chat_order_id" => $order->id,
            "girl_chat_id"       => $id,
            "content"            => $content,
            "img"                => $img,
            "types"              => $types,
            "status"             => \GirlChatComplaintModel::STATUS_WAIT,
            "ip_str"             => USER_IP,
            "city_name"          => $city_name,
            "created_at"         => Carbon::now(),
            "updated_at"         => Carbon::now()
        ]);
        test_assert($create, "系统错误！");

        return true;
    }
}