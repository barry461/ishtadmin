<?php


namespace service;

use Carbon\Carbon;
use MoneyIncomeLogModel;
use OrdersModel;
use PayTypeModel;
use PayWayModel;
use MemberModel;
use MoneyLogModel;
use TaskProgressLogModel;
use tools\HttpCurl;
use UserProxyCashBackDetailModel;
use WithdrawLogModel;
use UserProductModel;

class PayorderService
{

    /**
     * @param MemberModel $member
     * @param $type
     * @param bool $showMore
     * @return array
     */
    static function getProductList(MemberModel $member , $type, bool $showMore):array
    {
        $where = ['status' => 1, 'type' => $type];
        if ($type == \ProductModel::GOODS_TYPE_VIP){
            $where['show_more'] = 1;
        }
        $list = \ProductModel::where($where)
            ->with([
                'rights' => function ($query) {
                    return $query->with('right');
                },
                'pays' => function ($query) {
                    return $query->with('way');
                },
            ])
            ->orderByDesc('sort_order')
            ->orderByDesc('id')
            ->get();

        $list = $list->map(function (\ProductModel $item) use ($member) {
            if ($item->visible_type == \ProductModel::VISIBLE_TYPE_NEW_USER && !$member->new_user) {
                return null;
            }
            if ($item->pays->count() === 0) {
                return null;
            }

            $item->makeHidden('pays' , 'rights');
            $item->setAttribute('pay' , $item->pays->pluck('way')->sortByDesc('order')->filter()->values());
            $item->setAttribute('right' , $item->rights->pluck('right')->filter()->values());
            return $item;
        })->filter()->values();

        return  $list->toArray();
    }

    static function getPayTypeList()
    {
        $list = PayTypeModel::where(['status' => 1])
            ->orderBy('order', 'asc')->get()->toArray();
        if ($list) {
            foreach ($list as $key => $row) {
                $list[$key]['pay_way'] = PayWayModel::where('status', 1)
                    ->orderBy('order', 'asc')
                    ->get()->toArray();
            }
        }
        return $list;
    }

    static function getPayType($string = false)
    {
        $list = PayTypeModel::where(['status' => 1])
            ->get()->toArray();

        if ($list) {
            if ($string) {
                $return = '';
                foreach ($list as $v) {
                    $return .= $return ? ',' . $v['name'] : $v['name'];
                }
                return $return;
            } else {
                return $list;
            }
        }
    }
    static function getPayWay($string = false)
    {
        $list = PayWayModel::where(['status' => 1])
            ->get()->toArray();

        if ($list) {
            if ($string) {
                $return = '';
                foreach ($list as $v) {
                    $return .= $return ? ',' . $v['channel'] : $v['channel'];
                }
                return $return;
            } else {
                return $list;
            }
        }
    }

    static function getProductDetail($product_id)
    {
        return \ProductModel::query()->where('id', $product_id)->first();
    }

    static function getOrderDetail($uuid, $order_id)
    {
        if (empty($uuid) || empty($order_id)) {
            return null;
        }
        $row = \OrdersModel::where(['uuid' => $uuid, 'order_id' => $order_id])->orderByDesc('id')->first();
        return $row ? $row->toArray() : null;
    }

    static function getLeastOrder($uuid, $oder_id = '')
    {
        $w = [];
        $uuid && $w['uuid'] = $uuid;
        $oder_id && $w['order_id'] = $oder_id;
        if (!$w) {
            return null;
        }
        $row = \OrdersModel::where($w)->limit(1)->orderByDesc('id')->first();
        return $row ? $row->toArray() : null;
    }

    static function getOrderList($uuid, $page, $limit, $type)
    {
        $is_vip = $type == \ProductModel::GOODS_TYPE_VIP;
        $where= [
            ['uuid','=',$uuid],
            ['order_type',$is_vip?'=':'!=',\ProductModel::GOODS_TYPE_VIP],
        ];
        $rows = OrdersModel::where($where)->orderByDesc('id')->offset(($page - 1) * $limit)->limit($limit)->get()->toArray();
        if ($rows) {
            foreach ($rows as &$item) {
                $item['amount'] = moneyFormat($item['amount']);
                $item['pay_amount'] = moneyFormat($item['pay_amount']);
                $item['status_text'] = OrdersModel::PAY_STAT[$item['status']];
                $item['payway'] = PayWayModel::where('channel', $item['payway'])->first()->name;
                unset($item['goods_info']);
                unset($item['order_id']);
            }
        }
        return $rows;
    }

