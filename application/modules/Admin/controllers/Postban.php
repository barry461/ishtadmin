<?php

/**
 * Class PostbanController
 * @date 2023-06-01 06:34:11
 */
class PostbanController extends BackendBaseController
{

    use \repositories\HoutaiRepository;
    /**
     * 列表数据过滤
     * @return Closure
     */
    protected function listAjaxIteration()
    {
        return function (PostBanModel $item) {
            $item->setHidden([]);
            return $item;
        };
    }

    /**
     * 试图渲染
     * @return void
     */
    public function indexAction()
    {
        $this->display();
    }

    public function deleteAfterCallback($model, $isDelete)
    {
        $member = MemberModel::findByAff($model->aff);
        if ($member && $member->ban_post){
            $member->ban_post = MemberModel::BAN_POST_NO;
            $member->role_id = MemberModel::ROLE_NORMAL;
            $member->save();
            $member->clearCached();
        }
        $cacheKey = sprintf(PostBanModel::REDIS_POST_COMMENT_BAN, $model->aff);
        if (\tools\RedisService::get($cacheKey)){
            \tools\RedisService::del($cacheKey);
        }
        $cacheKey2 = sprintf(PostCommentModel::POST_COMMENT_LIMIT_KEY, $model->aff);
        if (\tools\RedisService::get($cacheKey2)){
            \tools\RedisService::del($cacheKey2);
        }
    }

    /**
     * 获取本控制器和哪个model绑定
     * @return string
     */
    protected function getModelClass(): string
    {
       return PostBanModel::class;
    }

    /**
     * 定义数据操作的表主键名称
     * @return string
     */
    protected function getPkName(): string
    {
        return 'id';
    }

    /**
     * 定义数据操作日志
     * @return string
     */
    protected function getLogDesc(): string {
        return '';
    }
}