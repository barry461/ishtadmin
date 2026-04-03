<?php

use Illuminate\Database\Eloquent\Model;

/**
 * class PostCreatorModel
 *
 *
 * @property int $id
 * @property int $aff aff
 * @property string $nickname
 * @property int $post_club_month 帖子订阅月卡价格
 * @property int $post_club_quarter 帖子订阅季卡价格
 * @property int $post_club_year 帖子订阅年卡价格
 * @property int $status 是否是博主 0不是 1是
 * @property int $ban_post 是否封禁 0不是 1是
 * @property string $created_at
 * @property string $updated_at
 * @property float $work_score
 *
 *
 *
 * @mixin \Eloquent
 */
class PostCreatorModel extends BaseModel
{
    protected $table = 'post_creator';
    protected $primaryKey = 'id';
    protected $fillable = [
        'aff',
        'nickname',
        'post_club_month',
        'post_club_quarter',
        'post_club_year',
        'status',
        'ban_post',
        'created_at',
        'updated_at',
        'work_score',
    ];
    protected $guarded = 'id';

    const STATUS_NO = 0;
    const STATUS_OK = 1;
    const STATUS_TIPS = [
        self::STATUS_NO => '否',
        self::STATUS_OK => '是'
    ];

    public function clubs()
    {
        return $this->hasOne(PostClubsModel::class, 'aff', 'aff');
    }

    public function member()
    {
        return $this->hasOne(MemberModel::class, 'aff', 'aff');
    }

    public static function findByAff($aff, $writePdo = false ,$lock = false): ?PostCreatorModel
    {
        $query = self::query();
        if ($writePdo) {
            $query->useWritePdo();
        }

        if ($lock){
            $query->lock();
        }

        /** @var ?self $model */
        $model = $query->where('aff', $aff)->first();
        return $model;
    }
}