    /**
     * 生成支付签名
     * @param $array
     * @param string $signKey
     * @return string
     */
    static function makePaySign($array, $signKey = '')
    {
        if (empty($array)) {
            return '';
        }
        //md5(app_name . app_type . aff . amount . 加密key);
        $string = @sprintf("%s%s%s%s%s", $array['app_name'], $array['app_type'] ?? '', $array['aff'] ?? '', $array['amount'] ?? '', $signKey);
        //        trigger_error('reqData:'.'makeSign:before:'. $string);
        $str = md5($string);
        //        trigger_error('makeSign:After:'. $str);
        return $str;
    }

    public static function setOrdersImg($order_id, $img_url)
    {

        $data = [
            'order_number' => $order_id,
            'order_img' => $img_url,
        ];
        $liveChange = config('live.live_api_url') . '/index/index/order_number';
        $data = json_encode($data);
        $signdata = self::publicEncrypt($data);
        $postdata = ['data' => $signdata];
        $curl = new \tools\HttpCurl();
        $result = $curl->curlPost($liveChange, $postdata);
        $result = json_decode($result, true);
        \OrdersModel::query()->where('order_id', $order_id)->update(['desc_img' => $img_url]);
        return $result;
    }

    /**
     * @param string $data
     * @return null|string
     * @uses 公钥加密
     */
    private static function publicEncrypt($data = ''): ?string
    {
        if (!is_string($data)) {
            return null;
        }
        return openssl_public_encrypt($data, $encrypted, self::getPrvateKey()) ? base64_encode($encrypted) : null;
    }

    private static function getSignPem()
    {
        $publicKey = "-----BEGIN PUBLIC KEY-----
MIICIjANBgkqhkiG9w0BAQEFAAOCAg8AMIICCgKCAgEAxMUYPeBY+2Hv5g3Je0rJ
ik51qR/rsRQkAvBkKr/hGYjy90EgeiVZfHsXWnw4NsYdbPkyf2ksaxYojRsvkbk4
XcPX+v8e3hbyADJJ9QJgfdAQe60osDcpfTU9g9TJcDa+02RpigJC9matvFtjKuZQ
MCSM0mQtKZvufI/ah5do25d0v0/kTiTrBsQbljoOOvlsmw0EPJOmB3TO8+50UiZc
CywNknHyqrVdBE0qCBBeRvoxNf+8Vzlq1TZuwi8FoNpMR+cXE/7iQIn14FBIv48M
RNLrArlS8NIlPOsY9v8t38d5XswzGGivyoYnUN10Bv4xvUOgTkmoEMx6RSMI3kld
07kehOhybbZrJKWpcIUF0bxYMSY3p26y/W+VnH4lmEv05xU7Myh1doEUC6NLh+jw
uE38tUIp91DtFlXhuuqRz2Bx4y2K6tOPvdn8tfddY5GElGRifPgFPdZrVgi/9Vxb
KByWpQGyh8uj20W1+45cqPjd1W+6ASFAR/v4nu/KCy+NtRFM+bFKc+w1N6tfJPwM
4w5rmuPcpZc//SvDOLH3s7KXQEXsLYJ9+FlHX3HBwlouAQSbBFUA2bLczQ9odm4e
tMeGsJxBYcyZWf3TSCVH9J18iZEklBKb+Kyb2ncNSBFVLQe5lHWHE2JSV8t6XA+4
mdm0WlN5+xu6CP3CWJhDchECAwEAAQ==
-----END PUBLIC KEY-----";
        return $publicKey;
    }

