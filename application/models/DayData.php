<?php

/**
 * @property int $id
 * @property string $date 日期
 * @property int $reg_total 新增
 * @property int $active_total 活跃
 * @property int $pay_total 总充值
 * @property int $pay_num 总充值成功订单数
 * @property int $vip_total vip充值
 * @property int $coins_total 金币充值
 * @property int $reg_pay_total 新增充值
 * @property int $reg_pay_scale 新增充值占比
 * @property int $pay_success_scale 支付成功率
 * @property int $coins_consume_total 金币消耗数
 * @property int $coins_consume_num 金币笔数
 * @property int $visit_website 官网访问数
 * @property int $down_and 安卓下载数
 * @property int $down_web PWA下载数
 * @property int $down_ios IOS下载数
 * @property int $down_window WINDOW下载数
 * @property int $down_macos MACOS下载数
 * @property int $down_total 总下载量
 * @property int $down_rate 官网下载率
 * @property string $created_at
 * @mixin \Eloquent
 */
class DayDataModel extends BaseModel
{
    protected $table = 'day_data';

    protected $fillable = [
        'id',
        'date',
        'reg_total',
        'active_total',
        'active_ios',
        'active_android',
        'active_web',
        'pay_total',
        'pay_num',
        'vip_total',
        'coins_total',
        'reg_pay_total',
        'reg_pay_scale',
        'pay_success_scale',
        'coins_consume_total',
        'coins_consume_num',
        'each_product_total',
        'visit_website',
        'down_total',
        'down_and',
        'down_web',
        'down_ios',
        'down_window',
        'down_macos',
        'down_rate',
        'created_at'
    ];

    protected $primaryKey = 'id';

    const UPDATED_AT = null;
}
