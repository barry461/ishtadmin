<?php

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property int $aff 用户标识
 * @property string $ground 用户底版
 * @property int $ground_w 用户底版宽度
 * @property int $ground_h 用户底版高度
 * @property string $thumb 用户上传头像
 * @property int $thumb_w 图片宽度
 * @property int $thumb_h 图片高度
 * @property string $face_thumb 处理完成图片
 * @property int $face_thumb_w 处理完成图片宽度
 * @property int $face_thumb_h 处理完成图片高度
 * @property int $is_delete 是否删除
 * @property int $status 状态
 * @property int $reason 处理异常描述
 * @property string $created_at
 * @property string $updated_at
 */
class MemberFaceModel extends BaseModel
{
    protected $table = 'member_face';

    protected $fillable = [
        'aff',
        'ground',
        'ground_w',
        'ground_h',
        'thumb',
        'thumb_w',
        'thumb_h',
        'face_thumb',
        'face_thumb_w',
        'face_thumb_h',
        'is_delete',
        'status',
        'reason',
        'created_at',
        'updated_at',
    ];

    const STATUS_WAIT = 0;
    const STATUS_DOING = 1;
    const STATUS_SUCCESS = 2;
    const STATUS_FAIL = 3;
    const STATUS_TIPS = [
        self::STATUS_WAIT    => '排队中',
        self::STATUS_DOING   => '处理中',
        self::STATUS_SUCCESS => '已成功',
        self::STATUS_FAIL    => '已失败',
    ];
    const DELETE_NO = 0;
    const DELETE_OK = 1;
    const DELETE_TIPS = [
        self::DELETE_NO => '未删除',
        self::DELETE_OK => '已删除',
    ];
    const SE_MY_FACE = ['id', 'thumb', 'thumb_w', 'thumb_h', 'face_thumb', 'face_thumb_w', 'face_thumb_h', 'status', 'reason', 'created_at'];

    protected static function booted()
    {
        parent::booted();
    }

    public function member(): HasOne
    {
        return $this->hasOne(MemberModel::class, 'aff', 'aff');
    }

    public function getThumbAttribute(): string
    {
        return url_image($this->attributes['thumb'] ?? '');
    }

    public function setGroundAttribute($value)
    {
        parent::resetSetPathAttribute('ground', $value);
    }

    public function getGroundAttribute(): string
    {
        return url_image($this->attributes['ground'] ?? '');
    }

    public function setThumbAttribute($value)
    {
        parent::resetSetPathAttribute('thumb', $value);
    }

    public function getFaceThumbAttribute(): string
    {
        return url_image($this->attributes['face_thumb'] ?? '');
    }

    public function setFaceThumbAttribute($value)
    {
        parent::resetSetPathAttribute('face_thumb', $value);
    }

    public static function create_record($aff, $material_id, $ground, $ground_w, $ground_h, $thumb, $thumb_w, $thumb_h)
    {
        $data = [
            'aff'          => $aff,
            'material_id'  => $material_id,
            'ground'       => $ground,
            'ground_w'     => $ground_w,
            'ground_h'     => $ground_h,
            'thumb'        => $thumb,
            'thumb_w'      => $thumb_w,
            'thumb_h'      => $thumb_h,
            'face_thumb'   => '',
            'face_thumb_w' => 0,
            'face_thumb_h' => 0,
            'is_delete'    => self::DELETE_NO,
            'status'       => self::STATUS_WAIT,
            'reason'       => '',
            'created_at'   => date('Y-m-d H:i:s'),
            'updated_at'   => date('Y-m-d H:i:s'),
        ];
        return self::create($data);
    }

    public static function create_customize_record($aff, $ground, $ground_w, $ground_h, $thumb, $thumb_w, $thumb_h)
    {
        $data = [
            'aff'          => $aff,
            'ground'       => $ground,
            'ground_w'     => $ground_w,
            'ground_h'     => $ground_h,
            'thumb'        => $thumb,
            'thumb_w'      => $thumb_w,
            'thumb_h'      => $thumb_h,
            'face_thumb'   => '',
            'face_thumb_w' => 0,
            'face_thumb_h' => 0,
            'is_delete'    => self::DELETE_NO,
            'status'       => self::STATUS_WAIT,
            'reason'       => '',
            'created_at'   => date('Y-m-d H:i:s'),
            'updated_at'   => date('Y-m-d H:i:s'),
        ];
        return self::create($data);
    }

    public static function list_my_face($aff, $status, $page, $limit): Collection
    {
        return self::select(self::SE_MY_FACE)
            ->where('aff', $aff)
            ->when(in_array($status,[0,1,2,3]),function ($query)use($status){
                if(in_array($status,[0,1])){
                   return $query->whereIn('status',[0,1]);
                }else{
                    return  $query->where('status', $status);
                }
            })
            ->where('is_delete', self::DELETE_NO)
            ->forPage($page, $limit)
            ->get();
    }
}