    private static function getPrvateKey()
    {
        $privateKey = '-----BEGIN PUBLIC KEY-----
MIICIjANBgkqhkiG9w0BAQEFAAOCAg8AMIICCgKCAgEArGF9L6/6UnBYz0A7l30L
og7ubXEYMdM33wW3DPlPEaIUbqAtKhvBgBvvqdvw3ri2vPoNc5zAzaOExF2IoXQl
yl4vmdtnncJVrlv4ARrJuNYhMBLbF8jzkgv6ohWDm0wn7sk69grQuNPIRuVwbSZf
OuzMcwVV6GcFZ6Vcivh7APjW50O9a0l5Sf4kmcKfgEiEFtkaoVm3rcB4Ib8ohkt7
mGu6RyUCXzCi4EhSH5H/lXYHR78BgTxUh3PgREPaqQ0YB5haeBxqzd2Up/qB6Ko+
PDcL2nYMPxe6nC82V9x1Uxbh5wBscedEu1zKvBhjEAU/8nWC9Hqrm3jwARCCuDeU
9gtAQ6pZ+MXj6tTQiL6yJa8tIErbBKJ9yM8EutsUwFTRp7vWXYf2BICRQrYPfbcK
OnYV5s/xtlMBimtgL62Bqogv19f5kEPiEyhnjBw7/Ss6y2QFljNDZ0e1kItamiTM
ZTwMfRPfnAzwVmXyRkzTD+sxE4EGLSj9eGO8bBmXVyldkfdGyvW4DaNqB0TUxLpW
RNI1aI/d5kIvqS4ag/KfkLOR22E0wvLSNWoHLrEpn+UHdt09zw2P26xe2i+ag0iH
5LtAj8zCkiBNBNSscA+0QWWCNqmYaNXds5aQuZOuG8gD+TNCS+fI1KVWK2c+B8Md
DefWQVrFEO1nTYFAoM6BKHMCAwEAAQ==
-----END PUBLIC KEY-----';
        return $privateKey;
    }

    /**
     *
     *  支付或 提现回调签名验证
     *
     * @param array $data  回调数据
     * @param string $sign 签名key
     * @return string       签名
     */
    static function makeCallBackPaySign($data, $sign = 'scb37537f85ext23766194765b9epa51'): string
    {
        if (isset($data['sign'])) {
            unset($data['sign']);
        }
        ksort($data);
        $string = '';
        $string = http_build_query($data, null, '&');
        return md5($string . $sign);
    }

    protected static function createAgentOrder($data){
        $product = $data['product'];
        if ($product == 'game') {
            list($desc, $orderType) = ['游戏充值', \ProductModel::GOODS_TYPE_GAME];
        } elseif ($product == 'vip') {
            list($desc, $orderType) = ['vip充值', \ProductModel::GOODS_TYPE_VIP];
        } elseif ($product == 'coins') {
            list($desc, $orderType) = ['coins充值', \ProductModel::GOODS_TYPE_COIN];
        } else {
            throw new \Exception('不支持的类型');
        }
        $pay_money = $data['pay_money'];
        $start = $pay_money * 0.92;
        if ($pay_money > 10000) {
            $end = $pay_money * 1.0001;
        } elseif ($pay_money > 1000) {
            $end = $pay_money * 1.001;
        } else {
            $end = $pay_money * 1.02;
        }
        $productIdAry = \AgentTmpPayingModel::where('aff' , $data['aff'])
            ->whereBetween('price', [$start * 100, $end * 100])
            ->where('created_at' , '>' , Carbon::now()->subHours(2)->toDateTimeString())
            ->orderByDesc('id')
            ->limit(3)
            ->pluck('product_id')
            ->toArray();
        $query = \ProductModel::queryBase();
        if (empty($productIdAry)){
            $wayId = PayWayModel::where('channel', 'agent')->value('id');
            if ($wayId) {
                $productIdAry = \PayMapModel::where('way_id', $wayId)->pluck('product_id');
                $query->whereIn('id', $productIdAry);
            }
        }else{
            $query->whereIn('id', $productIdAry);
        }
        /** @var \ProductModel $productObject */
        $productObject = $query
            ->where('status' , \ProductModel::STATUS_LISTED)
            ->whereBetween('promo_price', [$start * 100, $end * 100])
            ->orderByDesc('sort_order')
            ->where('type', $orderType)
            ->first();
        if (empty($productObject)){
            //throw new \Exception('产品不存在');
        }
        /** @var MemberModel $member */
        $member = MemberModel::query()->where('aff' , $data['aff'])->first();
        if (empty($member)){
            throw new \Exception('用户不存在');
        }
        $order = \OrdersModel::create([
            'uuid' => $member->uuid,
            'oauth_type' => $member->oauth_type,
            'product_id' => $productObject ? $productObject->id : 0,
            'amount' => $data['pay_money'] * 100,
            'pay_amount' => $data['pay_money'] * 100,
            'status' => OrdersModel::PAY_STAT_TO_PAY,
            'order_id' => $data['order_id'],
            'app_order' => $data['third_id'],
            'channel' => $data['channel'],
            'descp' => $desc,
            'order_type' => $orderType,
            'pay_type_sdk' => 0,
            'payway' => $data['channel'],
            'expired_at' => 0,
            'pay_type' => $data['channel'],
            'pay_url' => $data['channel'],
            'goods_info' =>json_encode($productObject ? $productObject->getAttributes() : null , JSON_UNESCAPED_UNICODE),
            'build_id' => $member->channel
        ]);
        if (empty($order)){
            throw new \Exception('订单生成失败');
        }
        return $order;
    }

