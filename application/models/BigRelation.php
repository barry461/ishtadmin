<?php

/**
 * class BigRelationModel
 *
 *
 * @property int $id
 * @property int $big_id 事件ID
 * @property int $c_id 文章ID
 * @property int $order 排序
 * @property string $created_at
 * @property string $updated_at
 *
 *
 *
 * @mixin \Eloquent
 */
class BigRelationModel extends BaseModel
{
    protected $table = 'big_relation';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'big_id',
        'c_id',
        'order',
        'created_at',
        'updated_at'
    ];
    protected $guarded = 'id';
    public $timestamps = true;

    const CK_BIG_RELATION_LIST = 'ck:big:relation:%d';
    const GP_BIG_RELATION_LIST = 'gp:big:relation';
    const CN_BIG_RELATION_LIST = '大事件文章列表';

    public static function list_ids($bid){
        $key = sprintf(self::CK_BIG_RELATION_LIST, $bid);
        return cached($key)
            ->group(self::GP_BIG_RELATION_LIST)
            ->chinese(self::CN_BIG_RELATION_LIST)
            ->fetchJson(function () use ($bid){
                $ids = self::where('big_id', $bid)->orderByDesc('order')->pluck('c_id')->toArray();

                $table = Yaf_Registry::get('database')->prefix;
                $fullTable = $table.'contents';
                $list = ContentsModel::query()
                    ->with([
                        'relationships' => function ($query) {
                            $query->with('meta');
                        },
                    ])
                    ->selectRaw("$fullTable.cid,title,created,`order`,type,status,commentsNum,is_home,home_top,is_slice,authorId,view")
                    ->with('fields', 'author')
                    ->whereIn('cid', $ids)
                    ->where('status', ContentsModel::STATUS_PUBLISH)
                    ->where('type', ContentsModel::TYPE_POST)
                    ->where('is_slice', 1)
                    ->where('app_hide', ContentsModel::APP_HIDE_NO)
                    ->get()
                    ->each(function (ContentsModel $model) {
                        $model->loadTagWithCategory();
                    });

                return array_keep_idx($list, $ids, 'cid');
            });
    }
}