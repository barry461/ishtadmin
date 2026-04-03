<?php


/**
 * class InfoVipResourcesModel
 *
 * @property int $created_at 创建时间
 * @property int $id
 * @property int $info_id 资源id
 * @property int $sort 排序
 * @property int $type 类型
 * @property int $status 审核状态
 * @property string $cover 封面
 * @property string $url 地址
 * @property-read  string $url_str 地址
 *
 * @property-read InfoVipModel $info
 *
 * @mixin \Eloquent
 */
class InfoVipResourcesModel extends BaseModel
{

    protected $table = "info_vip_resources";

    protected $primaryKey = 'id';

    protected $fillable = ['created_at', 'info_id', 'sort', 'type', 'status', 'url', 'cover'];

    const TYPE_IMAGE = 1;
    const TYPE_VIDEO = 2;

    const TYPE_ARR = [
        self::TYPE_IMAGE => '图片',
        self::TYPE_VIDEO => '视频',
    ];


    const STATUS_WAITING = 1;
    const STATUS_ACCEPT = 2;
    const STATUS_ACCEPTED  = 3;
    const STATUS_ARR = [
        self::STATUS_WAITING => '等待中',
        self::STATUS_ACCEPT => '切片中',
        self::STATUS_ACCEPTED => '切片完成',
    ];

    const UPDATED_AT = null;

    protected $appends = ['url_str','cover_str'];


    public function info()
    {
        return $this->hasOne(InfoVipModel::class, 'id', 'info_id');
    }


    public function getUrlStrAttribute()
    {
        if(APP_MODULE != 'staff'){
            return;
        }
        $type = $this->attributes['type'] ?? 0;
        $url = $this->attributes['url'] ?? '';
        if ($type == self::TYPE_IMAGE) {
            return url_image($this->attributes['url']);

        } elseif ($type == self::TYPE_VIDEO) {
            $extension = preg_replace("#\?.*#", "", pathinfo($url, PATHINFO_EXTENSION));
            if($extension == 'mp4'){
                return config('mp4.visit').$url;
            }else{
                return config('video_backend_url').$url;
            }

        }
        return '';
    }


    public function getCoverStrAttribute()
    {
        if(APP_MODULE != 'staff'){
            return;
        }
        $url = $this->attributes['cover'] ?? '';
        return url_image($url);
        ;
    }
    public function getUrlAttribute(){
        if(APP_MODULE == 'staff'){
            return $this->attributes['url'];
        }
        switch($this->attributes['type']){
            case InfoVipResourcesModel::TYPE_IMAGE:
                return url_image($this->attributes['url']);
            break;
            case InfoVipResourcesModel::TYPE_VIDEO:
                return url_video($this->attributes['url']);
            break;
        }
    }

    public static function makeSlice(self $userUpload)
    {
        $mp4url  = $userUpload->url;
        if (strpos($userUpload->url,config('r2.mp4_url') )==false){
            $mp4url = url_video($userUpload->url);
        }else{
            $mp4url = str_replace("https://play.xmyy8.co/","",$mp4url);
        }

        $data = [
            'uuid'    => 0,
            'm_id'    => $userUpload->id,
            'playUrl' => $mp4url,
            'needMp3' => 0,
            'needImg' => 1,
        ];
        $isOk = \tools\mp4Upload::accept($data, 'userBaoyangCallback');
        if ($isOk){
            $userUpload->status = self::STATUS_ACCEPT;
            $userUpload->save();
        }
        error_log('发起视频请求 URL:' . $data['playUrl'] . PHP_EOL, 3, APP_PATH . '/storage/logs/log.log');
    }


}