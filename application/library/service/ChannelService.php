<?php

namespace service;


use Tbold\Serv\biz\BizAppVisit;
use Tbold\Serv\biz\BizDown;
use Tbold\Serv\biz\BizOrder;
use Tbold\Serv\biz\BizUser;
use Tbold\Serv\biz\BizWebVisit;

class ChannelService
{
    public static function addIpVisit($uid, $uuid, $behavior, $ip, $agent_id, $agent_name, $agent_channel, $dwell_time, $created_at)
    {
        BizAppVisit::make([
            'uid'           => $uid,
            'uuid'          => $uuid,
            'behavior'      => $behavior,
            'ip'            => $ip,
            'dwell_time'    => $dwell_time,
            'agent_id'      => $agent_id,
            'agent_name'    => $agent_name,
            'agent_channel' => $agent_channel,
            'created_at'    => $created_at,
        ]);
        BizAppVisit::pushQueue();
    }

    public static function addIpInstall($uid, $uuid, $ip, $agent_id, $agent_name, $agent_channel, $created_at)
    {
        BizUser::make([
            'uid'           => $uid,
            'uuid'          => $uuid,
            'ip'            => $ip,
            'created_at'    => $created_at,
            'agent_id'      => $agent_id,
            'agent_name'    => $agent_name,
            'agent_channel' => $agent_channel,
        ]);
        BizUser::pushQueue();
    }

    public static function addIpOrder($uid, $uuid, $ip, $order_sn, $order_price, $pay_price, $agent_id, $agent_name, $agent_channel, $created_at)
    {
        BizOrder::make([
            'uid'           => $uid,
            'uuid'          => $uuid,
            'ip'            => $ip,
            'order_sn'      => $order_sn,
            'order_price'   => $order_price,
            'pay_price'     => $pay_price,
            'agent_id'      => $agent_id,
            'agent_name'    => $agent_name,
            'agent_channel' => $agent_channel,
            'created_at'    => $created_at,
        ]);
        BizOrder::pushQueue();
    }

    public static function addIpDownload($url, $type, $ip, $agent_id, $agent_name, $agent_channel, $created_at)
    {
        BizDown::make([
            'url'           => $url,
            'type'          => $type,
            'ip'            => $ip,
            'agent_id'      => $agent_id,
            'agent_name'    => $agent_name,
            'agent_channel' => $agent_channel,
            'created_at'    => $created_at
        ]);
        BizDown::pushQueue();
    }

    public static function addIpReferrer($url, $ip, $agent_id, $agent_name, $agent_channel, $created_at)
    {
        BizWebVisit::make([
            'url'           => $url,
            'ip'            => $ip,
            'agent_id'      => $agent_id,
            'agent_name'    => $agent_name,
            'agent_channel' => $agent_channel,
            'created_at'    => $created_at,
        ]);
        BizWebVisit::pushQueue();
    }

    private static function reportByMember(\MemberModel $member, $ip, \Closure $fn)
    {
        if ($member->channel == 'self') {
            return;
        }

        $channel =  cached("vista:" . $member->channel)->fetchPhp(function () use ($member){
            return \ChannelModel::query()
                ->where('channel_id', $member->channel)
                ->first();
        } , 86400);
        if (!$channel) {
            return;
        }

        $fn($member, $channel, $ip, time());
    }

    private static function reportByChannel($channel, $ip, \Closure $fn)
    {
        if ($channel == 'self') {
            return;
        }
        $channel =  cached("vista:" . $channel)->fetchPhp(function () use ($channel){
            return \ChannelModel::query()
                ->where('channel_id', $channel)
                ->first();
        } , 86400);
        if (!$channel) {
            return;
        }

        $fn($channel, $ip, time());
    }

    public static function reportVisit($member, $ip, $event_no, $dwell_time = 0)
    {
        return;
        self::reportByMember($member, $ip, function ($member, $channel, $ip, $ts) use ($event_no, $dwell_time) {
            // $uid, $uuid, $behavior, $ip, $agent_id, $agent_name, $agent_channel, $dwell_time, $created_at
            self::addIpVisit(
                $member->uid,
                $member->uuid,
                $event_no,
                $ip,
                $channel->channel_num,  // $agent_id
                $channel->name,         // $agent_name
                $channel->channel_id,   // $agent_channel
                $dwell_time,
                $ts
            );
        });
    }

    public static function reportInstall($member, $ip)
    {
        return;
        self::reportByMember($member, $ip, function ($member, $channel, $ip, $ts) {
            // $uid, $uuid, $ip, $agent_id, $agent_name, $agent_channel, $created_at
            self::addIpInstall(
                $member->uid,
                $member->uuid,
                $ip,
                $channel->channel_num,  // $agent_id
                $channel->name,         // $agent_name
                $channel->channel_id,   // $agent_channel
                $ts
            );
        });
    }

    public static function reportOrder($member, $ip, $order_sn, $order_price, $pay_price)
    {
        return;
        self::reportByMember($member, $ip, function ($member, $channel, $ip, $ts) use ($order_sn, $order_price, $pay_price) {
            // $uid, $uuid, $ip, $order_sn, $order_price, $pay_price, $agent_id, $agent_name, $agent_channel, $created_at
            self::addIpOrder(
                $member->uid,
                $member->uuid,
                $ip,
                $order_sn,
                $order_price,
                $pay_price,
                $channel->channel_num,  // $agent_id
                $channel->name,         // $agent_name
                $channel->channel_id,   // $agent_channel
                $ts
            );
        });
    }

    public static function reportDownload($channel, $ip, $type, $url)
    {
        return;
        self::reportByChannel($channel, $ip, function ($channel, $ip, $ts) use ($type, $url) {
            // $url, $type, $ip, $agent_id, $agent_name, $agent_channel, $created_at
            self::addIpDownload(
                $url,
                $type,
                $ip,
                $channel->channel_num,  // $agent_id
                $channel->name,         // $agent_name
                $channel->channel_id,   // $agent_channel
                $ts
            );
        });
    }

    public static function reportReferrer($channel, $ip, $url)
    {
        return;
        self::reportByChannel($channel, $ip, function ($channel, $ip, $ts) use ($url) {
            // $url, $ip, $agent_id, $agent_name, $agent_channel, $created_at
            self::addIpReferrer(
                $url,
                $ip,
                $channel->channel_num,  // $agent_id
                $channel->name,         // $agent_name
                $channel->channel_id,   // $agent_channel
                $ts
            );
        });
    }
}