<?php

use Illuminate\Events\Dispatcher;

/**
 * @property int $id 
 * @property int $aff 
 * @property int $related_id 
 * @property int $type 
 * @property int $package_id
 * @property string $created_at
 * @property string $updated_at 
 * @property string $deleted_at
 *
 * @property MvModel $mv
 * @property PictureModel $pic
 *
 * @mixin \Eloquent
 */
class UserBuyLogModel extends BaseModel
{
    protected $table = 'user_buy_log';

    protected $fillable = [
        'id',
        'aff',
        'related_id',
        'type',
        'package_id',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $primaryKey = 'id';

    CONST TYPE_MV = 1;
    CONST TYPE_BOOK = 2;
    CONST TYPE_STORY = 3;
    CONST TYPE_LINK = 4;
    CONST TYPE_SOUND_STORY = 5;
    CONST TYPE_PIC = 6;
    CONST TYPE_GIRL = 7;
    CONST TYPE_CHAT = 8;
    CONST TYPE_UNLOCK_CHAT = 9;
    CONST TYPE_UNLOCK_GIRL = 10;
    CONST TYPE_SHORT_MV = 11;
    CONST TYPE_PUA_COURSE = 12;
    CONST TYPE_PUA_TEACHER = 13;
    CONST TYPE_POST = 14;
    CONST TYPE_CONTENTS = 15;
    CONST TYPE_MVPACKAGE = 99;


    const TYPE = [
        self::TYPE_MV => '长视频',
        self::TYPE_SHORT_MV => '短视频',
        self::TYPE_BOOK => '漫画',
        self::TYPE_STORY => '小说',
        self::TYPE_LINK => '链接',
        self::TYPE_SOUND_STORY => '有声小说',
        self::TYPE_PIC => '美图',
        self::TYPE_GIRL => '嫖娼',
        self::TYPE_CHAT => '聊天',
        self::TYPE_UNLOCK_CHAT => '解锁聊天',
        self::TYPE_UNLOCK_GIRL => '解锁嫖娼',
        self::TYPE_MVPACKAGE => '视频打折包',
        self::TYPE_POST => '帖子',
        self::TYPE_CONTENTS => '内容'
    ];

    protected static function booted()
    {
        parent::booted();
        static::created(function (self $model) {
            redis()->hSet("tb:buy-" . self::getModelByContentType($model->type) . ":" . $model->aff , $model->related_id ,1);
        });
    }


    static function getStatus($aff, $relatedId, $type)
    {
        $status = self::where('aff', $aff)
            ->where('related_id', $relatedId)
            ->where('type', $type)
            ->first();
        if ($status) {
            return $status;
        } else {
            return false;
        }
    }
    // static function setStatus($aff, $relatedId, $type)
    // {
    //     $model = SELF::getModelByContentType($type);
    //     $model = $model::find($relatedId);
    //     if ($model) {
    //         $status = self::getStatus($aff, $relatedId, $type);
    //         if ($status) {
    //             $status->delete();
    //             $model->favorites = $model->favorites - 1;
    //             $model->save();
    //         } else {
    //             self::create(['aff' => $aff, 'related_id' => $relatedId, 'type' => $type]);
    //             $model->favorites = $model->favorites + 1;
    //             $model->save();
    //         }
    //         return true;
    //     } else {
    //         return false;
    //     }
    // }

    static function getModelByContentType($ContentType){
        switch($ContentType){
            case self::TYPE_BOOK:
                return 'MhModel';
            case self::TYPE_PUA_COURSE:
                return PuaCourseModel::class;
            case self::TYPE_PUA_TEACHER:
                return PuaTeacherModel::class;
            case self::TYPE_MV:
            case self::TYPE_SHORT_MV:
                return 'MvModel';
            case self::TYPE_GIRL:
                return 'GirlMeetModel';
            case self::TYPE_PIC:
                return 'PictureModel';
            case self::TYPE_CHAT:
                return 'GirlChatModel';
            case self::TYPE_STORY:
            case self::TYPE_SOUND_STORY:
                return 'StoryModel';
        }
    }

    public function contents(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ContentsModel::class, 'cid', 'related_id');
    }
}
