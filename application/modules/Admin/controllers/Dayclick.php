<?php

class DayclickController extends BackendBaseController
{
    use \repositories\HoutaiRepository;

    /**
     * 列表数据过滤
     * @return Closure
     */
    protected function listAjaxIteration()
    {
        return function ($item) {
            $item->type_str = DayClickModel::TYPE_TIPS[$item->type] ?? '未知';
            return $item;
        };
    }

    protected function getSearchBetweenParam()
    {
        $get = $this->getRequest()->getQuery();
        $between = $get['between'] ?? [];
        $where = [];
        foreach ($between as $k => $item) {
            $from = $item['from'] ?? '__undefined__';
            $to = $item['to'] ?? '__undefined__';
            if ($from && $from != '__undefined__') {
                $where[] = [$k, '>=', $from];
            }
            if ($to && $to != '__undefined__') {
                $where[] = [$k, '<=', $to];
            }
        }
        return $where;
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
        return DayClickModel::class;
    }

    /**
     * 定义数据操作的表主键名称
     * @return string
     */
    protected function getPkName(): string
    {
        return 'id';
    }
}