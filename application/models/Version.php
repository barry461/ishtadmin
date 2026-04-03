<?php

/**
 * class VersionModel
 *
 * @property int $id
 * @property string $version 版本号
 * @property int $apptype 框架类型 1 java 2 rn
 * @property string $type 型号
 * @property string $apk 下载连接
 * @property string $tips 更新说明
 * @property string $bundle_id ios企业安装包id
 * @property int $must 0 不强制更新 1强制
 * @property int $created_at 创建时间
 * @property string $via 来源 'agent' 企业签  'single' 个人签
 * @property int $status 1 启用  2 停用
 * @property string $message 系统维护公告
 * @property int $mstatus 系统公告状态 0 没有 1通知 2禁用
 * @property int $from_id 更新起点
 * @property int $to_id 更新终点
 * @property int $custom 域名跟随
 *
 * @date 2020-01-10 19:46:23
 *
 * @mixin \Eloquent
 */
class VersionModel extends BaseModel
{
    protected $table = "version";

    protected $primaryKey = 'id';

    protected $fillable
        = [
            'version',
            //'apptype',
            'type',
            'apk',
            'tips',
            //'bundle_id',
            'must',
            'created_at',
            //'via',
            'status',
            'message',
            'mstatus',
            'channel',
            //'from_id',
            //'to_id',
            'custom',
        ];

    protected $guarded = 'id';
    const SHARE_AFF_SET = 'share:aff';
    const CHAN_TF = 'testflight';// tf 包
    const CHAN_PG = 'normal';// 企業簽  包

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;


    const STATUS_SUCCESS = 1;
    const STATUS_FAIL = 2;
    const STATUS
        = [
            self::STATUS_SUCCESS => '启用',
            self::STATUS_FAIL    => '停用',
        ];

    const MUST_UPDATE = 1;
    const MUST_UPDATE_NOT = 2;
    const MUST
        = [
            self::MUST_UPDATE_NOT => '软更',
            self::MUST_UPDATE     => '强更',
        ];

    const REDIS_VERSION_KEY
        = [
            'ios'     => 'version:ios',
            'android' => 'version:android',
            'office'  => 'version:office',
            'web'     => 'version:web',
        ];

    const TYPE_ANDROID = 'android';
    const TYPE_IOS = 'ios';
    const TYPE_WEB = 'web';
    const TYPE_MAC = 'macos';
    const TYPE_WIN = 'windows';
    const TYPE
        = [
            self::TYPE_ANDROID => '安卓',
            self::TYPE_IOS     => '苹果',
            self::TYPE_WEB     => 'web',
            self::TYPE_MAC     => '苹果电脑',
            self::TYPE_WIN     => 'window系统',
        ];

    /**
     * 系统公告状态 0 没有 1通知 2禁用
     */
    const MSTATUS_NO = 0;
    const MSTATUS_NOTICE = 1;
    const MSTATUS
        = [
            self::MSTATUS_NO     => '没有',
            self::MSTATUS_NOTICE => '通知',
        ];

    const CUSTOM_NO = 0;
    const CUSTOM_OK = 1;
    const CUSTOM_TIPS = [
            self::CUSTOM_NO     => '否',
            self::CUSTOM_OK     => '是',
        ];

    const PUBLISH_APK_DOWN_CHANNEL = 'apk_down_channel';

    protected static function booted()
    {
        parent::booted();
        static::saved(function ($ads) {
            // 要处理包放到后台去下载地址
            if (!$ads->channel) {
                $is_update = 0;
                if ($ads->custom == self::CUSTOM_OK){
                    $is_update = 1;
                }
                // 官方包才处理
                jobs([self::class, 'defend_apk'], [$ads->apk, $is_update]);
                //订阅
                $data = json_encode([$ads->apk, $is_update]);
                redis()->publish(self::PUBLISH_APK_DOWN_CHANNEL, $data);
            }
        });
    }

    /**
     * 清除版本缓存
     *
     */
    static function clearRedis()
    {
        cached('')->clearGroup('version', 'last:version');
    }

    public function getApkAttribute()
    {
        $val = $this->attributes['apk'];
        $custom = $this->attributes['custom'];
        if (str_ends_with($val , '.apk') || str_ends_with($val , '.dmg') || str_ends_with($val , '.exe')){
            if ($custom == self::CUSTOM_OK) {
                return $val;
            }
            $val = parse_url($val , PHP_URL_PATH);
            $val = TB_APP_DOWN_URL . $val;
        }
        return $val;
    }


    public static function lastVersion($oauthType)
    {
        return cached('last:version:'.$oauthType)
            ->group('last:version')
            ->fetchPhp(function () use ($oauthType) {
                $where = [
                    'status' => VersionModel::STATUS_SUCCESS,
                    'type'   => $oauthType,
                ];
                $version = VersionModel::where($where)->orderBy('id', 'desc')
                    ->first();
                if (!empty($version)) {
                    $data = $version->toArray();
                } else {
                    $data = [
                        'version' => '0.0.0', 'type'=> $oauthType,
                        'apk' => '', 'tips' => '',  'must' => 0, 'status' => 1,
                        'message' => '', 'mstatus' => 0, 'channel' => '',
                    ];
                }
                if ($data['type'] == 'ios') {
                    $data['apk'] = 'https://aff.cggo.life/';
                }
                $data['versionMsg'] = $version;

                return $data;
            });
    }

    // 下载到其他的目录下面
    public static function defend_apk($apk, $is_update = 0)
    {
        try {
            $filename = ltrim(parse_url($apk, PHP_URL_PATH), '/');
            $dirname = rtrim(APP_PATH, '/') . '/../www_html/apk';
            $file_path = $dirname . '/' . $filename;
            wf("获取信息", [$dirname, $file_path], false, '/storage/logs/apk.log');
            if (file_exists($file_path) && $is_update == 0) {
                wf("跳过存在", $file_path, false, '/storage/logs/apk.log');
                return;
            }
            $dirname = dirname($file_path);
            if (!file_exists($dirname)) {
                wf("创建目录", $dirname, false, '/storage/logs/apk.log');
                $rs = mkdir($dirname, 0777, true);
                test_assert($rs, '无法创建目录:' . $dirname);
            }
            wf("获取文件", $apk, false, '/storage/logs/apk.log');
            $txt = file_get_contents($apk);
            test_assert($txt, '无法获取文件:' . $apk);
            wf("写入文件", $file_path, false, '/storage/logs/apk.log');
            $rs = file_put_contents($file_path, $txt);
            test_assert($rs, '无法写入文件:' . $file_path);
        } catch (Throwable $e) {
            wf("出现异常", $e->getMessage(), false, '/storage/logs/apk.log');
        }
    }
}
