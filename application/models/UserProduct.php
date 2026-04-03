<?php

use Carbon\Carbon;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Events\Dispatcher;

/**
 * @property int $id
 * @property int $aff
 * @property int $product_id
 * @property int $vip_level
 * @property string $expired_time
 * @property int $type
 * @property int $status
 * @property string $created_at
 * @mixin \Eloquent
 */
class UserProductModel extends BaseModel
{
    protected $table = 'user_product';

    protected $fillable = [
        'id',
        'aff',
        'product_id',
        'vip_level',
        'expired_time',
        'status',
        'type',
        'created_at'
    ];

    protected $primaryKey = 'id';

    const UPDATED_AT = NULL;
    const STATUS_ACTIVITY = 1;
    const STATUS_INACTIVITY = 0;

    const STATUS = [
        self::STATUS_INACTIVITY => '禁用',
        self::STATUS_ACTIVITY => '启用'
    ];

    const TYPE_CARD = 1;
    const TYPE_ACT = 2;
    const TYPE = [
        self::TYPE_CARD => '卡',
        self::TYPE_ACT => '活动'
    ];

    protected static function booted()
    {
        parent::booted();
        static::saved(function ($userProduct) {
            redis()->del(UserPrivilegeModel::REDIS_KEY_USER_PRIVILEGE . $userProduct->aff);
        });
    }

    public static function queryBase(...$args)
    {
        return parent::queryBase(...$args)->where('status' , self::STATUS_ACTIVITY);
    }


    public function getExpiredTimeAttribute($value): string
    {
        return $this->getCreatedAtAttribute($value);
    }

    static function buy($memberOrAff, ProductModel $good): bool
    {
        $member = $memberOrAff instanceof MemberModel ? $memberOrAff : MemberModel::findByAff($memberOrAff);
        $aff = $member->aff;
        /** @var UserProductModel $userProduct */
        $userProduct = self::where('aff', $aff)->where('product_id', $good->id)->first();
        if (!$userProduct) {
            $carbon = Carbon::now();
            $userProduct = new self();
            $userProduct->aff = $aff;
            $userProduct->product_id = $good->id;
            $userProduct->type = self::TYPE_CARD;
        } else {
            $carbon = Carbon::parse($userProduct->expired_time);
        }
        $userProduct->expired_time = $carbon->max(Carbon::now())->addDays($good->valid_date)->toDateTimeString();
        $userProduct->status = self::STATUS_ACTIVITY;
        $userProduct->vip_level = max($userProduct->vip_level ?? 0, $good->vip_level);
        $isOk = $userProduct->save();
        if (empty($isOk)) {
            return false;
        }
        if ($member->vip_level < $userProduct->vip_level) {
            $member->vip_level = $userProduct->vip_level;
        }
        if (Carbon::parse($member->expired_at)->lt($userProduct->expired_time)) {
            $member->expired_at = $userProduct->expired_time;
        }
        if ($member->isDirty()) {
            $isOk = $member->save();
            if (empty($isOk)) {
                return false;
            }
        }

        /** @var ProductPrivilegeModel[] $productPrivileges */
        $productPrivileges = ProductPrivilegeModel::where('product_id', $good->id)->get();
        $userPrivileges = UserPrivilegeModel::where('aff', $aff)->where('product_id', $good->id)->get();
        foreach ($productPrivileges as $productPrivilege) {
            /** @var UserPrivilegeModel $userPrivilege */
            $userPrivilege = $userPrivileges->firstWhere('product_id', $productPrivilege->product_id);
            if ($userPrivilege) {
                $carbon = Carbon::parse($userPrivilege->expired_time);
                if ($carbon < Carbon::now()) {
                    $userPrivilege->value = $productPrivilege->value;
                } elseif ($productPrivilege->privilege_type == ProductPrivilegeModel::PRIVILEGE_TYPE_DISCOUNT) {
                    $userPrivilege->value = min($productPrivilege->value , $userPrivilege->value); //折扣保留最大的折扣
                } else {
                    $userPrivilege->value += $productPrivilege->value;
                }
                $userPrivilege->value = $productPrivilege->value;
            } else {
                $userPrivilege = new UserPrivilegeModel();
                $userPrivilege->aff = $aff;
                $userPrivilege->product_id = $good->id;
                $userPrivilege->privilege_id = 0;
                $userPrivilege->value = $productPrivilege->value;
                $carbon = Carbon::now();
            }
            $userPrivilege->resource_type = $productPrivilege->resource_type;
            $userPrivilege->privilege_type = $productPrivilege->privilege_type;
            $userPrivilege->status = UserPrivilegeModel::STATUS_ACTIVITY;
            $userPrivilege->expired_time = $carbon->max(Carbon::now())->addDays($good->valid_date)->toDateTimeString();

            $isOk = $userPrivilege->save();
            if (empty($isOk)) {
                return false;
            }
        }
        return true;
    }
}
