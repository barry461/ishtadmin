<?php

class PosttopicController extends BackendBaseController
{

    use \repositories\HoutaiRepository;

    /**
     * 列表数据过滤
     * @return Closure
     */
    protected function listAjaxIteration()
    {
        return function (PostTopicModel $item) {
            $cate = PostTopicCategoryModel::where('id', $item->pid)->first();
            $item->cate_str = $cate ? $cate->name : '';
            $item->status_str = PostTopicModel::STATUS_TIPS[$item->status];
            $item->hot_str = PostTopicModel::HOT_TIPS[$item->is_hot];
            $item->intro_str = mb_substr($item->intro_str, 10) . '...';
            return $item;
        };
    }

    /**
     * 试图渲染
     * @return void
     */
    public function indexAction()
    {
        $arr = PostTopicCategoryModel::get()->pluck('name', 'id')->toArray();
        $spid = $_GET['pid'] ?? '';
        $this->assign('cateArr', $arr);
        $this->assign('spid', $spid);
        $this->display();
    }


    /**
     * 获取本控制器和哪个model绑定
     * @return string
     */
    protected function getModelClass(): string
    {
        return PostTopicModel::class;
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
     * @author xiongba
     */
    protected function getLogDesc(): string
    {
        return '';
    }

    protected function saveAfterCallback($model, $oldModel = null)
    {
        PostTopicModel::clearCache();
    }
}