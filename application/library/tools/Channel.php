<?php


namespace tools;
use Carbon\Carbon;
/**
 * Class Channel
 * 上报联盟
 * @package tools
 */
class Channel
{
    private static $product_id = '41'; // 产品ID  渠道中心获取
    public static $queueRedisKey = 'channel_queue';
    private static $url = 'https://union-api.hao123apps.info/api.php/';
    private static $signKey = '9c9fd68f9ea34214088c436aa9a913bc';
    private static $cryptKey = '5a3b3c58d959b37fa149b2cf6b900f4c';
    const SIGN_KEY = '9c9fd68f9ea34214088c436aa9a913bc';//签名key
    const ENCRYPT_KEY = '5a3b3c58d959b37fa149b2cf6b900f4c';//加密key
     // 渠道订单回调数据上报结构
//    $data = [
//        'type'  => 'updateOrder',
//        'content'   => [
//            'product_id' => self::$product_id,
//            'order_sn'      => $order['order_id'],
//            'pay_amount'    => $ordre['pay_amount'],
//            'status'        => '1',
//            'updated_at'    => $order['updated_at'],
//        ]
//    ];

    /**
     * 新增渠道用户上报数据
     * @param array $member
     */
    public static function addUserQueue(array $member):void
    {
        $member['channel'] = self::getChannelNumById($member['channel']);
        $data = [
            'type'      => 'addUser',
            'content'   => [
                'product_id'    => self::$product_id,
                'uid'           => $member['uid'],
                'uuid'          => $member['uuid'],
                'invite_by'    => $member['invited_by'],
                'channel'       => $member['channel'],
                'created_at'    => Carbon::parse($member['regdate'])->timestamp,
                'device'        => tran2device($member['oauth_type'])
            ]
        ];

        self::addQueue($data);
    }

    /**
     * 订单上报渠道中心
     * @param array $order
     */
    public static function addOrderQueue(array $order)
    {
        $order['channel'] = self::getChannelNumById($order['channel']);
        $data = [
            'type'      => 'addOrder',
            'content'   => [
                'product_id'    => self::$product_id,
                'order_sn'      => $order['order_id'],
                'order_type'    => $order['order_type']==\ProductModel::GOODS_TYPE_VIP?0:1, // 0 vip  1 j金币
                'uuid'          => $order['uuid'],
                'invite_by'    => $order['invited_by'],
                'channel'       => $order['channel'],
                'order_amount'  => $order['amount'] / 100, // 单位为元，请注意
                'status'        => 0,
                'created_at'    => Carbon::parse($order['created_at'] ?? null)->timestamp,
                'device'        => tran2device($order['oauth_type'])
            ]
        ];
        self::addQueue($data);
    }

    /**
     * 订单回调上报渠道中心
     * @param array $order
     */
    public static function updateOrderQueue(array $order)
    {
        $data = [
            'type' => 'updateOrder',
            'content' => [
                'product_id' => self::$product_id,
                'order_sn' => $order['order_id'],
                'pay_amount' => $order['pay_amount'] / 100,// 单位为元，请注意
                'status' => '1',
                'updated_at' => Carbon::parse($order['updated_at'])->timestamp,
            ]
        ];
        self::addQueue($data);
    }

    /**
     * 获取渠道中心渠道广告
     * @param $channel
     * @return array|bool|mixed|string
     */
    public static function getAdvert($channel,$pos)
    {
        $redisKey = 'channel:advert:list:' . $channel.':'.$pos;
        $data = redis()->getWithSerialize($redisKey);
        if (!$data) {
            $params = [
                'channel'   => $channel,
                'product_id'=> self::$product_id,
            ];
            $result = self::postData('api/ads/ads', $params);
            $result = $result[$pos] ?? [];
            $data = [];
            foreach ($result as $key => $item) {
                // TODO 序列化返回数据
                $data[$key] = [
                    'id'        => $item['id'],
                    'title'     => $item['title'],
                    'url'   => parse_url($item['image'],PHP_URL_PATH),
                    'link'       => $item['link_url'],
                    'type'      => 3
                ];
            }
            if (!empty($data)) {
                redis()->setWithSerialize($redisKey, $data, 86400);
            }
        }
        return $data;
    }

