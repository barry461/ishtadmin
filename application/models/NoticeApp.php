<?php

/**
 * class AdsAppModel
 *
 * @property int $id
 * @property string $name 名称
 * @property string $intro 描述
 * @property string $thumb 图片地址
 * @property int $category_id 类型ID
 * @property int $type 产品类型 0:内部产品 1:外部产品
 * @property int $click_num 点击量
 * @property int $status 0-禁用，1-启用
 * @property int $sort 排序
 * @property int $created_at 创建时间
 * @property int $updated_at 创建时间
 * @mixin \Eloquent
 */
class NoticeAppModel extends BaseModel
{
    protected $table = "notice_app";

    protected $primaryKey = 'id';

    protected $fillable = [
        'name',
        'intro',
        'type',
        'click_num',
        'category_id',
        'thumb',
        'url',
        'status',
        'sort',
        'created_at',
        'updated_at'
    ];

    const STATUS_OK = 1;
    const STATUS_NO = 0;
    const STATUS_TIPS = [
        self::STATUS_OK => '启用',
        self::STATUS_NO => '禁用'
    ];

    const TYPE_1 = 0;
    const TYPE_2 = 1;
    const TYPE_TIPS = [
        self::TYPE_1 => '内部产品',
        self::TYPE_2 => '外部产品'
    ];

    const LIMIT_MAX_NUM = 16;

    protected $appends = [
        'clicked',
        'report_id',
        'report_type'
    ];

    const REDIS_KEY = 'home:notice:app';

    public function setThumbAttribute($value)
    {
        $this->resetSetPathAttribute('thumb', $value);
    }

    public function getThumbAttribute(): string
    {
        return url_image($this->attributes['thumb'] ?? '');
    }

    public function getClickedAttribute(): int
    {
        return rand(100000, 999999);
    }

    public static function listApps()
    {
        $data = cached(self::REDIS_KEY)
            ->fetchPhp(function () {
                return self::where('status', self::STATUS_OK)
                    ->orderByDesc('sort')
                    ->get();
            });
        $data = $data->toArray();
        $rand_no = [];
        $rand_yes = [];
        foreach ($data as $v){
            if ($v['sort'] > 0){
                $rand_no[] = $v;
            }else{
                $rand_yes[] = $v;
            }
        }
        $new_list = [];
        //排序视频数量大于需要取的数据
        if (count($rand_no) >= self::LIMIT_MAX_NUM){
            $new_list = array_slice($rand_no, 0 , self::LIMIT_MAX_NUM);
        }else{
            if (count($rand_yes) > 0){
                $new_list = $rand_no;
                $need_ct = self::LIMIT_MAX_NUM - count($rand_no);
                if (count($rand_yes)  < $need_ct){
                    $need_ct = count($rand_yes);
                }
                if ($need_ct > 1){
                    $arr_index = array_rand($rand_yes, $need_ct);
                    foreach ($arr_index as $k){
                        $new_list[] = $rand_yes[$k];
                    }
                }else{
                    $new_list = array_merge($new_list, $rand_yes);
                }
            }
        }
        return $new_list;
    }

    public static function clearCache()
    {
        cached(self::REDIS_KEY)->clearCached();
    }

    public function getReportIdAttribute(): int
    {
        return (int)($this->attributes['id'] ?? 0);
    }

    public function getReportTypeAttribute(): int
    {
        return DayClickModel::TYPE_NOTICE_APP;
    }

    public static function incrNum($id, $num = 1)
    {
        return self::where('id', $id)->increment('click_num', $num);
    }
}