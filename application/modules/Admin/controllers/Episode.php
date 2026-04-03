<?php

/**
 * Class EpisodeController
 * @date 2024-06-10 06:55:37
 */
class EpisodeController extends BackendBaseController
{

    use \repositories\HoutaiRepository;

    /**
     * 列表数据过滤
     * @return Closure
     */
    protected function listAjaxIteration()
    {
        return function (EpisodeModel $item) {
            $item->status_str = EpisodeModel::STATUS_TIPS[$item->status];
            $item->pre_str = EpisodeModel::PRE_TIPS[$item->is_pre];
            $item->play_url_full = $item->play_url;
            $item->play_url_show = parse_url($item->play_url, PHP_URL_PATH);
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
        $this->assign('p_id', $_GET['p_id'] ?? '');
        $this->display();
    }

    /**
     * 获取本控制器和哪个model绑定
     * @return string
     */
    protected function getModelClass(): string
    {
       return EpisodeModel::class;
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