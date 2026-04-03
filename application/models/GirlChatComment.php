<?php

/**
 * @property string $id
 * @property string $aff
 * @property string $girl_chat_id
 * @property string $face
 * @property string $service
 * @property string $comment
 * @property int $status
 * @property string $created_at
 * @property string $updated_at
 *
 * @mixin Eloquent
 */
class GirlChatCommentModel extends BaseModel
{
    protected $table = 'girl_chat_comment';

    protected $fillable = [
        'id',
        'aff',
        'girl_chat_id',
        'face',
        'service',
        'comment',
        'status',
        'reject_reason',
        'created_at',
        'updated_at'
    ];

    protected $primaryKey = 'id';

    const STATUS_WAIT = 0;
    const STATUS_PASS = 1;
    const STATUS_FAILURE = 2;
    const STATUS = [
        self::STATUS_WAIT    => '待审核',
        self::STATUS_PASS    => '已通过',
        self::STATUS_FAILURE => '已拒绝'
    ];

    const REDIS_KEY_COMMENT_LIST = "girl:chat:comment:list";
    const REDIS_KEY_COMMENT_GROUP = "girl:chat:comment:group";

    public function user()
    {
        return $this->belongsTo(MemberModel::class, "aff", "aff");
    }

    public static function defaultQuery()
    {
        return self::query()
            ->where(["status" => self::STATUS_PASS])
            ->with("user:uid,aff,nickname,thumb,aff,expired_at,vip_level,uuid");
    }


    public static function clearCommentList($girl_chat_id)
    {
        cached("")->clearGroup(self::REDIS_KEY_COMMENT_GROUP . $girl_chat_id);
    }

    public static function getCommentByGirlChatId($girl_chat_id, $page = 1, $limit = 24)
    {
      ;
        return cached(self::REDIS_KEY_COMMENT_LIST . ":" . $girl_chat_id . ":" . $page . ":" . $limit)
            ->group(self::REDIS_KEY_COMMENT_GROUP . $girl_chat_id)
            ->expired(3600)
            ->serializerPHP()
            ->fetch(function () use ($girl_chat_id, $page, $limit) {
                return self::defaultQuery()
                    ->where("girl_chat_id", $girl_chat_id)
                    ->orderBy("id")
                    ->forPage($page, $limit)
                    ->get();
            });
    }

    public function girlChat()
    {
        return $this->belongsTo(GirlChatModel::class, "girl_chat_id", "id");
    }
}
