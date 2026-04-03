<?php


class TagController extends BackendBaseController
{

    use \repositories\HoutaiRepository;

    /**
     * 列表数据过滤
     *
     * @return Closure
     */
    protected function listAjaxIteration()
    {
        return function (TagsModel $item) {
            $item->setHidden([]);

            return $item;
        };
    }

    /**
     * 试图渲染
     *
     * @return void
     */
    public function indexAction()
    {
        $this->display();
    }


    /**
     * 获取本控制器和哪个model绑定
     *
     * @return string
     */
    protected function getModelClass(): string
    {
        return TagsModel::class;
    }

    /**
     * 定义数据操作的表主键名称
     *
     * @return string
     */
    protected function getPkName(): string
    {
        return 'id';
    }

    /**
     * 定义数据操作日志
     *
     * @return string
     * @author xiongba
     */
    protected function getLogDesc(): string
    {
        return '';
    }
}