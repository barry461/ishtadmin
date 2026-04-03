<?php

/**
 * @property int $id
 * @property string $word
 * @property int $number
 * @property string $created_at
 * @property string $updated_at
 * @mixin \Eloquent
 */
class SearchWordModel extends BaseModel
{
    protected $table = 'search_word';

    protected $fillable = [
        'id',
        'word',
        'aff',
        'type',
        'created_at',
        'updated_at'
    ];

    protected $primaryKey = 'id';

    const TYPE_CONTENTS = 1;
    const TYPE_POST = 2;
    const TYPE = [
        self::TYPE_CONTENTS => '内容',
        self::TYPE_POST => '帖子',
    ];
    const SEARCH_TOPLIST_POST_KEY = 'search:toplist:post';

    // 创建查询记录
    public static function createSearchRecord($word, $aff, $type = \SearchWordModel::TYPE_POST)
    {
        self::create([
            'word' => $word,
            'aff' => $aff,
            'type' => $type
        ]);
        \SearchTopModel::incrementNum($type, $word);
    }

}
