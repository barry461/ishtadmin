<?php

use Illuminate\Database\Eloquent\Model;

/**
 * class MessageModel
 *
 * @property int $id
 * @property int $aff 用户aff
 * @property int $type 类型
 * @property int $from_aff 来源用户
 * @property int $is_real 1真实2虚假
 * @property string $created_at
 * @property string $updated_at
 * @property string $data_name
 * @property string $data_id
 *
 *
 * @property MemberModel $from_member
 *
 * @mixin \Eloquent
 */
class MessageModel extends BaseModel
{
    const TYPE_UNLOCK = 1;
    const TYPE_CONFIRM = 2;
    const TYPE_CG_COMMENT = 3;
    const INFO_FAKE = 2;
    const INFO_REAL = 1;
    const INFO_REAL_COIN = 1;
    const IS_NOT_READ = 1;
    const IS_READ = 2;

    const COLOR_RED = '0xFFFF4149';
    const COLOR_PURPLE = '0xFFCD79FF';
    const COLOR_GRAY = '0xFF787878';

    protected $table = "message";

    protected $primaryKey = 'id';

    protected $fillable
        = [
            'aff',
            'type',
            'from_aff',
            'is_real',
            'is_read',
            'data_name',
            'data_id',
            'created_at',
            'updated_at',
        ];

    public static function queryUnread(...$args
    ): \Illuminate\Database\Eloquent\Builder {
        $query = self::query()->where('is_read', self::IS_NOT_READ);
        if (count($args)) {
            return $query->where(...$args);
        }

        return $query;
    }

    public function from_member()
    {
        return $this->hasOne(MemberModel::class, 'aff', 'from_aff');
    }

    public static function addMessage( MemberModel $member,  ?Model $data  ) {
        return self::insert([
            'aff'        => $member->aff,
            'type'       => self::TYPE_CG_COMMENT,
            'from_aff'   => 0,
            'data_name'  => $data ? $data->getTable() : '',
            'data_id'    => $data ? $data->getKey() : '',
            'is_real'    => self::IS_NOT_READ,
            'is_read'    => self::INFO_REAL,
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
    }


}