    /**
     *  支付回调业务判断处理
     *
     * @param $data
     * @param bool $is_mock
     */
    static function callBackPayProcess($data, $is_mock=false)
    {
        trigger_log("我方收到,支付 notifyCommon 异步回调 Post 数据--\n" . print_r($data, true));
        list(
            'order_id' => $order_id,
            'third_id' => $third_id,
            'pay_money' => $pay_money,
            'pay_time' => $pay_time,
            'success' => $success,
            'sign' => $sign
            )  = $data;

        $signKey = config('pay.pay_signkey', 'scb37537f85spaycm59f7e318b9epa51');
        $sign = $data['sign'];
        unset($data['sign']);
        $my_sign = self::makeCallBackPaySign($data, $signKey);
        if ($sign != $my_sign) {
            trigger_log("支付异步回调签名不正确 我方签名--\n".print_r($my_sign, true));
            if (T_ENV == 'product' && empty($is_mock)) {
                die('fail');
            }
        }
        extract($data);
        if (200 != $success) {
            trigger_log("支付异步回调支付不是成功状态--\n" . print_r($data, true));
            die('fail');
        }
        try {
            \DB::beginTransaction();
            if (data_get($data, 'channel') == 'AgentPay') {
                $order = self::createAgentOrder($data);
                test_assert($order , '代理充值产品不存在');
                if ($order->product_id == 0){
                    $member = $order->member;
                    if (!empty($member)){
                        $member->increment('order_count');
                    }
                    $order->status = OrdersModel::PAY_STAT_SUCCESS;
                    $order->saveOrFail();
                    goto run_success;
                }
            } else {
                $order = \OrdersModel::queryBase('order_id', $order_id)->first();
            }
            test_assert($order , '订单不存,失败');

            /** @var OrdersModel $order */
            if ($order->status == OrdersModel::PAY_STAT_SUCCESS) {
                goto run_success;
            }
            $real_pay = $pay_money * 100;
            $msg = sprintf("订单应付：%.2f元, 实付：%.2f元" , $order->amount / 100 , $real_pay / 100);
            // 4元向下浮动视为正常流程处理
            if ($order->amount <= 0 || $order->amount > $real_pay + 400) {
                throw new \Exception('价格异常:' . $msg);
            }
            if ($order->payway == 'usdt') {
                $real_pay = $real_pay * 7;
            }

            $product = $order->product_snapshot;
            test_assert($product , '产品快照异常');
            $member = $order->member;
            test_assert($member , '用户不存在');
            $coins = $product->coins + $product->free_coins;
            if ($coins > 0) {
                $template = $product->free_coins ? 'buy_and_give' : 'buy';
                if ($product->free_coins) {
                    $template_args = [
                        'coin' => $product->coins,
                        'free_coins' => $product->free_coins,
                    ];
                } else {
                    $template_args = ['coin' => $product->coins,];
                }
                $description = MoneyLogModel::formatDescription($template, $template_args);
                $rs = $member->addMoney($coins, MoneyLogModel::SOURCE_TOPPED, $description, $order);
                test_assert($rs , '日志添加失败');
            }

            # 添加金币免费观看时效
//            if (
//                isset($product->free_view_day) && $product->free_view_day > 0
//            ) {
//                $carbon = Carbon::now();
//                if ($member->free_view == MemberModel::FREE_VIEW) {
//                    $freeMvAt = $carbon->max(Carbon::now())
//                        ->max(Carbon::parse($member->free_view_at))
//                        ->addDays($product->free_view_day)
//                        ->toDateTimeString();
//                } else {
//                    $freeMvAt = $carbon->max(Carbon::now())
//                        ->addDays($product->free_view_day)
//                        ->toDateTimeString();
//                }
//                $member->free_view = MemberModel::FREE_VIEW;
//                $member->free_view_at = $freeMvAt;
//                $isOk = $member->save();
//                test_assert($isOk, '视频免费观看金币时间添加异常');
//            }

            $isOk = UserProductModel::buy($member, $product);
            test_assert($isOk, '处理用户权限卡失败');
            $isOk = $order->update([
                'app_order' => $third_id,
                'pay_amount' => $real_pay,
                'msg' => $msg,
                'status' => OrdersModel::PAY_STAT_SUCCESS
            ]);
            test_assert($isOk, '修改订单状态失败');
            // vip充值，给用户返现
            if ($product->type == \ProductModel::GOODS_TYPE_VIP && $member->channel == 'self') {
                if (empty($member->invited_by)) {
                    goto run_success;
                }
                $inviteMember = MemberModel::queryBase('aff', $member->invited_by)->first();
                if (empty($inviteMember)) {
                    goto run_success;
                }
            }
            if (in_array($product->id, explode(',', setting('91pron_lnhd', '')))) {
                jobs([self::class, 'give_dsp_cdk'], [$member->aff , $product->id]);
            }
            $member->increment('order_count');
            if ($member->new_user){
                \SysTotalModel::incrBy('order:new-user');
            }
            run_success:
            \DB::commit();

            //上报渠道V2数据
            //ChannelService::reportOrder($member,$member->lastip,$order->order_id,$order->amount / 100,$real_pay / 100);

            if (isset($member) && $member instanceof MemberModel) {
                $key = sprintf("%s%d", \UserPrivilegeModel::REDIS_KEY_USER_PRIVILEGE, $member->aff);
                redis()->bulkDel($key);
                //删除权限缓存
                cached($key)->clearCached();
                if ($member->channel != 'self') {
                    \tools\Channel::updateOrderQueue($order->toArray());
                }
                $member->clearCached();
                self::redisStat($member,$product,$pay_money);
            }
            jobs([\LotteryFreeLogModel::class, 'recharge'], [$member->aff, $order->amount / 100]);
            
            echo 'success';
        } catch (\Throwable $e) {
            \DB::rollBack();
            trigger_log($e);
            die('fail');
        }

    }

