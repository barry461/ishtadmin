<?php

/**
 * @property int $id
 * @property string $keyword 帖子ID
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * @mixin \Eloquent
 */
class PostCommentKeywordModel extends BaseModel
{
    protected $table = 'post_comment_keyword';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'keyword',
        'created_at',
        'updated_at',
    ];

    const POST_COMMENT_KEYWORD_LIST_KEY = 'post:comment:keyword';

    public static function listCommentKeywords()
    {
        return cached(self::POST_COMMENT_KEYWORD_LIST_KEY)
            ->expired(3600)
            ->serializerPHP()
            ->fetch(function () {
                $data = self::get()
                    ->toArray();
                return array_column($data, 'keyword');
            });
    }

    public static function clearCache()
    {
        redis()->del(self::POST_COMMENT_KEYWORD_LIST_KEY);
    }
}
