<?php

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * class MoneyLogModel
 *
 * @property int $aff 用户aff
 * @property int $prev_coin 操作前的金额
 * @property int $coinCnt 获币数量
 * @property int $next_coin 操作后的金额
 * @property \Carbon\Carbon $created_at
 * @property string $desc 描述
 * @property int $id
 * @property int $source 日志来源
 * @property int $source_aff
 * @property int $type 1增 2减
 * @property string $data_name 数据名称
 * @property int $data_id 数据id
 *
 * @mixin \Eloquent
 */
class MoneyLogModel extends BaseModel
{
    protected $table = 'money_log';

    protected $fillable
        = [
            'aff',
            'source',
            'type',
            'prev_coin',
            'coinCnt',
            'next_coin',
            'source_aff',
            'created_at',
            'desc',
            'data_name',
            'data_id',
        ];

    protected $appends = ['source_str', 'type_str', 'coin'];
    protected $hidden = ['data_name', 'data_id'];

    const UPDATED_AT = null;

    const SOURCE_TOPPED = 1;     //充值
    const SOURCE_PROXY = 2;      //邀请
    const SOURCE_SIGN_UP = 3;    //签到
    const SOURCE_MANAGEMENT_INSPECTION = 4;  // 管理巡查
    const SOURCE_BUY_MV = 5;  // 购买视频
    const SOURCE_EXCHANGE = 6;  // 金币购买会员
    const SOURCE_BUY_GIRL = 7;  // 解锁约炮
    const SOURCE_BUY_CHAT = 8;  // 购买裸聊
    const SOURCE_SPECIAL_COIN_CARD = 9;  // 每天金币卡领取
    const SOURCE_BUY_VIP_GIFT = 10;  // vip赠送
    const SOURCE_BUY_PACKAGE = 11;  // 购买优惠包
    const SOURCE_UNLOCK_CHAT = 12;  // 解锁裸聊
    const SOURCE_UNLOCK_GIRL = 13;  // 解锁楼凤
    const SOURCE_BUY_BOOK = 14;  // 购买漫画
    const SOURCE_BUY_PIC = 15;  // 购买美图
    const SOURCE_BUY_STORY = 16;  // 购买小说
    const SOURCE_BUY_PUACOURSE = 17;  // 购买pua课程
    const SOURCE_SALE_GIRLCHAT = 18;  // 用户购买裸聊获得分成
    const SOURCE_CHATORDER_FUND_ADD = 19;  // 退单增加用户金币
    const SOURCE_CHATORDER_FUND_SUB = 20;  // 退单减去商家收益
    const SOURCE_REWARD_POST = 21;  // 帖子打赏
    const SOURCE_TASK = 22;  // 任务获取
    const SOURCE_BUY_CONTENT = 23; // 购买内容
    const SOURCE_POSTCLUB = 24; // 帖子订阅
    const SOURCE_SKITS = 25; // 解锁合集
    const SOURCE_EPISODE = 26; // 解锁单集
    const SOURCE_AI_TY = 27; // AI脱衣
    const SOURCE_AI_IMG_HL = 28; // AI图片换脸
    const SOURCE_LOTTERY = 29; // 抽奖
    const SOURCE_ACTIVITY_INVITE = 30; // 邀请活动，被邀请人
    const SOURCE_ACTIVITY_INVITE_BY = 31; // 邀请活动，邀请人
    const SOURCE_APPOINTMENT = 32; // 预约 预定