    /**
     * @param MemberModel $member
     * @param ProductModel $product
     * @param $money
     * @return void
     * @throws RedisException
     */
    public static function redisStat(\MemberModel $member, \ProductModel $product, $money)
    {
        //充值成功订单数/回调订单数
        \SysTotalModel::incrBy('notify-order');
        //总充值
        \SysTotalModel::incrBy('pay:recharge-amount-total', $money);
        if (in_array($product->type, [1, 2])) {
            //新用户充值
            if ($member->new_user){
                \SysTotalModel::incrBy('pay:recharge-amount-new', $money);
                \SysTotalModel::incrBy('pay:recharge-num-new');
            }
            if ($product->type == 1) {
                \SysTotalModel::incrBy('pay:recharge-vip-amount', $money);
            } else {
                \SysTotalModel::incrBy('pay:recharge-coins-amount', $money);
            }
        }
        //产品购买数量
        $keyPro = "product:buy:" . date('Ymd');
        redis()->hIncrBy($keyPro, $product->pname, 1);
        if (redis()->ttl($keyPro) == -1) {
            $todayExpire = strtotime(date('Y-m-d', strtotime('+1 day 12 hours'))) - TIMESTAMP;
            redis()->expire($keyPro, $todayExpire);
        }
    }

    /**
     * @param $aff
     *
     * @throws \Exception
     */
    static function give_dsp_cdk($aff , $productId)
    {
        try {
            return ;
            $pidStr = setting('give:rsapp:pid', '');
            $pidAry = explode(',', $pidStr);
            if (!in_array($productId, $pidAry)) {
                return;
            }
            $web_url = 'https://meat.app003.cc';
            $name = '肉食男女裸聊';
            $key1 = 'rsapp:cdk';
            $key2 = "$key1:give";
            $len = redis()->sCard($key1);
            $len1 = redis()->sCard($key2);
            if ($len1 == 0 && $len == 0 ){
                $text = <<<CDK
MESWIHq
MFbqpr9
MH6torx
MHQtvn8
MP7Z3DM
MVJao60
MYPr4Sd
Mb4ZHvY
McCzq3h
MgYsVNK
MlCPtjX
MokiNLX
MrFKwm7
MtNpg70
MuladUQ
Mwa5aiZ
MwmfxeU
N1q6Guu
N5ZZ9lh
N80bll0
NERtJmq
NMyiqGb
NNavMuX
NR3cb5I
NbS5oSQ
NhbZ2Qp
Nl7D9Y1
NlTW9g8
NmCTu9s
Nn6msF1
Np421dc
Nr23dGw
NtJK0s1
O4MUDwr
O4k8UYN
OBfJ9ee
ODO50t8
OFJQ0lZ
OLeeQZ1
OQoahks
OVJ2nHU
OXaA0zR
Ocz6xsf
OgEH8Ro
Oohbzf0
OpSTL2m
Oqwn7yd
OtPCkCK
OtxZuA4
Owy2YOL
OyJAPh8
Oz6d78D
P7ULhfF
PAFuosU
PDe1Quk
PFPGUsf
PKWxizf
PLujP2E
PTehvfU
PVjoZAr
PWhAUEd
PaaTtrL
PjYQeXX
PomcPAi
Ppevsp7
PqpmWOX
PtSvyUN
Q19C5Sw
Q3nZOpX
QGbunvP
QI0EsWr
QM56nfp
QVyGJit
QWddCrE
QWw0fL5
QcsZjot
Qi5eLyO
QipXLCf
QlNuuSf
Qml9pxE
Qpb3sUX
QsrhOJR
Qtdq5P1
QumRP8j
QxeG5eZ
QyEYFR0
RAoICCV
RKZvqoB
RPy4HSl
RYChb7J
RZXyOKC
Ra0wNF9
RfwmUC0
RhBNT59
RofRSVb
S5iNb2j
S7YxS8M
S8xUDXJ
SH7wmQX
SHpl4YU
SOGcGKg
SToiZnb
SUiGhEY
SW4W1or
SbAX8Yp
SblWXSe
SdYQN02
Sly5F2F
So0ALKm
SpjhAQt
SuxgBvO
Swoe9jM
SzRLnGr
T670y3I
T9KAwKL
TS25W59
TVDuDiS
TWOyxow
TaGx5WO
U0LXvhV
U8uwspx
UGYeQII
UGoGKLO
UHwsCUZ
UKZzarE
UKjqcNj
URscXSo
UV1GPx0
Ua9QAT6
Uc48zCO
UjVorLX
UxDQXMt
V3WeDYp
V3aUdK2
V77od5N
V8GobNZ
VBUoXfc
VK436aM
VKXFuLb
VTp8pyI
VUtmpQT
VUvlAi2
VWDJeVL
VXhv2ue
VZsxda3
VaqntNN
Vf6eJOg
VgzY7HT
ViduAwN
Vuk9GLP
Vx9fAyt
Vyq4jhu
W1BxnPL
W8btizy
WB3LabG
WEz4TCW
WJ55o7V
WM4V7il
WMIEbK6
WMnIOl5
WPgvoms
WUHNVWr
WiSAo9d
WmRecBG
WpVpNF7
Wr2BdL9
WsHeFY5
WuyZNVi
Wwt03zO
WxFRg8D
WyecKMN
WzbduQ0
X0bA9BE
X3j1UGe
XCiLEDV
XErbnk1
XMzJmeP
XRazteg
XVtibPG
XX0ZJQI
XY3j8ug
Xa8rC6V
XcRdAAt
XdddJtu
Xj7xnfq
Xo2Yj1L
XrhDSBQ
Xwfb1C8
XxuQxMY
Y09RPoJ
Y1bPoxm
Y2A8cNz
Y2MXEEY
Y83ySg3
YBpsecj
YCwS23I
YFYIS4V
YJkuoi3
YKQIGvz
CDK;
                $ary = collect(explode("\n",$text))->map(function ($v){ return trim($v); })->filter()->shuffle()->toArray();
                redis()->sAddArray($key1 , $ary);
                $len = count($ary);
            }
            if ($len == 0 ){
                return ;
            }
            $json = setting('rsapp:cdk:config', '{}');
            $json = json_decode($json, true);
            if ($json === false) {
                $json = [];
            }
            $v = intval($json[$productId] ?? '0');
            if (empty($v)){
                return ;
            }
            for ($i = 0; $i < $v; $i++) {
                $cdk = redis()->sPop($key1);
                if (redis()->sIsMember($key2, $cdk)) {
                    continue;
                }
                redis()->sAdd($key2 , $cdk);
                \SystemNoticeModel::createBy($aff, "兑换码：【{$cdk}】，下载链接：$web_url", "获得{$name}APP的裸聊兑换码");
            }
        }catch (\Throwable $e){
        }
        return;
        $url = 'https://dsp.aff004.cc/ios/dsp_lmhd';
        if (empty($url)) {
            return;
        }
        $cdk = HttpCurl::post($url, ['pwd' => 'lmhd111']);
        if (empty($cdk)) {
            return;
        }
        // 异步任务，链接可能会超时，尝试运行两次，第二次进行重链接数据库
        retry(2, function ($attempts) use ($aff, $cdk) {
            if ($attempts > 1){
                \DB::reconnect();
            }
            \SystemNoticeModel::createBy($aff, "兑换码：【" . $cdk . "】，下载链接：https://dsp.aff004.cc/", '获得91短视频兑换码');
        });
    }


