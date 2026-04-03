<?php

use Carbon\Carbon;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Collection;
use ProductPrivilegeModel as Privilege;

/**
 * @property int $id
 * @property string $title
 * @property int $aff 用户aff
 * @property int $product_id 产品id
 * @property int $privilege_id 权限id
 * @property int $resource_type 资源类型
 * @property int $privilege_type 权限类型
 * @property int $value 数据，比如折扣价，比如生育次数
 * @property int $status 状态
 * @property string $expired_time 过期时间
 * @property string $created_at
 *
 * @mixin \Eloquent
 */
class UserPrivilegeModel extends BaseModel
{
    protected $table = 'user_privilege';

    protected $primaryKey = 'id';
    const UPDATED_AT = null;
    const STATUS_INACTIVITY = 0;
    const STATUS_ACTIVITY = 1;

    const STATUS = [
        self::STATUS_INACTIVITY => '非活动',
        self::STATUS_ACTIVITY => '活动'
    ];

    const REDIS_KEY_USER_PRIVILEGE = 'user:privilege:';
    const USER_DEFINE_PRIVILEGE = 0;


    protected $fillable = [
        'id',
        'title',
        'aff',
        'product_id',
        'privilege_id',
        'resource_type',
        'privilege_type',
        'value',
        'status',
        'expired_time',
        'created_at'
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
        return parent::queryBase(...$args);//->where('status', self::STATUS_INACTIVITY);
    }

    public function getExpiredTimeAttribute($value): string
    {
        return $this->getCreatedAtAttribute($value);
    }

    /**
     * @param MemberModel $member
     * @return array<UserProductModel>|Collection
     */
    static function getUserPrivilege(MemberModel $member)
    {
        $userPrivilege = self::initPrivilege();
        /** @var array<UserProductModel> $userProducts */
        $userProducts = UserProductModel::queryBase('aff', $member->aff)->get();
        if ($userProducts) {
            foreach ($userProducts as $userProduct) {
                if (Carbon::parse($userProduct->expired_time)->lt(Carbon::now())) {
                    $userProduct->status = UserProductModel::STATUS_INACTIVITY;
                    $userProduct->save();
                    UserPrivilegeModel::where([
                        'aff' => $member->aff,
                        'product_id' => $userProduct->product_id
                    ])->update(['status' => UserPrivilegeModel::STATUS_INACTIVITY]);
                    continue;
                }
                /** @var array<UserPrivilegeModel> $privileges */
                $privileges = UserPrivilegeModel::queryBase([
                    'aff' => $member->aff,
                    'product_id' => $userProduct->product_id
                ])->get();
                foreach ($privileges as $privilege) {
                    list($resource_type, $privilege_type) = [$privilege->resource_type, $privilege->privilege_type];
                    if ($userPrivilege[$resource_type][$privilege_type]['status'] == 0) {
                        $userPrivilege[$resource_type][$privilege_type]['status'] = 1;
                        $userPrivilege[$resource_type][$privilege_type]['value'] = $privilege->value;
                    } elseif ($privilege_type == Privilege::PRIVILEGE_TYPE_DISCOUNT) {
                        if ($userPrivilege[$resource_type][$privilege_type]['value'] > $privilege->value) {
                            $userPrivilege[$resource_type][$privilege_type]['value'] = $privilege->value;
                        }
                    } elseif ($userPrivilege[$resource_type][$privilege_type]['value'] < $privilege->value) {
                        $userPrivilege[$resource_type][$privilege_type]['value'] += $privilege->value;
                    }
                    $userPrivilege[$resource_type][$privilege_type]['_id'] = $privilege->id;
                }

            }
        }
        /** @var array<UserPrivilegeModel> $privileges */
        $privileges = UserPrivilegeModel::where('product_id', self::USER_DEFINE_PRIVILEGE)
            ->where('aff', $member->aff)
            ->where('status', UserPrivilegeModel::STATUS_ACTIVITY)
            ->get();
        if ($privileges) {
            foreach ($privileges as $privilege) {
                if (Carbon::parse($privilege->expired_time)->lt(Carbon::now())) {
                    $privilege->status = UserPrivilegeModel::STATUS_INACTIVITY;
                    $privilege->save();
                    continue;
                }
                list($resource_type, $privilege_type) = [$privilege->resource_type, $privilege->privilege_type];
                if ($userPrivilege[$resource_type][$privilege_type]['status'] == 0) {
                    $userPrivilege[$resource_type][$privilege_type]['status'] = 1;
                    $userPrivilege[$resource_type][$privilege_type]['value'] = $privilege->value;
                } elseif ($privilege_type == Privilege::PRIVILEGE_TYPE_DISCOUNT) {
                    if ($userPrivilege[$resource_type][$privilege_type]['value'] > $privilege->value) {
                        $userPrivilege[$resource_type][$privilege_type]['value'] = $privilege->value;
                    }
                } elseif ($userPrivilege[$resource_type][$privilege_type]['value'] < $privilege->value) {
                    $userPrivilege[$resource_type][$privilege_type]['value'] += $privilege->value;
                }
                $userPrivilege[$resource_type][$privilege_type]['_id'] = $privilege->id;
            }
        }
        return $userPrivilege;
    }

    static function initPrivilege(): array
    {
        $userPrivilege = [];
        foreach (Privilege::RESOURCE_TYPE_NUM as $resource) {
            foreach (Privilege::PRIVILEGE_TYPE_NUM as $privilege) {
                $userPrivilege[$resource][$privilege] = ['value' => 0, 'status' => 0, '_id' => 0];
            }
        }

        $userPrivilege[Privilege::RESOURCE_TYPE_SYSTEM][Privilege::PRIVILEGE_TYPE_FEED] = ['status' => 1, 'value' => 9999, '_id' => 0];

        return $userPrivilege;
    }

    /**
     * @param $userPrivilege
     * @param $resource
     * @param $privilege
     * @return false|int
     */
    static function hasPrivilege($userPrivilege, $resource, $privilege)
    {
        if ($userPrivilege[$resource][$privilege]['status'] == 1) {
            return $userPrivilege[$resource][$privilege]['value'];
        }
        return false;
    }

    static function hasPrivilegeAndSubTimePrivilege($userPrivilege, $resource, $privilege, $aff): bool
    {
        $value = self::hasPrivilege($userPrivilege, $resource, $privilege);
        if ($value === false || $value <= 0) {
            return false;
        }
        if ($value >= 9999) {
            return true;
        }
        $userPrivilege[$resource][$privilege]['value'] -= 1;
        /** @var UserPrivilegeModel $user */
        $user = self::query()
            ->where([
                'aff' => $aff,
                'resource_type' => $resource,
                'privilege_type' => $privilege,
                'status' => self::STATUS_ACTIVITY,
            ])
            ->where('value', '>', 0)
            ->first();
        if ($user) {
            $user->value = $user->value - 1;
            $user->save();
            return true;
        } else {
            return false;
        }
    }
}
