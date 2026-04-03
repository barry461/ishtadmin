<?php

/**
 *  定时完成订单处理
 */
class ChatorderController extends \Yaf\Controller_Abstract
{

    static $baseDir = APP_PATH . '/storage/logs/';

    // 2天以上的未完成订单  进行商家分成
    public function finishAction()
    {

    }

    // 2天以上的未完成订单  进行商家分成
    public function confirmbrokerAction()
    {
        echo "招嫖订单维护开始","\r\n";;
        $meetorders =  MeetOrderModel::query()->selectRaw("count(1) as num,girl_meet_id")
            ->groupBy('girl_meet_id')
            ->get()->toArray();
        foreach ($meetorders as $meetorder){
            $meet = GirlMeetModel::query()->where("id",$meetorder['girl_meet_id'])->first();
            if (!$meet){
                continue;
            }
            $meet->buy_count = $meetorder['num'];
            $meet->comment_count = MeetOrderModel::query()
                ->where("girl_meet_id",$meetorder['girl_meet_id'])
                ->where("comment","!=","")
                ->count();
            $meet->save();

        }
        echo "招嫖订单维护完毕","\r\n";

        echo "商家开始维护", "\r\n";;
        BrokerModel::query()->eachById(function (BrokerModel $broker) {
            $girls_num = GirlMeetModel::query()->where("aff", $broker->aff)->where("status", GirlMeetModel::STATUS_PASS)->count();
            $broker->girl_num = $girls_num;
            $broker->save();
            echo $broker->id, "    ", $girls_num, "\r\n";
        });
        echo "商家开始维护完毕","\r\n";



    }

    // 2天以上的未完成订单  进行商家分成
    public function heightAction()
    {
        $meets = GirlMeetModel::query()->where("girl_height",0)->get();
        foreach ($meets as $meet){
            if ($meet->girl_height == 0){
                $meet->girl_height = rand(152,172);
                $meet->save();
            }
        }
    }
}