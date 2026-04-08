<?php

use Illuminate\Database\Eloquent\Model;

/**
 * Class MvModel
 *
 * @mixin \Eloquent
 * @property int $id
 * @property string $_id
 * @property string $theme
 * @property string $title_zh
 * @property string $title_jp
 * @property string $title_en
 * @property string $description_zh
 * @property string $description_jp
 * @property string $description_en
 * @property string $m3u8
 * @property string $v_ext
 * @property int $duration
 * @property string $cover
 * @property string $cover_full
 * @property string $preview_video
 * @property int $watch_count
 * @property int $favorite_count
 * @property int $comment_html_count
 * @property int $comment_count
 * @property int $is_hot
 * @property int $is_show
 * @property int $is_recommend
 * @property int $is_latest
 * @property int $latest_sort
 * @property int $hot_sort
 * @property string $created_at
 * @property string $updated_at
 * @property string $publish_at
 * @property int $like_count
 * @property int $hot_today
 * @property int $hot_week
 * @property int $hot_month
 * @property int $hot_total
 * @property int $xp_id
 * @property int $search_hot
 * @property int $search_sort
 * @property string $time_node
 * @property string $used_at
 */
class MvModel extends BaseModel
{
    protected $table = 'sq_mv';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        '_id',
        'theme',
        'title_zh',
        'title_jp',
        'title_en',
        'description_zh',
        'description_jp',
        'description_en',
        'm3u8',
        'v_ext',
        'duration',
        'cover',
        'cover_full',
        'preview_video',
        'watch_count',
        'favorite_count',
        'comment_html_count',
        'comment_count',
        'is_hot',
        'is_show',
        'is_recommend',
        'is_latest',
        'latest_sort',
        'hot_sort',
        'created_at',
        'updated_at',
        'publish_at',
        'like_count',
        'hot_today',
        'hot_week',
        'hot_month',
        'hot_total',
        'xp_id',
        'search_hot',
        'search_sort',
        'time_node',
        'used_at',
    ];

    protected $appends = [
        'video_code',
        'duration_formatted',
        'cover_url',
    ];

    // 关联演员
    public function actors()
    {
        return $this->hasMany(MvActorConnModel::class, 'mv_id', 'id');
    }

    // 关联标签
    public function tags()
    {
        return $this->hasMany(MvTagConnModel::class, 'mv_id', 'id');
    }

    // 关联主题
    public function themes()
    {
        return $this->hasMany(MvStyleConnModel::class, 'mv_id', 'id');
    }

    // 获取视频番号
    public function getVideoCodeAttribute()
    {
        return $this->_id;
    }

    // 格式化时长
    public function getDurationFormattedAttribute()
    {
        if (empty($this->duration)) {
            return '00:00';
        }

        $seconds = $this->duration;
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        if ($hours > 0) {
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
        } else {
            return sprintf('%02d:%02d', $minutes, $secs);
        }
    }

    // 获取封面URL
    public function getCoverUrlAttribute()
    {
        return url_image($this->cover);
    }

    // 热门视频
    public static function getHotVideos($limit = 10, $offset = 0)
    {
        return self::where('is_show', 1)
            ->where('is_hot', 1)
            ->orderBy('hot_sort', 'desc')
            ->orderBy('hot_total', 'desc')
            ->skip($offset)
            ->take($limit)
            ->get();
    }

    // 最新视频
    public static function getLatestVideos($limit = 10, $offset = 0)
    {
        return self::where('is_show', 1)
            ->where('is_latest', 1)
            ->orderBy('latest_sort', 'desc')
            ->orderBy('created_at', 'desc')
            ->skip($offset)
            ->take($limit)
            ->get();
    }

    // 中文字幕视频
    public static function getChineseVideos($limit = 10, $offset = 0)
    {
        // 这里需要根据实际情况调整，可能需要关联标签表
        return self::where('is_show', 1)
            ->orderBy('created_at', 'desc')
            ->skip($offset)
            ->take($limit)
            ->get();
    }

    // 巨乳视频
    public static function getBustyVideos($limit = 10, $offset = 0)
    {
        // 这里需要根据实际情况调整，可能需要关联标签表
        return self::where('is_show', 1)
            ->orderBy('created_at', 'desc')
            ->skip($offset)
            ->take($limit)
            ->get();
    }

    // 女优相关视频
    public static function getActressVideos($actressId, $limit = 10, $offset = 0)
    {
        return self::where('is_show', 1)
            ->whereHas('actors', function ($query) use ($actressId) {
                $query->where('actor_id', $actressId);
            })
            ->orderBy('created_at', 'desc')
            ->skip($offset)
            ->take($limit)
            ->get();
    }
}
