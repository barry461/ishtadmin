<?php

/**
 * class SystemNoticeModel
 *
 * @property int $id
 * @property int $aff
 * @property int $type
 * @property string $title
 * @property string $content
 * @property int $read 1未读2已读
 * @property string $created_at
 * @property int $updated_at
 * @mixin \Eloquent
 */
class SystemNoticeModel extends BaseModel
{
    protected $table = "system_notice";

    protected $primaryKey = 'id';

    protected $fillable = ['aff', 'type', 'content', 'read', 'created_at', 'updated_at', 'title'];

    const IS_READ = 2;
    const IS_UN_READ = 1;
    const READ_STATUS = [
        self::IS_READ => '已读',
        self::IS_UN_READ => '未读',
    ];

    const AUDIT_POST_PASS_MSG = '您发布的帖子《%s》已通过审核';
    const AUDIT_POST_UNPASS_MSG = '您发布的帖子《%s》未通过审核,原因:%s';
    const AUDIT_COMMENT_PASS_MSG = '您发布的评论【%s】已通过审核';
    const AUDIT_COMMENT_UNPASS_MSG = '您发布的评论【%s】未通过审核,原因:%s';
    const COMMENT_POST_MSG = '用户【%s】评论了你的帖子 《%s》: %s';
    const COMMENT_COMMENT_MSG = '用户【%s】评论了你的评论 《%s》: %s';
    const POST_REWARD_MSG = '用户【%s】打赏了你的帖子 《%s》: 钻石:%s';
    const FOLLOW_MSG = '用户【%s】关注了你';
    const UNFOLLOW_MSG = '用户【%s】已取消关注你';
    const AUDIT_MEMBER_PASS_MSG = '您修改了自己的基础信息,已通过审核';
    const AUDIT_MEMBER_UNPASS_MSG = '您修改了自己的基础信息,未通过审核,原因:%s';

    public static function createBy($aff, $content, $title)
    {
        $model = self::make();
        $model->timestamps = false;
        $model->fill([
            'aff' => $aff,
            'content' => $content,
            'title' => $title,
            'read' => self::IS_UN_READ,
            'type' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $model->saveOrFail();
        return $model;
    }

    public static function queryUnread(...$args)
    {
        $query = self::query()->where('read', self::IS_UN_READ);
        if (count($args)) {
            return $query->where(...$args);
        }
        return $query;
    }

    public function getQuestionAttribute()
    {
        return $this->attributes['content'] ?? '';
    }

    public static function addNotice($aff, $content, $title)
    {
        $data = [
            'aff' => $aff,
            'content' => $content,
            'title' => $title,
            'read' => self::IS_UN_READ,
            'type' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        return self::create($data);
    }
}