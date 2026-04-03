<?php

/**
 * @property int $id
 * @property int $aff 被邀请
 * @property int $invite_by 邀请人
 * @property string $created_at
 *
 * @mixin \Eloquent
 */
class ActivityInviteLogModel extends BaseModel
{
    protected $table = 'activity_invite_log';
    protected $fillable = [
        'id',
        'aff',
        'invite_by',
        'created_at',
    ];
    protected $primaryKey = 'id';
    public $timestamps = false;

    //邀请赠送金币
    public static function invite($aff, $invite_by){
        //被邀请人
        $member = MemberModel::find($aff);
        //邀请人
        $inviteMember = MemberModel::find($invite_by);
        if (empty($member) || empty($inviteMember)){
            return false;
        }
        //活动邀请送金币
        $activity_start = setting('activity_start');
        $activity_end = setting('activity_end');
        $activity_test_member = setting('activity_test_member');
        $activity_test_member = explode(',', $activity_test_member);
        $now = time();
        if (($now < strtotime($activity_start) || $now > strtotime($activity_end)) && !in_array($invite_by, $activity_test_member)){
            return false;
        }
        //判断是否是渠道
        $channel = ChannelModel::findByAff($invite_by);
        if (!empty($channel)){
            return false;
        }
        //判断是否邀请过
        $invite_exists = self::where('invite_by', $invite_by)->where('aff', $aff)->exists();
        if ($invite_exists){
            return false;
        }
        //活动邀请最大人数
        $activity_invited_max_num = setting('activity_invited_max_num', 10);
        $invite_num = self::where('invite_by', $invite_by)->count('id');
        if ($invite_num >= $activity_invited_max_num){
            return false;
        }
        //邀请记录
        $model = self::make();
        $model->aff = $aff;
        $model->invite_by = $invite_by;
        $model->created_at = \Carbon\Carbon::now();
        $model->save();

        $activity_aff_send_coins = setting('activity_aff_send_coins', 3);
        $activity_by_aff_send_coins = setting('activity_by_aff_send_coins', 7);
        //被邀请人添加金币
        $description = MoneyLogModel::formatDescription('invited', ['coins' => $activity_aff_send_coins]);
        $member->addMoney($activity_aff_send_coins, MoneyLogModel::SOURCE_ACTIVITY_INVITE, $description, $model);

        //被邀请人添加金币
        $description = MoneyLogModel::formatDescription('invited_by', ['coins' => $activity_by_aff_send_coins]);
        $inviteMember->addMoney($activity_by_aff_send_coins, MoneyLogModel::SOURCE_ACTIVITY_INVITE_BY, $description, $model);

        return true;
    }

}
