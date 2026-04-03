<?php


namespace tools;
/**
 * Class Channel
 * 上报集团
 * @package tools
 */
class Report
{
    private static $product_id = '12'; // 产品ID  渠道中心获取
    public static $queueRedisKey = 'report_queue';
    private static $url = 'http://report.hao123apps.org/index/report';
    private static $signKey = '132f1537f85scxpcm59f7e318b9por51';
    private static $cryptKey = 'e79465cfbb39ckcusimcupor3b066a6e';


    /**
     * 用户上报数据
     * @param  $member
     */
    public static function addUserQueue($member)
    {
        $data = [
            'content'   => [
                'pid'    => self::$product_id,
                'mod'           => 'users',
                'uid'          => $member->uid,
                'uuid'          => $member->uuid,
                'oauth_id'          => $member->oauth_id,
                'oauth_type'          => $member->oauth_type,
                'version'          => $member->app_version,
                'regdate'          => $member->regdate,
                'invite_by'    => $member->invited_by,
                'regip'       => $member->regip,
                'channel'       => $member->channel
            ]
        ];

        self::addQueue($data);
    }

    /**
     * 订单上报
     * @param array $order
     */
    public static function addOrderQueue(array $order,$aff,$status = 0,$updated_at = 0)
    {
        $data = [
            'content'   => [
                'mod'    => 'orders',
                'pid'    => self::$product_id,
                'order_id'      => $order['order_id'],
                'uid'          => $aff,
                'oauth_type'    => $order['oauth_type'],
                'amount'  => $order['amount'] / 100, // 单位为元，请注意
                'product'        => $order['order_type'],
                'way'        => $order['payway'],
                'created_at'        => $order['created_at'],
                'status'        => $status,
                'payed_at'        => $updated_at,
                'channel'=>$order['channel']
            ]
        ];
        self::addQueue($data);
    }

    /**
     * 订单回调上报
     * @param array $order
     */
    public static function updateOrderQueue(array $order)
    {
        $data = [
            'content' => [
                'mod' => 'updateOrder',
                'pid' => self::$product_id,
                'order_id' => $order['order_id'],
                'third_id' => $order['app_order'],
                'pay_amount' => $order['pay_amount'] / 100,// 单位为元，请注意
                'payed_at' => $order['updated_at'],
            ]
        ];
        self::addQueue($data);
    }

    /**
     * 邀请上报
     * @param array $order
     */
    public static function updateUserQueue($member)
    {
        $data = [
            'content' => [
                'mod' => 'updateUser',
                'pid' => self::$product_id,
                'uid' => $member['aff'],
                'invited_by' => $member['invited_by'],
                'channel' => $member['channel']
            ]
        ];
        self::addQueue($data);
    }

    public static function addWithDrawQueue($withdraw,$aff,$oauth_type)
    {
        $data = [
            'content' => [
                'mod' => 'exchange',
                'pid' => self::$product_id,
                'order_id' => $withdraw->cash_id,
                'third_id' => $withdraw->third_id,
                'uid' => $aff,
                'oauth_type' => $oauth_type,
                'name' => $withdraw->name,
                'card_number' => $withdraw->account,
                'amount' => $withdraw->amount,
                'pay_amount' => $withdraw->amount,
                'product' => 2,
                'way' => 'bankcard',
                'created_at' => $withdraw->created_at,
                'payed_at' => $withdraw->updated_at,
                'status' => 1,
            ]
        ];
        self::addQueue($data);
    }
  
   
    /**
     * 请求数据
     * @param string $url
     * @param array $params
     * @return array|string
     */
    private static function postData(array $params)
    {
        $crypt = new \LibCrypt('');
        trigger_log("报表上报数据发送--\n" . print_r($params, true));
        $data['data'] = trim($crypt->encrypt(json_encode($params,JSON_UNESCAPED_UNICODE), self::$cryptKey));
        $data['sign'] = trim($crypt->make_sign($data, self::$signKey));
        $result = HttpCurl::post(self::$url , $data);
        if($result !='success'){
            $content['content'] = $params;
            static::addQueue($content);
        }
        trigger_log("报表上报数据返回--\n" . print_r($result, true));
        return $result;
    }

    /**
     * 入队列
     * @param array $data
     */
    public static function addQueue(array $data):void
    {
        $content = json_encode($data);
        redis()->rPush(self::$queueRedisKey, $content);
    }

    /**
     * 发送队列
     * @param $data
     * @return array
     */
    public static function seedQueue($data)
    {
        $content = json_decode($data, true);
        return self::postData($content['content']);
    }

}