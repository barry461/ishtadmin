<?php

use Illuminate\Database\Eloquent\Model;

/**
 * class ContentsSearchModel
 *
 *
 * @property int $cid
 * @property string $title
 * @property string $slug
 * @property int $created
 * @property int $modified
 * @property string $text
 * @property int $order
 * @property int $authorId
 * @property string $template
 * @property string $type
 * @property string $status
 * @property string $password
 * @property int $commentsNum
 * @property string $allowComment
 * @property string $allowPing
 * @property string $allowFeed
 * @property int $parent
 * @property int $is_home 是否在首页展示
 * @property int $home_top
 * @property int $is_slice 默认处于切片状态
 * @property int $app_hide app端隐藏
 * @property int $favorite_num 收藏统计
 * @property int $web_show web显示
 * @property int $sort_by
 * @property int $view
 * @property int $app_view
 * @property int $web_view
 * @property string $authorName
 * @property string $category
 * @property string $tags
 *
 * @property array<FieldsModel>|\Illuminate\Database\Eloquent\Collection $fields
 *
 * @mixin \Eloquent
 */
class ContentsSearchModel extends Model
{
    protected $table = 'contents_search';
    protected $primaryKey = 'cid';
    protected $fillable = [
        'cid',
        'title',
        'slug',
        'created',
        'modified',
        'text',
        'order',
        'authorId',
        'template',
        'type',
        'status',
        'password',
        'commentsNum',
        'allowComment',
        'allowPing',
        'allowFeed',
        'parent',
        'is_home',
        'home_top',
        'is_slice',
        'app_hide',
        'favorite_num',
        'web_show',
        'sort_by',
        'view',
        'app_view',
        'web_view',
        'authorName',
        'category',
        'tags'
    ];
    protected $guarded = 'cid';
    public $timestamps = false;

    public function fields(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(FieldsModel::class, 'cid', 'cid')
            ->whereNotIn('name', [
                'disableDarkMask',
                'enableFlowChat',
                'enableMathJax',
                'enableMermaid',
                'TOC',
            ]);
    }

    public static function sysData($maxId){
        ContentsModel::query()
            ->when($maxId != 0,function ($q) use ($maxId){
                return $q->where('cid','>',$maxId);
            })
            ->where('status', ContentsModel::STATUS_PUBLISH)
            ->whereIn('type', [ContentsModel::TYPE_POST, ContentsModel::TYPE_SKITS, ContentsModel::TYPE_BIG_WENT])
            ->where('is_slice', 1)->chunkById(100,function (\Illuminate\Support\Collection $items){
                collect($items)->each(function (ContentsModel $item){
                    $model = ContentsSearchModel::create($item->getAttributes());
                    //authorName
                    /** @var UsersModel $user */
                    $user = UsersModel::where('uid',$model->authorId)->first();
                    if ($user){
                        $model->authorName = $user->screenName;
                    }
                    $midArr = RelationshipsModel::where('cid',$model->cid)->get()->pluck('mid')->toArray();
                    if ($midArr){
                        //category
                        $category = [];
                        //tags
                        $tags = [];
                        MetasModel::whereIn('mid',$midArr)->get()->map(function (MetasModel $item) use (&$category,&$tags){
                            if ($item->type == MetasModel::TYPE_CATEGORY) {
                                $category[] = $item->name;
                            } elseif ($item->type == MetasModel::TYPE_TAG) {
                                $tags[] = $item->name;
                            }
                        });
                        $model->category = implode(',',$category);
                        $model->tags = implode(',',$tags);
                    }
                    if ($model->isDirty()){
                        $model->save();
                    }
                });
            });
    }
}