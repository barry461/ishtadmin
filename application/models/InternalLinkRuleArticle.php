<?php

/**
 * 内链规则与文章关联表
 *
 * @property int $id
 * @property int $rule_id 内链规则ID
 * @property int $article_id 文章ID（contents.cid）
 * @property string $first_inserted_at 首次成功插入时间
 *
 * @mixin \Eloquent
 */
class InternalLinkRuleArticleModel extends BaseModel
{
    protected $table = 'internal_link_rule_article';

    protected $primaryKey = 'id';

    protected $fillable = [
        'rule_id',
        'article_id',
        'first_inserted_at',
    ];

    protected $guarded = 'id';

    public $timestamps = false;
}