    /**
     * 渠道中心运用中心数据
     * @param $channel
     * @return array|bool|mixed|string
     */
    public static function getApps($channel)
    {
        $data = RedisService::get('channel_apps_list_' . $channel);
        if (!$data) {
            $params = [
                'channel'   => $channel,
                'product_id'=> self::$product_id
            ];

            $data  = self::postData('api/ads/appCenter', $params);
            $best = [
                'desc'  => '宅男必备精品',
                'type'  => 'best',
                'items' => [],
            ];

            $rec = [
                'desc'  => '热门推荐',
                'type'  => 'rec',
                'items' => [],
            ];

            if (!empty($data)) {
                foreach ($data as $key => $datum) {
                    // TODO 序列化返回数据
                    if ($key < 4) {
                        $temp = [
                            'id'    => $datum['id'],
                            'title' => $datum['name'],
                            'desc'  => $datum['tips'],
                            'status'=> 1,
                            'type'  => 'best',
                            'sort'  => $key,
                            'apk'   => $datum['channel_url'],
                            'img'   => $datum['big_image'],
                            'icon'  => $datum['logo'],
                            'created_at'    => TIMESTAMP
                        ];
                        $best['items'][] = $temp;
                    } else {
                        $rec['items'][] = [
                            'id'    => $datum['id'],
                            'title' => $datum['name'],
                            'desc'  => $datum['tips'],
                            'status'=> 1,
                            'type'  => 'rec',
                            'sort'  => $key,
                            'apk'   => $datum['channel_url'],
                            'img'   => $datum['big_image'],
                            'icon'  => $datum['logo'],
                            'created_at'    => TIMESTAMP
                        ];
                    }
                }
                $data = [
                    $best, $rec
                ];
            }
        }
        RedisService::set('channel_apps_list_' . $channel, $data, 7200);
        return $data;

    }
    public static function manSend($data){
        $url = 'api/originData/' . $data['type'];
        return self::postData($url, $data['content']);
    }

    /**
     * 请求渠道数据
     * @param string $url
     * @param array $params
     * @return array|string
     */

    private static function postData(string $url, array $params)
    {
        $crypt = (new \LibCrypt())->setKey(self::SIGN_KEY, self::ENCRYPT_KEY);
        //\LibCrypt::$debug = false;
        $data = $crypt->replyData($params);
        $result1 = HttpCurl::post(self::$url . $url, json_decode($data,true));
        //\LibCrypt::$debug = true;
        $result = $crypt->checkInputData(json_decode($result1,true),false);
        trigger_log("上报返回数据3--". print_r($result, true));
        if (!isset($result['status']) || $result['status'] != 1) {
            return [];
        }
        return $result['data'] ?? [];
    }

    /**
     * 入队列
     * @param array $data
     */
    public static function addQueue(array $data):void
    {
        if (empty(self::$product_id)){
            return;
        }
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
        $url = 'api/originData/' . $content['type'];
        return self::postData($url, $content['content']);
    }

    /**
     * 通过渠道id获取联盟渠道号
     * @param $channel_num
     */
    public static function getChannelNumById($channel_num)
    {
        $channel = \ChannelModel::query()->where('channel_id', $channel_num)->first();
        return $channel->channel_num ?? $channel_num;//兼容错误的channel_id
    }

    /**
     * @param string $fan_id 番号
     * @param float $price //价格大于0 必须 只上报购买的
     * @param string|null $date eg：2021-12-12
     * @return array|void
     */
    public static function avReport(string $fan_id, float $price, int $aff, string $date = null)
    {
        $postData = [
            'product_id' => self::$product_id,
            'fan_id'     => $fan_id,
            'price'      => $price,
            'day'        => is_null($date) ? date("Y-m-d") : $date,
            'aff'        => $aff,
        ];
        // dd($postData);
        return self::postData('api/originData/avReport', $postData);
    }

    public static function keepData($channel, $aff, $num_install, $num_pre_install, $num_pre_serve, array $extendData = [], ?string $date = null)
    {
        $postData = [
            'product_id'      => self::$product_id,
            'channel'         => $channel,//一般为推广链接上的渠道标识 或用户表字段 build_id
            'aff'             => $aff,//渠道关联用户编号
            'num_install'     => $num_install,//昨日安装
            'num_pre_install' => $num_pre_install,//前日安装
            'num_pre_serve'   => $num_pre_serve,//前日安装昨日活跃
            'day'             => is_null($date) ? date("Y-m-d") : $date,
        ];
        if ($extendData) {
            $postData = array_merge($postData, $extendData);
        }
        $queue = [
            'type' => 'reportKeep',
            'content' => $postData
        ];
        self::addQueue($queue);
    }

    public static function keepDataV2(?\MemberModel $member)
    {
        if (empty($member) || empty($member->channel) || empty($member->invited_by)){
            return ;
        }
        $register_date = date('Y-m-d', strtotime($member->regdate));
        $last_visit_date = date('Y-m-d', strtotime($member->lastactivity));
        $queue = [
            'type'    => 'reportKeepV2',
            'content' => [
                'product_id'      => self::$product_id,
                'channel'         => $member->channel,//一般为推广链接上的渠道标识 或用户表字段 build_id
                'uid'             => $member->uid,//用户的编号
                'invited_aff'     => $member->invited_by,
                'register_date'   => $register_date,//注册日期
                'last_visit_date' => $last_visit_date,//最后更新日期
                'agent_username'  => $member->channel
            ],
        ];
        self::addQueue($queue);
    }
}