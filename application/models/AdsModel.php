<?php

use Illuminate\Database\Eloquent\Model;

/**
 * Class AdsModel
 *
 * @mixin \Eloquent
 * @property int $id
 * @property string $title
 * @property string $description
 * @property string $img_url
 * @property string $url_config
 * @property int $position
 * @property string $android_down_url
 * @property string $ios_down_url
 * @property int $type
 * @property int $status
 * @property int $oauth_type
 * @property string $mv_m3u8
 * @property string $channel
 * @property string $created_at
 * @property string $router
 * @property string $start_at
 * @property string $end_at
 * @property int $sort
 * @property int $product_type
 * @property int $clicked
 * @property string $ads_code
 */
class AdsModel extends Model
{
    protected $table = 'sq_ads';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'title',
        'description',
        'img_url',
        'url_config',
        'position',
        'android_down_url',
        'ios_down_url',
        'type',
        'status',
        'oauth_type',
        'mv_m3u8',
        'channel',
        'created_at',
        'router',
        'start_at',
        'end_at',
        'sort',
        'product_type',
        'clicked',
        'ads_code',
    ];
}
