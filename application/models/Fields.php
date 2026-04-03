<?php


use Illuminate\Database\Eloquent\Model;

/**
 * class FieldsModel
 *
 * @property int $id
 * @property int $cid
 * @property string $name
 * @property string $type
 * @property string $str_value
 * @property int $int_value
 * @property string $float_value
 *
 * @author xiongba
 * @date 2022-10-21 07:17:53
 *
 * @mixin \Eloquent
 */
class FieldsModel extends BaseModel
{

    protected $table = "fields";

    protected $primaryKey = 'id';

    protected $fillable
        = [
            'cid',
            'name',
            'type',
            'str_value',
            'int_value',
            'float_value',
        ];

    protected $guarded = 'id';

    public $timestamps = false;

    const TYPE_STR = 'str';
    const TYPE_INT = 'int';
    const TYPE_FLOAT = 'float';
    const TYPE = [
        self::TYPE_STR => 'str',
        self::TYPE_INT => 'int',
        self::TYPE_FLOAT => 'float',
    ];

    const CK_CONTENTS_SLUG = 'ck:contents:slug:%s';
    const GP_CONTENTS_SLUG = 'gp:contents:slug';
    const CN_CONTENTS_SLUG = '文章别名跳转';


    public function getStrValueAttribute()
    {
        $value = $this->attributes['str_value'] ?? '';
        $name = $this->attributes['name'] ?? '';
        if ($name == 'banner') {
            $value = trim($value);
            if (strlen($value) === 0) {
                return '';
            }
            if (strpos($value, "\n") !== false) {
                $ary = explode("\n", $value);
                $ary = array_map(function ($v) {
                    return trim($v);
                }, $ary);
                $ary = array_filter($ary);
                if (!empty($ary)) {
                    shuffle($ary);
                    $value = $ary[0];
                }
            }
            return url_image($value);
        } elseif ($name == 'redirect') {
            $value = str_replace([" ", "\r", "\n"], '', $value);
            preg_match("#\/[a-zA-Z0-9_]+\.html#", $value, $matches1);
            if ($matches1 && $matches1[0]) {
                preg_match("#[a-zA-Z0-9_]+#", $matches1[0], $matches2);
                if ($matches2) {
                    $slug = $matches2[0];
                    if ($slug) {
                        $redirect = self::getRedirectStr($slug);
                        if ($redirect) {
                            $value = $redirect;
                        }
                    }
                }
            }
            //抽奖替换
            $watchUser = self::$watchUser;
            if ($watchUser) {
                return str_replace('{token}', getID2Code($watchUser->uid), $value);
            }
            return $value;
        } else {
            return $value;
        }
    }

    public static function getAdsID()
    {
        $value = 1;
        $name = 'ads_field';
        return self::where('name', $name)->where('str_value', $value)->get()->map(function ($row) {
            return $row->cid;
        })->toArray();
    }

    public static function getNameByCid($cid)
    {
        return self::where('cid', $cid)->get()->map(function ($row) {
            return $row->name;
        })->toArray();
    }

    public static function getRedirectStr($slug)
    {
        $model = cached(sprintf(self::CK_CONTENTS_SLUG, $slug))
            ->chinese('大类列表')
            ->fetchPhp(function () use ($slug) {
                $cid = ContentsModel::where('slug', $slug)->value('cid');
                if ($cid) {
                    return self::where('cid', $cid)->where('name', 'redirect')->first();
                } else {
                    return null;
                }
            });
        if (!empty($model)) {
            return $model->str_value;
        }
        return '';
    }

    public static function setFieldValuesByCid(int $cid, array $fields)
    {
        //删除已有的自定义字段
        self::where('cid', $cid)->delete();

        foreach ($fields as $name => $value) {

            if (is_array($value)) {
                $value = implode("\n", $value);
            }
            $value = trim($value);
            switch ($name) {
                case 'banner':
                    $value = strlen($value) === 0 ? '' : url_image($value);
                    break;

                case 'redirect':
                    $value = str_replace([" ", "\r", "\n"], '', $value);
                    break;
            }
            $model = self::firstOrNew(['cid' => $cid, 'name' => $name]);
            $model->cid = $cid;
            $model->name = $name;
            $model->type = self::TYPE_STR;
            $model->str_value = $value;
            if (!$model->save()) {
                throw new Exception("保存字段失败: {$name}");
            }
        }
        return true;
    }


    /**
     * 切换热搜状态
     */
    public static function toggleHotSearch(int $cid)
    {
        $existingField = self::where('cid', $cid)->where('name', 'hotSearch')->first();

        if (empty($existingField)) {
            $field = new self([
                'cid' => $cid,
                'name' => 'hotSearch',
                'type' => self::TYPE_INT,
                'int_value' => 1,
                'str_value' => '1',
                'float_value' => 0
            ]);
            return $field->save();
        } else {
            $newValue = $existingField->str_value == 1 ? 0 : 1;
            $existingField->str_value = $newValue;
            $existingField->int_value = $newValue;
            return $existingField->save();
        }
    }

    public static function setHotSearch(int $cid, int $value)
    {
        $value = $value ? 1 : 0;

        $field = self::firstOrNew(['cid' => $cid, 'name' => 'hotSearch']);
        $field->cid = $cid;
        $field->name = 'hotSearch';
        $field->type = self::TYPE_INT;
        $field->str_value = (string) $value;
        $field->int_value = $value;
        $field->float_value = 0;

        return $field->save();
    }
}
