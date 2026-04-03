<?php

/**
 * class SkitsModel
 *
 *
 * @property string $id
 * @property string $title 标题
 * @property string $desc 描述
 * @property string $update_text 更新提示
 * @property string $fee_text 收费提示
 * @property int $is_open 特惠活动
 * @property string $coins 合集价格
 * @property string $buy_num 购买次数
 * @property string $buy_coins 购买总金币
 * @property string $created_at
 * @property string $updated_at
 *
 *
 *
 * @mixin \Eloquent
 */
class SkitsModel extends BaseModel
{
    protected $table = 'skits';
    protected $fillable = [
        'id',
        'title',
        'desc',
        'update_text',
        'fee_text',
        'is_open',
        'coins',
        'buy_num',
        'buy_coins',
        'created_at',
        'updated_at'
    ];
    protected $primaryKey = 'id';
    public $timestamps = true;

    const OPEN_NO = 0;
    const OPEN_OK = 1;
    const OPEN_TIPS = [
        self::OPEN_NO => '关闭',
        self::OPEN_OK => '开启',
    ];

    public $appends = [
        'is_pay'
    ];

    const CK_SKITS_DETAIL = 'ck:skits:detail:%d';
    const GP_SKITS_DETAIL = 'gp:skits:detail';
    const CN_SKITS_DETAIL = '短剧集合详情';

    public function getIsPayAttribute(): int
    {
        static $ary = null;
        if (APP_MODULE == 'staff') {
            return 1;
        }
        if (isset($this->attributes['is_pay'])) {
            return $this->attributes['is_pay'];
        }
        $aff = self::$watchUser ? self::$watchUser->aff : 0;
        if (empty($aff)) {
            return 0;
        }
        //判断是否有会员权限
        $hasPrivilege = UserPrivilegeModel::hasPrivilege(USER_PRIVILEGE,
            ProductPrivilegeModel::RESOURCE_TYPE_SKITS,
            ProductPrivilegeModel::PRIVILEGE_TYPE_VIEW);
        if ($hasPrivilege){
            return 1;
        }
        $rk = SkitsPayModel::generateSkitsRk($aff);
        if ($ary === null) {
            $ary = redis()->sMembers($rk);
        }
        if (empty($ary) || !is_array($ary) || !in_array($this->attributes['id'], $ary)) {
            return 0;
        }

        return 1;
    }

    public static function findById($sid){
        $key = sprintf(self::CK_SKITS_DETAIL, $sid);
        return cached($key)
            ->group(self::GP_SKITS_DETAIL)
            ->chinese(self::CN_SKITS_DETAIL)
            ->fetchPhp(function () use ($sid){
                return self::selectRaw('id, title, `desc`, update_text, fee_text, is_open, coins')->where('id', $sid)->first();
            });
    }

}