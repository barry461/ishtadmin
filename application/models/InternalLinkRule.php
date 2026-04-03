<?php

/**
 * 站内内链规则
 *
 * @property int $id
 * @property string $keyword 关键词
 * @property string $target_url 指向链接（相对路径）
 * @property int $max_per_article 单篇最多插入次数
 * @property int $priority 优先级，数字越大越优先
 * @property int $inserted_article_count 已插入文章数
 * @property int $status 状态：1 启用，0 暂停
 * @property string $created_at
 * @property string $updated_at
 *
 * @mixin \Eloquent
 */
class InternalLinkRuleModel extends BaseModel
{
    protected $table = 'internal_link_rule';

    protected $primaryKey = 'id';

    protected $fillable = [
        'keyword',
        'target_url',
        'max_per_article',
        'priority',
        'inserted_article_count',
        'status',
        'created_at',
        'updated_at',
    ];

    protected $guarded = 'id';

    public $timestamps = true;

    const STATUS_DISABLED = 0;
    const STATUS_ENABLED = 1;

    const STATUS = [
        self::STATUS_ENABLED  => '启用',
        self::STATUS_DISABLED => '暂停',
    ];
}

