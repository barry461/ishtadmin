<?php

namespace service;

use Carbon\Carbon;
use LotteryItemModel;
use LotteryLogModel;
use LotteryModel;
use LotterySignModel;
use LotteryUserModel;
use MemberModel;
use MoneyLogModel;
use ProductModel;
use tools\MemberRand;
use UserProductModel;

class LotteryService
{
    public function qd(MemberModel $member, $lottery_id){
        $is_active = LotteryModel::isActive($lottery_id);
        test_assert($is_active, '活动无效');
        $data = date('Y-m-d');
        $user_sign = LotterySignModel::where('aff', $member->aff)->where('date', $data)->first();
        test_assert(empty($user_sign), '今日已签到');
        $user_sign = LotterySignModel::make();
        $user_sign->aff = $member->aff;
        $user_sign->date = $data;
        $isOk = $user_sign->save();
        test_assert($isOk, '签到失败，请重试');
        //添加免费次数
        $lottery_num = LotteryUserModel::addUserLottery($member, 1, $lottery_id);
        return $lottery_num;
    }

    public function init(MemberModel $member, LotteryModel $lottery){
        /** @var LotteryUserModel $lottery_user */
        $lottery_user = LotteryUserModel::getInfoByAff($member->aff, $lottery->id);
        if (empty($lottery_user)){
            $lottery_num = 0;
        }else{
            $lottery_num = $lottery_user->val;
        }
        $data = date('Y-m-d');
        $user_sign = LotterySignModel::where('aff', $member->aff)->where('date', $data)->first();
        $sign_status = !empty($user_sign) ? 1 : 0;
        $topList = Collect([]);
        if ($lottery->lottery_begin <= Carbon::now() && $lottery->lottery_end >= Carbon::now()){
            $topList = $this->topList();
        }
        return [
            'id'            => $lottery->id,
            'token'         => getID2Code($member->uid),
            'lottery_num'   => $lottery_num,
            'sign_status'   => $sign_status,
            'top_list'      => $topList
        ];
    }

    public function topList()
    {
        $list = redis()->lRange('lottery_top_list_eleven', 0, 10);
        $topList = Collect([]);
        if ($list){
            foreach ($list as $item) {
                $member = json_decode($item);
                $topList->push($member);
            }
        }else{
            $record = json_encode([
                'username' => MemberRand::randNickname(),
                'result' => '666现金红包',
            ]);
            redis()->lPush('lottery_top_list_eleven', $record);
            $record = json_encode([
                'username' => MemberRand::randNickname(),
                'result' => '飞机杯',
            ]);
            redis()->lPush('lottery_top_list_eleven', $record);
            $record = json_encode([
                'username' => MemberRand::randNickname(),
                'result' => '外围空降',
            ]);
            redis()->lPush('lottery_top_list_eleven', $record);
            $record = json_encode([
                'username' => MemberRand::randNickname(),
                'result' => '666现金红包',
            ]);
            redis()->lPush('lottery_top_list_eleven', $record);
            $record = json_encode([
                'username' => MemberRand::randNickname(),
                'result' => '飞机杯',
            ]);
            redis()->lPush('lottery_top_list_eleven', $record);
            $record = json_encode([
                'username' => MemberRand::randNickname(),
                'result' => '充气娃娃',
            ]);
            redis()->lPush('lottery_top_list_eleven', $record);
        }
        return $topList;
    }

