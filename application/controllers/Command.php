<?php


class CommandController extends \Yaf\Controller_Abstract
{
    public function indexAction()
    {
        if (PHP_SAPI !== 'cli') {
            exit('You can not access this module');
        }
        $module = $_SERVER['argv'][1] ?? '';
        $argv = $_SERVER['argv'][2] ?? '';
        if ($module == 'channel-keep') {
            $this->channelKeep();
        } elseif ($module == 'channel-user') {
            $this->channelMember();
        } else {
            $server = new \commands\Kernel($module, $argv);
        }
    }

    private function channelMember()
    {
        $start = '2023-05-26';

        $max = MemberModel::where('regdate', '<', $start)->max('uid');
        echo $start , "\r\n";
        echo "上报用户: \r\n";
        $uuidAry = [];
        MemberModel::where('uid', '>', $max)
            ->where('channel', '!=', 'self')
            ->chunkById(1000, function ($items) use (&$uuidAry) {
                collect($items)->each(function (MemberModel $member) use (&$uuidAry) {
                    if ($member->oauth_type == 'channel') {
                        return;
                    }
                    $channel = ChannelModel::findByAff($member->invited_by);
                    if (empty($channel)) {
                        return;
                    }
                    $data = $member->toArray();
                    \tools\Channel::addUserQueue($data);
                    echo $member->uid, "\r";
                    $uuidAry[] = $member->uuid;
                });
            });
        $start = strtotime($start);
        $max = OrdersModel::where('created_at', '<', $start)->max('id');
        echo $start , "\r\n";
        echo "上报订单: \r\n";

        try {
            OrdersModel::with('member')
                ->whereIn('uuid' , $uuidAry)
                ->where('id' ,'>' , $max)
                ->chunkById(1000 , function ($items){
                    collect($items)->each(function (OrdersModel $order) {
                        $data = $order->toArray();
                        if (empty($order->member)){
                            return;
                        }
                        unset($data['member']);
                        $data['channel'] = $order->member->channel;
                        $data['invited_by'] = $order->member->invited_by;
                        $data['phone'] = $order->member->phone;
                        \tools\Channel::addOrderQueue($data);
                        if ($order->status == OrdersModel::PAY_STAT_SUCCESS){
                            \tools\Channel::updateOrderQueue($data);
                        }
                        echo $order->id, "\r";
                    });
                });
        }catch (\Throwable $e){
            echo $e;
        }
    }

    private function channelKeep()
    {
        $fn = function (ChannelModel $agent, $datestr, $lastvisitstr = null) {
            $where = [
                ['channel', '=', $agent->channel_id],
                ['regdate', '>=', date('Y-m-d 00:00:00', strtotime($datestr))],
                ['regdate', '<=', date('Y-m-d 23:59:59', strtotime($datestr))],
            ];
            if ($agent->agent_level > 1) {//子渠道 只统计直推 没有列表
                $where[] = ['invited_by', '=', $agent->aff];
            }
            if ($lastvisitstr) {
                $where[] = ['lastactivity', '>=', $lastvisitstr];
            }
            return \MemberModel::where($where)->count('uid');
        };

        $chunk = 500;//根据项目渠道多少来设置 500 1000 都行
        ChannelModel::query()
            ->chunkById($chunk, function ($items) use ($fn, $chunk) {
                collect($items)->each(function (ChannelModel $agent) use ($fn) {
                    list($aff, $agent_id, $channel) = [
                        $agent->aff, $agent->channel_num, $agent->channel_id
                    ];
                    //昨日安装
                    $yesterday = $fn($agent, '-1 days');
                    $yesterday2 = $fn($agent, '-2 days');
                    //前日安装在昨日的活跃量
                    $yesterdayActive = $fn($agent, '-2 days', date('Y-m-d 00:00:00', strtotime('-1 days')));
                    //入队列上报
                    tools\Channel::keepData($channel, $aff, $yesterday, $yesterday2, $yesterdayActive, [
                        'agent_username' =>$agent->channel_id
                    ]);
                    echo "username: {$agent->channel_id} ,渠道：{$channel} , aff: {$aff} , 昨日安装：{$yesterday} , 前天安装：{$yesterday2} , 前天安装昨日留存：{$yesterdayActive}\r\n";
                });
                echo "======== chunk {$chunk} ========" . PHP_EOL . PHP_EOL;
            });
    }
}