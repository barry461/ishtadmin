<?php


use Illuminate\Database\Eloquent\Model;

/**
 * class FieldsModel
 *
 * @property int $cid
 * @property string $name
 * @property string $type
 * @property string $str_value
 * @property int $int_value
 * @property string $float_value
 *
 * @date 2022-10-21 07:17:53
 *
 * @mixin \Eloquent
 */
class ContentFieldsDataModel extends BaseModel
{

    protected $table = "content_fields_data";

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

    protected $guarded = 'cid';

    public $timestamps = false;

    const TYPE_STR = 'str';
    const TYPE_INT = 'int';
    const TYPE_FLOAT = 'float';
    const TYPE = [
        self::TYPE_STR => 'str',
        self::TYPE_INT => 'int',
        self::TYPE_FLOAT => 'float',
    ];

    public function getStrValueAttribute()
    {
        $value = $this->attributes['str_value'] ?? '';
        $name = $this->attributes['name'] ?? '';
        if ($name == 'banner') {
            $value = trim($value);
            if (strlen($value) === 0){
                return '';
            }
            if (strpos($value ,"\n")!==false){
                $ary = explode("\n" , $value);
                $ary = array_map(function ($v){return trim($v);} , $ary);
                $ary = array_filter($ary);
                if (!empty($ary)){
                    shuffle($ary);
                    $value = $ary[0];
                }
            }
            return url_image($value);
        }elseif ($name == 'redirect'){
            $value = str_replace([" " , "\r", "\n"] , '' , $value);
            preg_match("#\/\d+\.html#", $value, $matches1);
            if ($matches1 && $matches1[0]){
                preg_match("#\d+#", $matches1[0], $matches2);
                if ($matches2 && $matches2[0]){
                    $cid = $matches2[0];
                    if ($cid > 0){
                        $redirect = FieldsModel::getRedirectStr($cid);
                        if ($redirect){
                            return $redirect;
                        }
                    }
                }
            }
            return $value;
        } else {
            return $value;
        }
    }
}
