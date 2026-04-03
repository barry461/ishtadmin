<?php

/**
 * Class GirlchatcomplaintController
 * @date 2025-04-16 04:35:03
 */
class GirlchatcomplaintController extends BackendBaseController
{

    use \repositories\HoutaiRepository;
    /**
     * 列表数据过滤
     * @return Closure
     */
    protected function listAjaxIteration()
    {
        return function (GirlChatComplaintModel $item) {
            $item->setHidden([]);
            $item->type_arr_str = implode(",", array_values($item->type_arr));
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

    /**
     * 获取本控制器和哪个model绑定
     * @return string
     */
    protected function getModelClass(): string
    {
        return GirlChatComplaintModel::class;
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