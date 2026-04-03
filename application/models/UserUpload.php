<?php

/**
 * class UserUploadModel
 *
 *
 * @property int $id
 * @property int $user_id 用户ID
 * @property string $name 名称
 * @property string $progress_rate 进度
 * @property int $upload_type 0 普通 1分段
 * @property int $upload_status 0未上传 1上传完成
 * @property int $slice_status 0未切片 1切片中 2切片完成
 * @property string $cover 封面
 * @property string $mp4_url Mp4
 * @property string $m3u8_url M3u8 地址
 * @property string $created_at
 * @property string $updated_at
 *
 *
 *
 * @mixin \Eloquent
 */
class UserUploadModel extends BaseModel
{
    protected $table = 'user_upload';
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'name',
        'progress_rate',
        'upload_type',
        'upload_status',
        'slice_status',
        'cover',
        'mp4_url',
        'm3u8_url',
        'created_at',
        'updated_at'
    ];
    protected $guarded = 'id';
    public $timestamps = true;

    protected $appends = ['video_url'];
    const UPLOAD_TYPE_COM = 0;
    const UPLOAD_TYPE_SEG = 1;
    const UPLOAD_TYPE_TIPS = [
        self::UPLOAD_TYPE_COM => '普通',
        self::UPLOAD_TYPE_SEG => '分段',
    ];

    const SLICE_WAIT = 0;
    const SLICE_PROCESS = 1;
    const SLICE_SUCCESS = 2;
    const SLICE_TIPS = [
        self::SLICE_WAIT => '未切片',
        self::SLICE_PROCESS => '切片中',
        self::SLICE_SUCCESS => '成功',
    ];

    const UPLOAD_STATUS_ON = 0;
    const UPLOAD_STATUS_OK = 1;
    const UPLOAD_STATUS_TIPS = [
        self::UPLOAD_STATUS_ON => '未上传',
        self::UPLOAD_STATUS_OK => '上传完成',
    ];

    public function getCoverAttribute()
    {
        if ($this->attributes['cover']){
            return url_image($this->attributes['cover']);
        }
        return '';
    }

    public function getVideoUrlAttribute()
    {
        if ($this->attributes['m3u8_url']){
            //return url_video_sns($this->attributes['m3u8_url'], 2);
            return TB_VIDEO_ADM_US . '/' . ltrim($this->attributes['m3u8_url'], '/');
        }
        if ($this->attributes['mp4_url']){
            return $this->attributes['mp4_url'];
        }
        return '';
    }

    public static function makeSlice(UserUploadModel $userUpload)
    {
        $data = [
            'uuid'    => 0,
            'm_id'    => $userUpload->id,
            'playUrl' => $userUpload->mp4_url,
            'needMp3' => 0,
            'needImg' => 1,
        ];
        $isOk = \tools\mp4Upload::accept($data, 'user_mv_callback');
        if ($isOk){
            $userUpload->slice_status = self::SLICE_PROCESS;
            $userUpload->save();
        }
        error_log('发起视频请求 URL:' . $userUpload->mp4_url . PHP_EOL, 3, APP_PATH . '/storage/logs/log.log');
    }
}