<?php

use service\AppFeedSystemService;

/**
 * class UserFeedModel
 *
 * @property \Carbon\Carbon $created_at
 * @property int $evaluation 用户评价
 * @property int $help_type 问题类型:详见admin/Usercontroller
 * @property int $id
 * @property string $image_1 图片1
 * @property int $is_read
 * @property int $is_replay 1客服已经回复
 * @property int $message_type 消息类型 1 文字 2图片
 * @property string $question 问题描述
 * @property int $status 1:用户内容;2,管理员已回复
 * @property int $updated_at
 * @property string $user_ip ip
 * @property string $uuid 用户唯一id
 * @property int $replay_old 用户被屏蔽前的原始回复状态
 * @property int $admin_id  审核管理员ID
 *
 * @property MemberModel $user
 * @property ManagerModel $manager
 *
 * @mixin \Eloquent
 */
class UserFeedModel extends BaseModel
{
    protected $table = "user_feed";

    protected $primaryKey = 'id';

    protected $fillable = [
        'evaluation',
        'help_type',
        'image_1',
        'is_read',
        'is_replay',
        'replay_old',
        'message_type',
        'question',
        'status',
        'created_at',
        'updated_at',
        'user_ip',
        'uuid',
        'admin_id'
    ];

    protected $guarded = 'id';

    const FEED_STATUS = [
        1 => '用户',
        2 => '管理员',
    ];

    const FEED_EVALUATION = [
        1 => '不满意',
        2 => '一般',
        3 => '满意'
    ];
    const IS_UN_READ = 0;
    const IS_READ = 1;
    const IS_SCREEN = 2;

    const REPLAY_STATUS = [
        self::IS_UN_READ => '未回复',
        self::IS_READ => '已回复',
        self::IS_SCREEN => '已屏蔽',
    ];

    public function user()
    {
        return $this->hasOne(MemberModel::class, 'uuid', 'uuid');
    }

    public function manager()
    {
        return $this->hasOne(ManagerModel::class, 'uid', 'admin_id');
    }

    public static function queryUnreply(...$args)
    {
        $query = self::query()
            ->where('is_read', UserFeedModel::IS_UN_READ)
            ->where('status', 2);
        if (count($args)) {
            return $query->where(...$args);
        }
        return $query;
    }

    public static function remoteQuest($uuid, $content){
        jobs(function () use($uuid, $content) {
            $member = MemberModel::findByUuid($uuid);
            // 工单系统处理
            (new AppFeedSystemService())->sendRemoteRequest(null, [
                    'app' => VIA,
                    'uuid' => $uuid,
                    'app_type' => $member->oauth_type,
                    'aff' => $member->aff,
                    'product' => 0,
                    'type' => 1,
                    'nickname' => $member->nickname,
                    'content' => $content ?: 'xxx图片',
                    'version' => $member->app_version,
                    'ip' => USER_IP,
                    'vip_level' => MemberModel::VIP_LEVEL[$member->vip_level] ?? '大众',
                    'status' => 1,
                ]
            );
        });
    }

}