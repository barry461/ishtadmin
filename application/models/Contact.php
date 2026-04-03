<?php

/**
 * class ContactModel
 *
 *
 * @property int $id
 * @property string $title 标题
 * @property string $icon 图标
 * @property string $show_val 显示
 * @property string $url 联系方式
 * @property string $group 分组标识
 * @property int $status 状态1 生效
 * @property int $sort 排序
 *
 *
 *
 * @mixin \Eloquent
 */
class ContactModel extends BaseModel
{
    protected $table = 'contact';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'title',
        'icon',
        'show_val',
        'url',
        'group',
        'status',
        'sort'
    ];
    protected $guarded = 'id';
    public $timestamps = false;

    const STATUS_OK = 1;
    const STATUS_NO = 0;
    const STATUS_TIPS = [
        self::STATUS_NO => '禁用',
        self::STATUS_OK => '启用',
    ];

    const GROUP_ZM = 'zm';
    const GROUP_TIPS = [
        self::GROUP_ZM => '招募'
    ];

    public function getIconAttribute(): string
    {
        return url_image($this->attributes['icon'] ?? '');
    }

    public function setIconAttribute($value)
    {
        parent::resetSetPathAttribute('icon', $value);
    }

    public static function contactStr(){
        return cached('zm_contact')
            ->clearCached()
            ->fetchJson(function (){
                $str = '';
                self::query()->selectRaw('title, url')
                    ->where('status', ContactModel::STATUS_OK)
                    ->orderByDesc('sort')
                    ->get()->map(function (ContactModel $item) use (&$str){
                        $str .= $item->title . ":" . $item->url . "\r\n";
                    });

                return rtrim($str, "\r\n");
            });

    }
}