    /**
     *  提现申请通过 回调业务判断处理
     * @param $data
     * @throws \Throwable
     */
    static function callBackWithDrayProccess($data)
    {
        trigger_log("提现回调 Post 数据--\n" . print_r($data, true));
        $signKey = config('withdraw.key', 'scb37537f85ext23766194765b9epa51');
        $sign = $data['sign'];
        unset($data['sign']);
        //ksort($data);
        //$text = http_build_query($data) . $signKey;
        //$my_sign = md5($text);
        $my_sign = self::makeCallBackPaySign($data, $signKey);
        if ($sign != $my_sign) {
            trigger_log("提现回调回调签名不正确 我方签名--\n" . print_r($my_sign, true));
            // 推送系统消息 提现失败
            die('failed');
        }
        list('app_id' => $app_id, 'success' => $success, 'pay_time' => $pay_time, 'third_id' => $third_id) = $data;
        extract($data);

        $withdraw = \WithdrawLogModel::find($app_id);

        if (!$withdraw) {
            trigger_log("提现回调;未找到申请订单--\n" . print_r($data, true));
            die('success');
        }
        if (\WithdrawLogModel::STATUS_SUCCESS == $withdraw->status) {
            trigger_log("提现回调;订单已处理--\n" . print_r($data, true));
            die('success');
        }
        if (\WithdrawLogModel::STATUS_FAILURE == $withdraw->status) {
            trigger_log("提现回调;订单已处理--\n" . print_r($data, true));
            die('success');
        }
        if (\WithdrawLogModel::STATUS_PASS != $withdraw->status) {
            trigger_log("提现回调;订单状态异常--\n" . print_r($data, true));
            die('failed');
        }
        try {
            if (200 == $success) {
                transaction(function () use ($withdraw, $success, $pay_time, $third_id, $data) {
                    $isOk = $withdraw->update([
                        'updated_at' => $pay_time,
                        'third_id' => $third_id,
                        'status' => \WithdrawLogModel::STATUS_SUCCESS
                    ]);
                    test_assert($isOk, '修改数据失败');
                    $member = $withdraw->member;
                    test_assert($member, '用户不存在');
                    if ($withdraw->type == 5) {
                        //游戏提现
                    } else {
                        \tools\Report::addWithDrawQueue($withdraw, $member->aff, $member->oauth_type);
                    }
                    return true;
                });
            }
            elseif (100 == $success) {
                self::refundCoin($withdraw);
            }
            else{
                throw new \RuntimeException('回调状态错误');
            }
            die('success');
        }catch (\Throwable $e){
            trigger_log($e);
            die('failed');
        }
    }

