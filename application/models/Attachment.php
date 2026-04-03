<?php

/**
 * class AttachmentdModel
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
class AttachmentModel extends BaseModel
{
    protected $table = 'attachments';
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
        'updated_at',
        'cid'
    ];
    protected $guarded = 'id';
    public $timestamps = false;

    // 禁用日期转换,避免Carbon依赖
    protected $casts = [];
    protected $dates = [];

    // 覆盖BaseModel的日期处理方法,避免Carbon依赖
    public function getCreatedAtAttribute($value): string
    {
        if (empty($value) || $value === '0000-00-00 00:00:00') {
            return '';
        }
        $timestamp = is_numeric($value) ? $value : strtotime($value);
        return date('Y-m-d H:i:s', $timestamp);
    }

    public function getUpdatedAtAttribute($value): string
    {
        return $this->getCreatedAtAttribute($value);
    }

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
        if ($this->attributes['cover']) {
            return url_image($this->attributes['cover']);
        }
        return '';
    }

    public function getUrlAttribute()
    {
        return $this->attributes['mp4_url'] ?? '';
    }


    public function getVideoUrlAttribute()
    {
        if ($this->attributes['m3u8_url']) {
            return TB_VIDEO_ADM_US . '/' . ltrim($this->attributes['m3u8_url'], '/');
        }
        if ($this->attributes['mp4_url']) {
            return $this->attributes['mp4_url'];
        }
        return '';
    }

    public function getRawMp4UrlAttribute()
    {
        return $this->attributes['mp4_url'] ?? '';
    }

    public static function makeSlice(AttachmentModel $userUpload)
    {


        $data = [
            'uuid' => 0,
            'm_id' => $userUpload->id,
            'playUrl' => $userUpload->mp4_url,
            'needMp3' => 0,
            'needImg' => 1,
        ];


        $isOk = \tools\mp4Upload::accept($data, 'attachment_callback');
        error_log('发起视频请求 URL:' . print_r($isOk) . PHP_EOL, 3, APP_PATH . '/storage/logs/log.log');
        if ($isOk) {
            $userUpload->slice_status = self::SLICE_PROCESS;
            $userUpload->save();
        }
        error_log('发起视频请求 URL:' . $userUpload->mp4_url . PHP_EOL, 3, APP_PATH . '/storage/logs/log.log');
    }

    /**
     * 分页获取附件列表
     * @param array $params 查询参数
     * @param int $limit 每页数量
     * @param int $offset 偏移量
     * @return array [$list, $total]
     */
    public static function getPageList(array $params, int $limit, int $offset): array
    {
        $query = self::query()->orderByDesc('id');

        // 名称搜索
        if (!empty($params['keyword'])) {
            $query->where('name', 'like', '%' . $params['keyword'] . '%');
        }

        // 切片状态筛选
        if (isset($params['slice_status']) && $params['slice_status'] !== '') {
            $query->where('slice_status', $params['slice_status']);
        }

        // 上传状态筛选
        if (isset($params['upload_status']) && $params['upload_status'] !== '') {
            $query->where('upload_status', $params['upload_status']);
        }

        // 用户ID筛选
        if (!empty($params['user_id'])) {
            $query->where('user_id', $params['user_id']);
        }

        $total = $query->count();

        $list = $query->offset($offset)->limit($limit)->get();

        $userIds = $list->pluck('user_id')->filter()->unique()->values()->all();
        $contentIds = $list->pluck('cid')->filter()->unique()->values()->all();

        $userMap = [];
        if (!empty($userIds)) {
            $userMap = UsersModel::query()
                ->whereIn('uid', $userIds)
                ->get(['uid', 'screenName', 'name'])
                ->keyBy('uid');
        }

        $contentMap = [];
        if (!empty($contentIds)) {
            $contentMap = ContentsModel::query()
                ->whereIn('cid', $contentIds)
                ->get(['cid', 'title'])
                ->keyBy('cid');
        }

        $list = $list->map(function ($item) use ($userMap, $contentMap) {
            $item->slice_status_str = self::SLICE_TIPS[$item->getRawOriginal('slice_status')] ?? '未知';
            $item->upload_status_str = self::UPLOAD_STATUS_TIPS[$item->getRawOriginal('upload_status')] ?? '未知';
            $item->upload_type_str = self::UPLOAD_TYPE_TIPS[$item->getRawOriginal('upload_type')] ?? '未知';
            $user = $userMap[$item->user_id] ?? null;
            $content = $contentMap[$item->cid] ?? null;
            $item->uploader_name = $user ? ($user->screenName ?: $user->name) : '';
            $item->content_title = $content ? $content->title : '';
            return $item;
        });

        return [$list, $total];
    }
}