    const SHOW_NAME
        = [
            self::SOURCE_TOPPED                => '充值',
            self::SOURCE_SIGN_UP               => '签到',
            self::SOURCE_PROXY                 => '邀请',
            self::SOURCE_MANAGEMENT_INSPECTION => '管理巡查',
            self::SOURCE_BUY_MV                => '购买视频',
            self::SOURCE_EXCHANGE              => '金币购买会员',
            self::SOURCE_BUY_GIRL              => '解锁约炮',
            self::SOURCE_BUY_CHAT              => '购买裸聊',
            self::SOURCE_SPECIAL_COIN_CARD     => '每天金币卡领取',
            self::SOURCE_BUY_VIP_GIFT          => 'vip赠送',
            self::SOURCE_BUY_PACKAGE           => '购买优惠包',
            self::SOURCE_UNLOCK_CHAT           => '解锁裸聊',
            self::SOURCE_UNLOCK_GIRL           => '解锁楼凤',
            self::SOURCE_BUY_BOOK              => '购买漫画',
            self::SOURCE_BUY_PIC               => '购买美图',
            self::SOURCE_BUY_STORY             => '购买小说',
            self::SOURCE_BUY_PUACOURSE         => '购买把妹课程',
            self::SOURCE_REWARD_POST           => '帖子打赏',
            self::SOURCE_TASK                  => '任务获取',
            self::SOURCE_BUY_CONTENT           => '购买内容',
            self::SOURCE_POSTCLUB              => '帖子订阅',
            self::SOURCE_SKITS                 => '解锁合集',
            self::SOURCE_EPISODE               => '解锁单集',
            self::SOURCE_AI_TY                 => 'AI脱衣',
            self::SOURCE_AI_IMG_HL             => 'AI图片换脸',
            self::SOURCE_LOTTERY               => '抽奖',
            self::SOURCE_ACTIVITY_INVITE       => '邀请活动(被邀请人)',
            self::SOURCE_ACTIVITY_INVITE_BY    => '邀请活动(邀请人)',
            self::SOURCE_APPOINTMENT    => '预定',
        ];

    const TYPE_ADD = 1;
    const TYPE_SUB = 2;
    const TYPE
        = [
            self::TYPE_ADD => '增加',
            self::TYPE_SUB => '减少',
        ];

    const INVITATION_REWARD = 20;

    public static function createAddLog(
        MemberModel $member,
        int $coin,
        int $source,
        string $descp,
        ?Model $dataModel
    ) {
        return self::createLog($member, self::TYPE_ADD, $coin, $source, $descp, $dataModel);
    }

    public static function createSubLog(
        MemberModel $member,
        int $coin,
        int $source,
        string $descp,
        ?Model $dataModel
    ) {
        return self::createLog($member, self::TYPE_SUB, $coin, $source,  $descp, $dataModel);
    }

    protected static function createLog(
        MemberModel $member,
        int $type,
        int $coin,
        int $source,
        string $descp,
        ?Model $data
    ) {
        if ($type == self::TYPE_ADD) {
            $prev_coin = $member->money;
            $next_coin = $member->money + $coin;
        } else {
            $prev_coin = $member->money - $coin;
            $next_coin = $member->money;
        }

        return MoneyLogModel::create(
            [
                'aff'        => $member->aff,
                'source'     => $source,
                'type'       => $type,
                'prev_coin'  => $prev_coin,
                'coinCnt'    => $coin,
                'next_coin'  => $next_coin,
                'source_aff' => 0,
                'desc'       => $descp,
                'data_name'  => $data ? $data->getTable() : '',
                'data_id'    => $data ? $data->getKey() : '0',
            ]
        );
    }

    public function getSourceStrAttribute(): string
    {
        $str = self::SHOW_NAME[$this->attributes['source']] ?? '未知';
        //帖子订阅单独处理
        if ($this->attributes['source'] == self::SOURCE_POSTCLUB){
            $postClubMember = PostClubMembersModel::find($this->attributes['data_id']);
            if ($postClubMember){
                $member = MemberModel::findByAff($postClubMember->club_aff);
                $str = sprintf("[订阅博主 %s]",$member->nickname);
            }
        }

        return  $str;
    }

    public function getTypeStrAttribute(): string
    {
        return self::TYPE[$this->attributes['type']] ?? '未知';
    }

    public function getCoinAttribute(): int
    {
        return intval($this->attributes['coinCnt'] ?? 0);
    }

    static function formatDescription($tmp_name, $data = []): string
    {
        $tmpValue = [
            'buy'               => '购买金币:%s 个.',
            'buy_and_give'      => '购买金币:%s 个，赠送金币:%s 个',
            'unlock_info'       => '解锁资源花费%s 个',
            'change'            => '兑换码兑换金币个数:%s',
            'task_give'         => '完成任务赠送金币:%s',
            'withdraw'          => '申请金币提现个数:%s,兑换比例：%s,兑换金额:%s',
            'withdraw_recovery' => '提现申请未通过;返还个数：%s,兑换比例：%s,兑换金额:%s',
            'lottery_lucky'     => '抽奖活动抽中金币:%s 个',
            'invited'           => '【邀请活动】通过邀请链接进入APP送金币:%s 个',
            'invited_by'        => '【邀请活动】邀请用户进入APP送金币:%s 个',
        ];

        return isset($tmpValue[$tmp_name]) ? vsprintf($tmpValue[$tmp_name], $data) : '';
    }
}