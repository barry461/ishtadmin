<?php

use Illuminate\Database\Eloquent\Model;

/**
 * class PostClubsModel
 *
 *
 * @property int $id
 * @property int $aff
 * @property int $month 月卡价格
 * @property int $quarter 季卡价格
 * @property int $year 年卡价格
 * @property int $post_num 帖子数量
 * @property int $member_num 成员数量
 * @property int $month_income 月卡收益
 * @property int $quarter_income 季卡收益
 * @property int $year_income 年卡收益
 * @property int $created_at 创建时间
 * @property string $notice 通知
 * @property int $status 状态
 *
 *
 * @property int $total_income
 * @property ?MemberModel $user
 * @property array<PostClubMembersModel>|\Illuminate\Database\Eloquent\Collection $members
 *
 * @mixin \Eloquent
 */
class PostClubsModel extends BaseModel
{
    protected $table = 'post_clubs';
    protected $primaryKey = 'id';
    protected $fillable
        = [
            'id',
            'aff',
            'month',
            'quarter',
            'year',
            'post_num',
            'member_num',
            'month_income',
            'quarter_income',
            'year_income',
            'created_at',
            'notice',
            'status',
        ];
    protected $guarded = 'id';
    protected $dateFormat = 'U';
    const STATUS_NO = 0;
    const STATUS_YES = 1;
    const STATUS = [self::STATUS_NO => '否', self::STATUS_YES => '是'];

    public static function findByAff($aff, $writePdo = false ,$lock = false): ?PostClubsModel
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

    public function user(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(MemberModel::class, 'aff', 'aff')->withDefault([
            'uid'      => 0,
            'aff'      => 0,
            'nickname' => '该账号已经注销',
        ]);
    }

    public function members(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PostClubMembersModel::class, 'club_id', 'id');
    }


    public function getTotalIncomeAttribute(): int
    {
        return $this->month_income + $this->year_income + $this->quarter_income;
    }

}