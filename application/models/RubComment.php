<?php

/**
 * class RubCommentModel
 *
 *
 * @property int $id
 * @property int $p_id 文章/帖子ID
 * @property int $aff 用户AFF
 * @property int $comment 留言内容
 * @property int $type 0 文章评论 1 社区帖子评论
 * @property string $ipstr IP
 * @property string $cityname 城市名
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * @property string $nickname 昵称
 * @property string $data
 *
 * @mixin \Eloquent
 */
class RubCommentModel extends BaseModel
{
    /**
     * @var array|mixed
     */
    protected $table = 'rub_comment';
    protected $primaryKey = 'id';
    protected $fillable = [
        'p_id',
        'aff',
        'comment',
        'type',
        'ipstr',
        'cityname',
        'created_at',
        'updated_at',
        'nickname',
        'data',
    ];
    const TYPE_CONTENTS = 0;
    const TYPE_POST = 1;
    const TYPE_TIPS = [
        self::TYPE_CONTENTS => '文章',
        self::TYPE_POST => '帖子',
    ];

    public static function addData($pid, $aff, $comment, $type, $ipstr, $cityname, $nickname, $data){
        $rub = self::make();
        $rub->p_id = $pid;
        $rub->aff = $aff;
        $rub->comment = $comment;
        $rub->type = $type;
        $rub->ipstr = $ipstr;
        $rub->cityname = $cityname;
        $rub->nickname = $nickname;
        $rub->data = json_encode($data);
        $rub->created_at = \Carbon\Carbon::now();
        $rub->updated_at = \Carbon\Carbon::now();
        $rub->save();
    }
}