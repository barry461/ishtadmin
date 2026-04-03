<?php

/**
 * @property int $id
 * @property string $media_url 媒体地址
 * @property string $cover 视频封面
 * @property int $thumb_width 封面宽
 * @property int $thumb_height 封面高
 * @property int $pid 帖子ID
 * @property int $aff 用户AFF
 * @property int $type 类型 0 图片 1 视频
 * @property int $relate_type 关联类型 0 帖子 1 评论
 * @property int $status 0 未转换 1 转换 2 转换中
 * @property int $duration 视频持续时间
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * @property string $mp4 mp4
 * @mixin \Eloquent
 *
 * @property-read String $url 用来获取原始路径
 */
class PostMediaModel extends BaseModel
{
    protected $table = 'post_media';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'media_url',
        'cover',
        'thumb_width',
        'thumb_height',
        'pid',
        'aff',
        'type',
        'relate_type',
        'created_at',
        'status',
        'duration',
        'mp4',
    ];

    const STATUS_NO = 0;
    const STATUS_OK = 1;
    const STATUS_ING = 2;
    const STATUS_TIPS = [
        self::STATUS_NO => '未转换',
        self::STATUS_OK => '已转换',
        self::STATUS_ING => '转换中'
    ];

    const TYPE_IMG = 1;
    const TYPE_VIDEO = 2;
    const TYPE_TIPS = [
        self::TYPE_IMG => '图片',
        self::TYPE_VIDEO => '视频',
    ];

    const TYPE_RELATE_POST = 1;
    const TYPE_RELATE_COMMENT = 2;
    const TYPE_RELATE_TIPS = [
        self::TYPE_RELATE_POST => '帖子',
        self::TYPE_RELATE_COMMENT => '评论',
    ];

    protected $appends = ['url'];

    public static function getR2Mp4PlayUrl()
    {
        return config('r2.mp4_url');
    }

    public function setCoverAttribute($value)
    {
        parent::resetSetPathAttribute('cover', $value);
    }

    public function getCoverAttribute()
    {
        $uri = $this->attributes['cover'] ?? '';
        return $uri ? url_image($uri) : '';
    }

    public function setMediaUrlAttribute($value)
    {
        if (str_contains($value, config('r2.mp4_url'))){
            $this->attributes['media_url'] = $value;
        }else{
            parent::resetSetPathAttribute('media_url', $value);
        }
    }

    public function getUrlAttribute()
    {
        $url = $this->attributes['media_url'];
        if (str_contains($url, config('r2.mp4_url'))){
            return $url;
        }else{
            $url = ltrim($url,"/");
            return "/".$url;
        }
    }

    public function getMediaUrlAttribute()
    {
        $uri = $this->attributes['media_url'] ?? '';
        //$type = $this->attributes['type'] ?? '';
        $type = $this->getOriginal('type') ?? '';
        switch ($type) {
            case PostMediaModel::TYPE_IMG:
                return url_image($uri);
            case PostMediaModel::TYPE_VIDEO:
                $uri = trim($uri,'/');
                if (substr($uri, -4) == '.mp4' && APP_MODULE == 'staff') {
                    if (str_contains($uri, config('r2.mp4_url'))){
                        return $uri;
                    }
                    return config('mp4.visit') . $uri;
                }
                $uri = ltrim($uri, '/');
                return url_video_sns('/' . $uri,2);
        }
    }

    public static function makeAndSlice(array $medias)
    {
        foreach ($medias as $media) {
            $data = [
                'uuid'    => $media['aff'],
                'm_id'    => $media['id'],
                'playUrl' => $media['media_url'],
                'needMp3' => 0,
                'needImg' => 1,
            ];
            \tools\mp4Upload::accept($data, 'post_mv_callback');
            error_log('发起视频请求 URL:' . $media['media_url'] . PHP_EOL, 3, APP_PATH . '/storage/logs/post-slice.log');
        }
    }

    public static function createRecord($aff, $type, $relate_type, $pid, $url,$cover,$status)
    {
        $mp4 = $type == self::TYPE_VIDEO ? $url : '';
        return self::create([
            'aff' => $aff,
            'type' => $type,
            'relate_type' => $relate_type,
            'pid' => $pid,
            'media_url' => $url,
            'mp4' => $mp4,
            'thumb_width' => 0,
            'thumb_height' => 0,
            'cover' => $cover,
            'status' => $status,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public static function getMakeSliceList($pid,$relate_type): array
    {
        return PostMediaModel::query()
            ->where('pid', $pid)
            ->where('type', PostMediaModel::TYPE_VIDEO)
            ->where('status', PostMediaModel::STATUS_NO)
            ->where('relate_type', $relate_type)
            ->get()->toArray();
    }

    public static function updateSliceStatus($pid,$relate_type){
        return PostMediaModel::query()
            ->where('pid', $pid)
            ->where('type', PostMediaModel::TYPE_VIDEO)
            ->where('status', PostMediaModel::STATUS_NO)
            ->where('relate_type', $relate_type)
            ->update(['status' => PostMediaModel::STATUS_ING]);
    }

}