    /**
     * @param WithdrawLogModel $withdraw
     * @return bool
     * @throws \Throwable
     */
    static function refundCoin(WithdrawLogModel $withdraw): bool
    {

        if ($withdraw->withdraw_from == WithdrawLogModel::WITHDRAW_FROM_PROXY){
            trigger_log("不支持代理提现");
            return true;
        }

        return transaction(function () use ($withdraw) {
            $member = $withdraw->member;
            if (!in_array($withdraw->status, [WithdrawLogModel::STATUS_INIT, WithdrawLogModel::STATUS_PASS])) {
                throw new \Exception('提现订单状态错误');
            }
            $withdraw->status = WithdrawLogModel::STATUS_FAILURE;
            $isOk = $withdraw->save();
            test_assert($isOk, '修改提现订单失败');
            if ($withdraw->withdraw_from == WithdrawLogModel::WITHDRAW_FROM_PROXY) {
                throw new \RuntimeException('代理提现类型错误');
                // 代理退款
                $coins = $withdraw->coins;
                if ($coins <= 0 && $withdraw->charge > 0) {
                    $coins = $withdraw->amount + $withdraw->amount * $withdraw->charge;
                }
                UserService::updateProxyMoney(
                    $member,
                    MoneyLogModel::TYPE_ADD,
                    UserProxyCashBackDetailModel::SOURCE_WITHDRAW_REFUND,
                    $coins,
                    null,
                    null,
                    $member->aff,
                    $withdraw->id
                );
            }
            elseif ($withdraw->withdraw_from == WithdrawLogModel::WITHDRAW_FROM_INCOME) {
                $source = MoneyIncomeLogModel::SOURCE_ADD_WITHDRAW;
                //收益退款
                $member->addIncome($withdraw->coins, $member, $withdraw, $source, '提现失败' , false);
                test_assert($isOk, '退款失败');
            }
            elseif ($withdraw->withdraw_from == WithdrawLogModel::WITHDRAW_FROM_GAOFEI) {
                $source = MoneyIncomeLogModel::SOURCE_GAOFEI;
                //稿费收益退款
                $member->addIncome($withdraw->coins, $member, $withdraw, $source, '提现失败' , false);
                test_assert($isOk, '退款失败');
            }
            else {
                throw new \Exception('提现类型错误');
            }
            return true;
        });
    }

