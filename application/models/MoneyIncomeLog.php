<?php

use Illuminate\Database\Eloquent\Model;


/**
 * class MoneyIncomeLogModel
 *
 * @property int $aff 用户aff
 * @property int $prev_coin
 * @property string $coinCnt
 * @property int $next_coin
 * @property string $created_at
 * @property int $data_id 数据id
 * @property string $data_name 数据名称
 * @property string $desc
 * @property string $snapshot  快照
 * @property int $id
 * @property int $source 日志来源
 * @property int $source_aff
 * @property int $type 1增 2减
 *
 * @property null|array $snapshot_data
 * @property MemberModel $source_member
 *
 * @mixin \Eloquent
 */
class MoneyIncomeLogModel extends BaseModel
{

    protected $table = "money_income_log";

    protected $primaryKey = 'id';

    protected $fillable
        = [
            'aff',
            'coinCnt',
            'prev_coin',
            'next_coin',
            'created_at',
            'data_id',
            'data_name',
            'snapshot',
            'desc',
            'source',
            'source_aff',
            'type',
        ];

    protected $guarded = 'id';

    const UPDATED_AT = null;

    const TYPE_ADD = 1;
    const TYPE_SUB = 2;
    const TYPE
        = [
            self::TYPE_ADD => '增加',
            self::TYPE_SUB => '减少',
        ];

    const SOURCE_TOPPED = 1;     //充值
    const SOURCE_PROXY = 2;      //邀请
    const SOURCE_SIGN_UP = 3;    //签到
    const SOURCE_SUB_WITHDRAW = 18;  // 提现扣除
    const SOURCE_ADD_WITHDRAW = 19;  // 提现退回
    const SOURCE_KEFU = 20;  // 后台上下分
    const SOURCE_GAOFEI = 21;  // 稿费
    const SOURCE_POSTCLUB = 24;  // 帖子订阅收益
    const SOURCE_POST_REWARD = 25;  // 帖子打赏

    const SOURCE
        = [
            self::SOURCE_TOPPED       => '充值',
            self::SOURCE_SIGN_UP      => '签到',
            self::SOURCE_PROXY        => '邀请',
            self::SOURCE_SUB_WITHDRAW => '提现扣除',
            self::SOURCE_ADD_WITHDRAW => '提现退回',
            self::SOURCE_KEFU         => '客服处理',
            self::SOURCE_GAOFEI       => '稿费',
            self::SOURCE_POSTCLUB     => '帖子订阅',
            self::SOURCE_POST_REWARD  => '帖子打赏',
        ];

    protected $appends = [
        'snapshot_data'
    ];


    public static function createAddLog(
        MemberModel $member,
        int $coin,
        int $source,
        string $descp,
        ?Model $dataModel,
        ?MemberModel $fromWho = null
    ) {
        return self::createLog($member, self::TYPE_ADD, $coin, $source, $descp, $dataModel,$fromWho);
    }

    public static function createSubLog(
        MemberModel $member,
        int $coin,
        int $source,
        string $descp,
        ?Model $dataModel,
        ?MemberModel $fromWho = null
    ) {
        return self::createLog($member, self::TYPE_SUB, $coin, $source,  $descp, $dataModel,$fromWho);
    }

    protected static function createLog(
        MemberModel $member,
        int $type,
        int $coin,
        int $source,
        string $desc,
        ?Model $data,
        ?MemberModel $fromWho = null
    ) {
        if (empty($fromWho)){
            $fromWho = $member;
        }
        if ($source == MoneyIncomeLogModel::SOURCE_GAOFEI){
            if ($type == self::TYPE_ADD) {
                $prev_coin = $member->income_royalties;
                $next_coin = $member->income_royalties + $coin;
            } else {
                $prev_coin = $member->income_royalties - $coin;
                $next_coin = $member->income_royalties;
            }
        }else{
            if ($type == self::TYPE_ADD) {
                $prev_coin = $member->income_money;
                $next_coin = $member->income_money + $coin;
            } else {
                $prev_coin = $member->income_money - $coin;
                $next_coin = $member->income_money;
            }
        }

        return self::create(
            [
                'aff'        => $member->aff,
                'source'     => $source,
                'source_aff' => $fromWho->aff,
                'prev_coin'  => $prev_coin,
                'coinCnt'    => $coin,
                'next_coin'  => $next_coin,
                'data_name'  => $data ? $data->getTable() : '',
                'data_id'    => $data ? $data->getKey() : '0',
                'snapshot'   => $data ? json_encode($data->getAttributes()) :'{}',
                'created_at' => time(),
                'type'       => $type,
                'desc'       => $desc,
            ]
        );
    }


    public function source_member( ): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(MemberModel::class, 'aff', 'source_aff');
    }

    public function getSnapshotDataAttribute()
    {
        if (array_key_exists('snapshot_data' ,$this->attributes)){
            return $this->attributes['snapshot_data'];
        }
        $data = json_decode($this->attributes['snapshot'] ?? '[]' , 1);
        if (self::SOURCE_KEFU == $this->source){
            $data = $data ?? [];
            return UserContentsModel::make()->setRawAttributes($data , true);
        }
        return $data;
    }

}
