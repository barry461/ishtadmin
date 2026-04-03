<?php

/**
 * Class UsermodelController
 * @author xiongba
 * @date 2020-06-05 07:56:43
 */
class UsermodelController extends BackendBaseController
{

    use \repositories\HoutaiRepository;

    /**
     * 列表数据过滤
     * @return Closure
     */
    protected function listAjaxIteration()
    {
        return function ($item) {
            /** @var FeedQuickModel $item */
            $item->created_str = \Carbon\Carbon::createFromTimestamp($item->created_at)->toDateTimeString();
            $item->updated_str = \Carbon\Carbon::createFromTimestamp($item->updated_at)->toDateTimeString();
            $item->title_str = replace_share($item->title);
            return $item;
        };
    }

    /**
     * 试图渲染
     * @return string
     */
    public function indexAction()
    {
        $this->display();
    }


    /**
     * 获取本控制器和哪个model绑定
     * @return string
     */
    protected function getModelClass(): string
    {
        return FeedQuickModel::class;
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
}