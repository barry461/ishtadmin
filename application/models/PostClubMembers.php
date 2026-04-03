<?php


/**
 * class PostClubMembersModel
 *
 * @property int $id
 * @property int $aff
 * @property int $club_id
 * @property int $club_aff
 * @property string $type
 * @property int $expired_at
 * @property int $created_at
 * @property int $updated_at
 *
 * @property ?MemberModel $user
 * @property ?MemberModel $club
 * @property ?MemberModel $club_member
 *
 * @mixin \Eloquent
 */
class PostClubMembersModel extends BaseModel
{
    protected $table = 'post_club_members';
    protected $primaryKey = 'id';
    protected $fillable
        = [
            'id',
            'aff',
            'club_id',
            'club_aff',
            'type',
            'expired_at',
            'created_at',
            'updated_at',
        ];
    protected $guarded = 'id';
    protected $dateFormat = 'U';

    const TYPE_MONTH = 10;
    const TYPE_QUARTER = 20;
    const TYPE_YEAR = 30;
    const TYPE
        = [
            self::TYPE_MONTH   => '月卡',
            self::TYPE_QUARTER => '季卡',
            self::TYPE_YEAR    => '年卡',
        ];

    const TIME = [
            self::TYPE_MONTH   => 30,
            self::TYPE_QUARTER => 120,
            self::TYPE_YEAR    => 365,
        ];

    public static function findByAffClubId($aff,  $club_id): ?PostClubMembersModel
    {
        /** @var ?self $model */
        $model = self::query()->where('aff' , $aff)->where('club_id' , $club_id)->first();
        return $model;
    }

    public static function findByAffClubAff($aff,  $club_aff): ?PostClubMembersModel
    {
        /** @var ?self $model */
        $model = self::query()->where('aff' , $aff)->where('club_aff' , $club_aff)->first();
        return $model;
    }

    public static function generateRk(string $aff): string
    {
        return 'rk-club-aff:' . $aff;
    }


    public function user(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(MemberModel::class, 'aff', 'aff');
    }

    public function club(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(MemberModel::class, 'aff', 'club_id');
    }

    public function club_member(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(MemberModel::class, 'aff', 'club_aff');
    }

    /**
     * 获取订阅的所有用户
     */
    public static function listClubUserAffs($aff): array
    {
        $affs = self::query()->where('aff', $aff)
            ->where('expired_at','>=', TIMESTAMP)
            ->get()
            ->pluck('club_aff');

        return collect($affs)->filter()->unique()->toArray();
    }

}