    /**
     * 财务日报表 上报统计
     */
    static function report()
    {
        $start = strtotime(date('Y-m-d', strtotime('-1 day')));
        $end = strtotime(date('Y-m-d 23:59:59', strtotime('-1 day')));
        $monthStart = strtotime(date('Y-m', $start));
        $order = OrdersModel::query()
            ->where('status', OrdersModel::PAY_STAT_SUCCESS)
            ->where('updated_at', '>=', $start)
            ->where('updated_at', '<=', $end);

        $month = OrdersModel::query()
            ->where('status', OrdersModel::PAY_STAT_SUCCESS)
            ->where('updated_at', '>=', $monthStart)
            //->where('updated_at', '<=', $end)
            ->sum('pay_amount');


        $total = \MemberLogModel::query()->select('id')->orderBy('id', 'desc')->first();
        $reg = \MemberModel::query()
            ->where('regdate', '>=', $start)
            ->where('regdate', '<=', $end)
            ->count('uid');
        $active = \MemberLogModel::query()->where('lastactivity', '>=', $start)->count('id');
        $count = $order->count('id');
        $amount = $order->sum('pay_amount');
        $data = [
            'code' => 1,
            'data' => [
                'activity' => $active, //日活
                'reg' => $reg, //新增
                'total' => $total->id, //总用户
                'amount' => intval($amount / 100), //日冲
                'count' => $count, //日订单
                'month' => intval($month / 100) //月冲
            ]
        ];
        echo json_encode($data);
    }

    /**
     * 新加判断 用户是否购买会员
     *
     * @param $uuid
     * @return mixed
     */
    static function hasChargeVip($uuid)
    {
        return cached('order:charge:' . $uuid)->expired(3600)
            ->serializerPHP()
            ->setSaveEmpty(true)
            ->fetch(function () use ($uuid) {
                return OrdersModel::where([
                    'uuid' => $uuid,
                    'order_type' => \ProductModel::GOODS_TYPE_VIP,
                    'status' => OrdersModel::PAY_STAT_SUCCESS
                ])->first();
            });
    }
}
