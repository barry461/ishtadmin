<?php

use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property int $aff 用户AFF
 * @property int $p_id 用户上传素材ID
 * @property int $cate_id 素材分类id
 * @property int $title 素材标题
 * @property string $thumb 素材图片
 * @property int $thumb_w 素材图片宽度
 * @property int $thumb_h 素材图片高度
 * @property int $sort 排序
 * @property int $is_hot 是否热门
 * @property int $used_ct 使用数
 * @property int $used_fct 假使用数
 * @property int $used_week_ct 周使用数
 * @property int $status 状态
 * @property string $up_at 上架时间
 * @property string $created_at
 * @property string $updated_at
 * @property int $coins
 *
 */
class FaceMaterialModel extends BaseModel
{
    protected $table = 'face_material';

    protected $fillable = [
        'aff',
        'p_id',
        'title',
        'cate_id',
        'thumb',
        'thumb_w',
        'thumb_h',
        'sort',
        'is_hot',
        'used_ct',
        'used_fct',
        'used_week_ct',
        'status',
        'up_at',
        'created_at',
        'updated_at',
        'coins'
    ];

    const STATUS_NO = 0;
    const STATUS_ON = 1;
    const STATUS_TIPS = [
        self::STATUS_NO => '下架',
        self::STATUS_ON => '上架',
    ];

    const HOT_NO = 0;
    const HOT_ON = 1;
    const HOT_TIPS = [
        self::HOT_NO => '非热门',
        self::HOT_ON => '热门',
    ];

    const SE_FACE_MATERIAL_LIST = ['id', 'aff', 'title', 'thumb', 'thumb_w', 'thumb_h', 'used_ct', 'used_fct', 'is_hot','coins'];
    const CK_FACE_MATERIAL_LIST = 'ck:face:material:list:%s:%s';
    const GP_FACE_MATERIAL_LIST = 'gp:face:material:list';
    const CN_FACE_MATERIAL_LIST = '换脸-素材列表';

    const SE_FACE_MATERIAL_DETAIL = ['id', 'aff', 'title', 'thumb', 'thumb_w', 'thumb_h', 'used_ct', 'used_fct', 'is_hot','coins'];
    const CK_FACE_MATERIAL_DETAIL = 'ck:face:material:detail:%s';
    const GP_FACE_MATERIAL_DETAIL = 'gp:face:material:detail';
    const CN_FACE_MATERIAL_DETAIL = '换脸-素材详情';

    public $appends = ['type'];

    protected static function booted()
    {
        parent::booted();
    }

//    public function cate(): HasOne
//    {
//        return $this->hasOne(FaceCateModel::class, 'id', 'cate_id');
//    }

    public function member(): HasOne
    {
        return $this->hasOne(MemberModel::class, 'aff', 'aff');
    }

    public function getThumbAttribute(): string
    {
        return url_image($this->attributes['thumb'] ?? '');
    }

    public function getCoinsAttribute(): int
    {
        if($this->attributes['coins'] == 0){
           return (int)setting('pay_face', 19);
        }
        return  $this->attributes['coins'];
    }

    public function getTypeAttribute(): int
    {
        //兼容
        return 2;
    }

    public function setThumbAttribute($value)
    {
        parent::resetSetPathAttribute('thumb', $value);
    }

    public function getUsedFctAttribute(): string
    {
        $used_ct = (int)($this->attributes['used_ct'] ?? 0);
        $used_fct = (int)($this->attributes['used_fct'] ?? 0);
        return APP_MODULE == 'api' ? $used_ct + $used_fct : $used_fct;
    }

    public static function list_material($page, $limit)
    {
        $cache_key = sprintf(self::CK_FACE_MATERIAL_LIST, $page, $limit);
        return cached($cache_key)
            ->group(self::GP_FACE_MATERIAL_LIST)
            ->chinese(self::CN_FACE_MATERIAL_LIST)
            ->fetchPhp(function () use ($page, $limit) {
                return self::select(self::SE_FACE_MATERIAL_LIST)
                    ->where('status', self::STATUS_ON)
                    ->orderByDesc('used_week_ct')
                    ->orderByDesc('id')
                    ->forPage($page, $limit)
                    ->get();
            });
    }

    public static function get_detail($id)
    {
        $cache_key = sprintf(self::CK_FACE_MATERIAL_DETAIL, $id);
        return cached($cache_key)
            ->group(self::GP_FACE_MATERIAL_DETAIL)
            ->chinese(self::CN_FACE_MATERIAL_DETAIL)
            ->fetchPhp(function () use ($id) {
                return self::select(self::SE_FACE_MATERIAL_DETAIL)
                    ->where('id', $id)
                    ->where('status', self::STATUS_ON)
                    ->first();
            });
    }
}