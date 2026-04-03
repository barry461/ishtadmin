<?php

/**
 * class ProjectModel
 *
 * @property int $id
 * @property string $title 项目名称
 * @property string $icon 图标
 * @property string $api api地址
 * @property int $status 0-禁用，1-启用
 * @property int $sort 排序
 * @property string $desc 描述
 * @property int $created_at 创建时间
 * @property int $updated_at 创建时间
 * @property string $type 标识符号
 * @property string $via APP类型
 * @mixin \Eloquent
 */
class ProjectModel extends BaseModel
{
    protected $table = "project";

    protected $primaryKey = 'id';

    protected $fillable = [
        'title',
        'icon',
        'api',
        'status',
        'sort',
        'desc',
        'created_at',
        'updated_at',
        'type',
        'via',
    ];

    const STATUS_OK = 1;
    const STATUS_NO = 0;
    const STATUS_TIPS = [
        self::STATUS_OK => '启用',
        self::STATUS_NO => '禁用'
    ];

    const VIA_SNS = 'sns';
    const VIA_APP = 'app';
    const VIA_TIPS = [
        self::VIA_SNS => 'SNS',
        self::VIA_APP => 'APP'
    ];

    public $timestamps = true;

    public function setIconAttribute($value)
    {
        $this->resetSetPathAttribute('icon', $value);
    }

    public function getIconAttribute(): string
    {
        return url_image($this->attributes['icon'] ?? '');
    }

    public static function listProjects()
    {
        return self::selectRaw('id,title,icon,api,type,via')
            ->where('status', self::STATUS_OK)
            ->orderByDesc('sort')
            ->orderByDesc('id')
            ->get()
            ->map(function (ProjectModel $item){
                $item->api = explode(',', $item->api);
                return $item;
            });
    }
}