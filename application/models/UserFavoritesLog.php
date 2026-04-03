<?php

use Illuminate\Events\Dispatcher;

/**
 * @property int $id
 * @property int $aff
 * @property int $related_id
 * @property int $type
 * @property string $created_at
 * @property string $updated_at
 *
 * @mixin \Eloquent
 */
class UserFavoritesLogModel extends BaseModel
{
    protected $table = 'user_favorites_log';
    protected $fillable = [
        'id',
        'aff',
        'related_id',
        'type',
        'created_at',
        'updated_at'
    ];

    protected $primaryKey = 'id';

    const USER_POST_FAVORITE_LIST = 'user:post:favorite:list:%s';

    const  TYPE_POST = 1;//帖子

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

    /**
     * @param $aff
     * @param $relatedId
     * @param $type
     * @return bool
     * @throws Throwable
     */
    static function setStatus($aff, $relatedId, $type): bool
    {
        $modelClass = self::getModelByContentType($type);
        test_assert($modelClass, '收藏类型不存在');
        $model = $modelClass::find($relatedId);
        test_assert($model, '收藏的数据不存在');
        if ($model instanceof MvModel && $model->mv_type == MvModel::MV_TYPE_SHORT) {
            $type = UserBuyLogModel::TYPE_SHORT_MV;
        }
        return transaction(function () use ($aff, $relatedId, $type, $model) {
            $status = self::getStatus($aff, $relatedId, $type);
            if ($status) {
                $isOk = $status->delete();
                test_assert($isOk, '操作失败1');
                $model->favorites = $model->favorites - 1;
            } else {
                $isOk = self::create(['aff' => $aff, 'related_id' => $relatedId, 'type' => $type]);
                test_assert($isOk, '操作失败1');
                $model->favorites = $model->favorites + 1;
                if ($type == UserBuyLogModel::TYPE_SHORT_MV && $model->favorites > 5) {
                    jobs([self::class, 'vlogForyou'], [$relatedId, $aff]);
                    if ($model->favorites > 1000) { // 全局推荐
                        redis()->sAdd('foryou', $model->id);
                    }
                }
            }
            test_assert($model->save(), '操作失败2');
            return true;
        });
    }

    public static function listFavoritePostIds($aff)
    {
        $data = \UserFavoritesLogModel::where('aff', $aff)
            ->where('type', \UserFavoritesLogModel::TYPE_POST)
            ->get()
            ->toArray();
        return array_column($data, 'related_id');
    }

    public static function isFavoritePost($aff,$postId)
    {
        $data = \UserFavoritesLogModel::where('aff', $aff)
            ->where('related_id',$postId)
            ->where('type', \UserFavoritesLogModel::TYPE_POST)
            ->first();

        if ($data){
            return 1;
        }
        return 0;
    }

    public static function favoritePostIds($aff){
        $cacheKey = sprintf(self::USER_POST_FAVORITE_LIST, $aff);
        return cached($cacheKey)
            ->fetchJson(function () use ($aff) {
                $data = self::where('aff', $aff)
                    ->where('type', self::TYPE_POST)
                    ->get()
                    ->toArray();
                return array_column($data, 'related_id');
            });
    }

}
