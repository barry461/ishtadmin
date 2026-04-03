<?php

class PostmediaController extends BackendBaseController
{

    use \repositories\HoutaiRepository;

    /**
     * 列表数据过滤
     * @return Closure
     */
    protected function listAjaxIteration()
    {
        return function (PostMediaModel $item) {
            $item->status_str = PostMediaModel::STATUS_TIPS[$item->status];
            $item->relate_type_str = PostMediaModel::TYPE_RELATE_TIPS[$item->relate_type];
            $item->type_str = PostMediaModel::TYPE_TIPS[$item->type];
            $default = '/static/default.jpg';
            $item->thumb = $item->type == PostMediaModel:: TYPE_VIDEO ? ($item->cover ? $item->cover : $default) : $item->media_url;
            return $item;
        };
    }

    /**
     * 试图渲染
     * @return void
     */
    public function indexAction()
    {
        $this->assign('pid', $_GET['pid'] ?? '');
        $this->assign('type', $_GET['type'] ?? '');
        $this->assign('relateType', $_GET['relate_type'] ?? '');
        $this->display();
    }


    /**
     * 获取本控制器和哪个model绑定
     * @return string
     */
    protected function getModelClass(): string
    {
        return PostMediaModel::class;
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
//        PostMediaModel::clearCache();
    }
}