<?php

/**
 * Class InfovipresourcesController
 * @date 2025-04-09 13:23:10
 */
class InfovipresourcesController extends BackendBaseController
{

    use \repositories\HoutaiRepository;

    /**
     * 列表数据过滤
     * @return Closure
     */
    protected function listAjaxIteration()
    {
        return function (InfoVipResourcesModel $item) {
            $item->setHidden([]);
            $item->video_url = url_video($item->url);
            $item->type_str = InfoVipResourcesModel::TYPE_ARR[$item->type];
//            $item->url_str = $item->type == InfoVipResourcesModel::TYPE_IMAGE ? url_image($item->url) : url_video($item->url);
            $item->status_str = InfoVipResourcesModel::STATUS_ARR[$item->status];
            return $item;
        };
    }

    /**
     * 试图渲染
     * @return void
     */
    public function indexAction()
    {
        $this->assign("info_id", $_GET["where"]['info_id']);
        $this->assign('query', $_GET);
        $this->display();
    }


    /**
     * 获取本控制器和哪个model绑定
     * @return string
     */
    protected function getModelClass(): string
    {
        return InfoVipResourcesModel::class;
    }

    protected function getModelObject(): \Illuminate\Database\Eloquent\Builder
    {
        return InfoVipResourcesModel::with("info");
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