    public function draw(MemberModel $member, $lottery_id, $num){
        $free_num = 0;
        /** @var LotteryUserModel $lottery_user */
        $lottery_user = LotteryUserModel::getInfoByAff($member->aff, $lottery_id);
        if (!empty($lottery_user)){
            $free_num = $lottery_user->val;
        }
        $use_free_num = 0;
        $use_coins_num = 0;
        //免费次数大于0，
        if ($free_num > 0){
//            $date = date('Y-m-d');
            //今日已使用免费次数
//            $day_use_free_num = LotteryLogModel::where('uid', $member->uid)
//                ->where('format_date', $date)
//                ->where('lottery_id', $lottery_id)
//                ->where('pay_time', LotteryLogModel::PAY_FREE_NUM)
//                ->count('log_id');
            //已使用满10次
//            if ($day_use_free_num >= 10){
//                $use_coins_num = $num;
//            }else{
                //今日剩余可使用免费次数
//                $day_remain = 10 - $day_use_free_num;
//                $use_free_num = min($free_num, $day_remain);
                $use_free_num = min($free_num, $num);
                $use_coins_num = max($num - $use_free_num, 0);
//            }
        }else{
            $use_coins_num = $num;
        }
        $lottery = LotteryModel::find($lottery_id);
        $jp_titles = transaction(function () use ($member, $lottery, $lottery_user, $num, $use_free_num, $use_coins_num){
            //扣费
            $kf_type_arr = [];
            if ($use_coins_num > 0){
                $member->subMoney((int)$use_coins_num * 10, MoneyLogModel::SOURCE_LOTTERY, '抽奖扣费', $lottery);
                for ($i=1; $i<= $use_free_num; $i++){
                    $kf_type_arr[] = 1;
                }
            }
            if ($use_free_num > 0){
                //扣除次数
                $lottery_user->val = $lottery_user->val - $use_free_num;
                $lottery_user->updated_at = \Carbon\Carbon::now();
                $isOk = $lottery_user->save();
                test_assert($isOk, '抽奖异常，请重试');
                for ($i=1; $i<= $use_free_num; $i++){
                    $kf_type_arr[] = 0;
                }
            }

            //抽奖
            $item = LotteryItemModel::draw($lottery->id, $num);
            $jp_titles = [];
            /** @var LotteryItemModel $value */
            foreach ($item as $key => $value){
                //奖品是高价值
                if ($value->giveaway_type == LotteryItemModel::GIVEAWAY_TYPE_MANUAL){
                    /** @var LotteryItemModel $lottery_item */
                    $lottery_item = LotteryItemModel::useWritePdo()->find($value->item_id);
                    //奖品消耗完了
                    if ($lottery_item->total_lucky == 0){
                        //谢谢惠顾
                        $value = LotteryItemModel::bad($value->lottery_id);
                    }
                }
                $jp_titles[] = [
                    'stay' => $value->giveaway_id
                ];
                //中奖日志
                $isOk = LotteryLogModel::createBy($member, $value, $kf_type_arr[$key]);
                test_assert($isOk, '记录添加失败');
                //中奖送东西
                $itOk = $this->giveaway($member, $value);
                test_assert($itOk, '操作失败');
                if ($value->giveaway_type != LotteryItemModel::GIVEAWAY_TYPE_MANUAL){
                    jobs([LotteryItemModel::class, 'decLuckyNum'], [$value->item_id]);
                }else{
                    //扣除奖品数
                    $isOk = $value->decrement('total_lucky');
                    test_assert($isOk, '操作失败');
                }

                //进入轮播
                if ($value->is_show == LotteryItemModel::SHOW_OK) {
                    $record = json_encode([
                        'username' => $member->nickname,
                        'result'   => $value->item_name
                    ]);
                    redis()->lPush('lottery_top_list_eleven', $record);
                    redis()->lTrim('lottery_top_list_eleven',0,10);
                }
            }
            //异步执行
            jobs([LotteryModel::class, 'addLottery'], [$value->lottery_id, $num]);

            return $jp_titles;
        });
        return [
            'lottery_num' => $free_num - $use_free_num,
            'lottery_title' => $jp_titles,
        ];
    }

    /**
     * 赠送礼物
     */
    public function giveaway(MemberModel $member, LotteryItemModel $item)
    {
        $result = true;

        switch ($item->giveaway_type) {
            case LotteryItemModel::GIVEAWAY_TYPE_COINS://金币
                return $this->giveaway_coin($member, $item);
            case LotteryItemModel::GIVEAWAY_TYPE_VIP_JK://季卡
                return $this->giveaway_vip($member, $item->giveaway_num);
            case LotteryItemModel::GIVEAWAY_TYPE_NONE:
            case LotteryItemModel::GIVEAWAY_TYPE_MANUAL:
                return true;
        }
        return $result;
    }


    /**
     * @desc 赠送金币
     */
    protected function giveaway_coin(MemberModel $member, LotteryItemModel $item)
    {
        $coin = $item->giveaway_num;
        $description = MoneyLogModel::formatDescription('lottery_lucky', ['coins' => $coin]);
        $rs = $member->addMoney($coin, MoneyLogModel::SOURCE_LOTTERY, $description, $item);
        test_assert($rs , '日志添加失败');
        return true;
    }

    /**
     * @desc 送VIP
     */
    protected function giveaway_vip(MemberModel $member, $product_id){
        $product = ProductModel::find($product_id);
        $isOk = UserProductModel::buy($member, $product);
        test_assert($isOk, '处理用户权限卡失败');
        return true;
    }
}