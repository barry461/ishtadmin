<?php

/**
 * Class TaskemailController
 * @date 2024-06-13 12:51:00
 */
class TaskemailController extends BackendBaseController
{
    use \repositories\HoutaiRepository;

    /**
     * 列表数据过滤
     * @return Closure
     */
    protected function listAjaxIteration()
    {
        return function (TaskEmailModel $item) {
            $item->user_type_str = TaskEmailModel::USER_TYPE_TIPS[$item->user_type];
            $item->status_str = TaskEmailModel::STATUS_TIPS[$item->status];
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

    /**
     * 获取本控制器和哪个model绑定
     * @return string
     */
    protected function getModelClass(): string
    {
       return TaskEmailModel::class;
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