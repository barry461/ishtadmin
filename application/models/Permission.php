<?php

/**
 * 日活统计表
 * Class MemberLogModel
 * @property int $id
 * @property int $p_id
 * @property string $icon
 * @property string $module
 * @property string $controller 控制器
 * @property string $action    方法
 * @property string $name
 * @property string $args
 * @property int $sort 排序值，数值越大越靠前
 *
 * @mixin \Eloquent
 */
class PermissionModel extends BaseModel
{
    protected $table = 'permissions';

    public $timestamps = false;

    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'p_id',
        'module',
        'icon',
        'controller',
        'action',
        'args',
        'name',
        'sort',
    ];

    protected $appends = ['args_arr'];

    public static function getAll()
    {
        $list = self::orderBy('sort', 'desc')->get()->toArray();
        $list = arrayToTree($list,'id' , 'p_id');
        $data = [];
        foreach ($list as $k => $v) {
            array_push($data, $v);
            if (isset($list[$k]['children'])) {
                foreach ($list[$k]['children'] as $vv) {
                    array_push($data, $vv);
                }
            }
        }
        return $data;
    }

    public static function getTreeAll(array $idArray )
    {
        $where = [];
        $query = self::query();
        if (!empty($idArray)) {
            $query->whereIn('id', $idArray);
        }
        $list = $query->where($where)->orderBy('sort', 'desc')->get()->toArray();
        return arrayToTree($list, 'id', 'p_id');
    }


    public static function getTreeArray($pid = -1, $prefix = '')
    {
        $list = self::where('p_id', $pid)->orderBy('sort', 'desc')->get(['id', 'name', 'p_id']);
        $res = [];
        /** @var self $item */
        foreach ($list as $item) {
            $res[$item->id] = $prefix . $item->name;
            $nRes = self::getTreeArray($item->id, $prefix . "&nbsp;&nbsp;&nbsp;&nbsp;");
            if (!empty($nRes)) {
                $res = array_merge($res, $nRes);
            }
        }
        return $res;
    }

    public static function getTreeArrayData($pid = 0, $prefix = '')
    {
        $list = self::where('p_id', $pid)->orderBy('sort', 'desc')->get(['id', 'name', 'p_id']);
        $res[0] = '顶级(默认)';
        /** @var self $item */
        foreach ($list as $item) {
            $res[$item->id] = $prefix . $item->name;
        }

        return $res;
    }

    public function getArgsArrAttribute()
    {
        $args = $this->attributes['args'] ?? '';
        if (empty($args)){
            return [];
        }
        $url = 'https://12.cn/x?' . $args;
        $query = parse_url($url , PHP_URL_QUERY);
        parse_str($query , $args);
        return  $args;
    }

}