<?php

use Illuminate\Database\Eloquent\Model;

/**
 * class SensitiveWordsModel
 *
 *
 * @property int $id
 * @property int $status 状态
 * @property string $word 敏感词
 *
 *
 *
 * @mixin \Eloquent
 */
class SensitiveWordsModel extends Model
{
    protected $table = 'sensitive_words';
    protected $primaryKey = 'id';
    protected $fillable = ['status', 'word'];
    public $timestamps = false;
    const STATUS_NO = 0;
    const STATUS_YES = 1;
    const STATUS_TIPS = [
        self::STATUS_NO =>  '失效',
        self::STATUS_YES =>  '正常',
    ];

    public static function sensitiveHandle()
    {
        $wordData = cached("sensitive_words")
            ->fetchPhp(function () {
                return SensitiveWordsModel::query()
                    ->where('status', SensitiveWordsModel::STATUS_YES)
                    ->pluck('word')
                    ->toArray();
            }, 86400);

        return \DfaFilter\SensitiveHelper::init()->setTree($wordData);
    }

}