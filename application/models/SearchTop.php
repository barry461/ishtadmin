<?php


use Illuminate\Database\Eloquent\Model;

/**
 * class SearchTopModel
 *
 * @property string $created_at 搜索日期
 * @property string $date 搜索日期
 * @property int $id
 * @property int $num 搜索次数
 * @property int $type
 * @property string $word 关键词
 * @property string $via 添加涞源
 *
 * @author xiongba
 * @date 2022-03-07 12:01:02
 *
 * @mixin \Eloquent
 */
class SearchTopModel extends BaseModel
{

    protected $table = "search_top";

    protected $primaryKey = 'id';

    protected $fillable = ['created_at', 'type', 'date', 'num', 'word' , 'via'];
    protected $appends = ['type_str'];

    protected $guarded = 'id';

    const UPDATED_AT = null;

    const TYPE = SearchWordModel::TYPE;


    public static function incrementNum($type, $word, $num = 1)
    {
        \SearchTopModel::updateOrInsert(
            [
                'date' => date('Y-m-d'),
                'type' => $type,
                'word' => $word,
            ],
            [
                'num' => \DB::raw("num+" . $num),
                'via' => 'api'
            ]
        );
    }


    public function getTypeStrAttribute(): string
    {
        return self::TYPE[$this->attributes['type'] ?? -1] ?? '未知';
    }


}
