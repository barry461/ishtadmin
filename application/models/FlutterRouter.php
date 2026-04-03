<?php

/**
 * class FlutterRouterModel
 *
 * @property int $id
 * @property string $name 名称
 * @property string $router 路由
 * @property int $sort 排序
 * @property int $status 状态
 * @property string $created_at 创建时间
 * @property string $updated_at 修改时间
 *
 * @mixin \Eloquent
 */
class FlutterRouterModel extends BaseModel
{

    protected $table = "flutter_router";

    protected $primaryKey = 'id';

    protected $fillable
        = [
            'name',
            'router',
            'sort',
            'status',
            'created_at',
            'updated_at',
        ];

    protected $guarded = 'id';

    public $timestamps = true;

    const STATUS_YES = 1;
    const STATUS_NO = 0;
    const STATUS
        = [
            self::STATUS_YES => '是',
            self::STATUS_NO  => '否',
        ];


    public static function queryBase(...$args)
    {
        return parent::queryBase($args)->where('status', self::STATUS_YES);
    }

    public static function router_list(): array
    {
        return FlutterRouterModel::queryBase()
            ->orderByDesc('sort')
            ->orderBy('id')
            ->pluck('name', 'router')
            ->toArray();
    }


    public static function parseRouterUri($url , $router): string
    {
        $value = ltrim($url, '/:');
        $params = [];
        $str = preg_replace_callback("#(/\:[a-zA-Z\d]+)#", function ($v) use (&$params) {
            list(, $v) = $v;
            $params[] = substr($v, 2);
            return '';
        }, $router);
        $values = explode('/:', $value);
        $args = [];
        foreach ($params as $k => $param) {
            $args[$param] = $values[$k] ?? null;
        }
        if (empty($str)){
            return '';
        }
        $str .= '??' . http_build_query($args, '', '&');
        return $str;
    }


}
