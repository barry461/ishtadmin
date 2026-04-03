<?php

/**
 * Class AdminlogController
 * @author xiongba
 * @date 2020-01-17 18:57:38
 */
class AdminlogController extends BackendBaseController
{

    use \repositories\HoutaiRepository;

    /**
     * 列表数据过滤
     * @return Closure
     * @author xiongba
     * @date 2019-12-02 17:08:03
     */
    protected function listAjaxIteration()
    {
        return function ($item) {
            $item->action_name = AdminLogModel::ACTION_TIPS[$item->action] ?? '';
            return $item;
        };
    }

    /**
     * 试图渲染
     * @return string
     * @author xiongba
     * @date 2020-01-17 18:57:38
     */
    public function indexAction()
    {
        $this->display();
    }


    /**
     * 获取对应的model名称
     * @return string
     * @author xiongba
     * @date 2020-01-17 18:57:38
     */
    protected function getModelClass(): string
    {
        return AdminLogModel::class;
    }

    /**
     * 定义数据操作的表主键名称
     * @return string
     * @author xiongba
     * @date 2020-01-17 18:57:38
     */
    protected function getPkName(): string
    {
        return 'id';
    }

    /**
     * 定义数据操作的表主键名称
     * @return string
     * @author xiongba
     * @date 2019-11-04 17:19:41
     */
    protected function getLogDesc(): string
    {
        return '';
    }

    /**
     * 获取列表数据
     */
    public function listAjaxAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            return $this->ajaxError('加载错误');
        }

        // \DB::enableQueryLog();
        $pkName = $this->getPkName();
        /** @var \Illuminate\Database\Eloquent\Builder $modelBuilder */
        $modelBuilder = $this->getModelObject();
        $orderBy = $this->listAjaxOrder();
        if (empty($orderBy)) {
            $modelBuilder->orderBy($pkName, 'desc');
        } else {
            foreach ($orderBy as $column => $direction) {
                $modelBuilder->orderBy($column, $direction);
            }
        }

        $where = array_merge(
            $this->builderWhereArray(),
            $this->listAjaxWhere()
        );

        if (!empty($where)) {
            $modelBuilder->where($where);
        }
        $whereRaw = $this->builderRawWhereArray();
        foreach ($whereRaw as $v) {
            $modelBuilder->whereRaw($v);
        }

        /** @var \Illuminate\Database\Eloquent\Model $modelBuilder */

        list($limit, $offset) = self::limitOffsetByGet();
        $oldBuilder = clone $modelBuilder;
        $data = $modelBuilder->limit($limit)->offset($offset)->get()->map($this->listAjaxIteration());

        $result = [
            'count' => empty($data) ? 0 : $data->count(),
            'data'  => $data,
            "msg"   => '',
            "desc"  => $this->getDesc($oldBuilder),
            'code'  => 0
        ];
        // trigger_logger(\DB::getQueryLog());
        return $this->ajaxReturn($result);
    }

    public function builderRawWhereArray()
    {
        $jsons = $_GET['json'] ?? [];
        $where = [];
        foreach ($jsons as $k => $v) {
            if ($v == '__undefined__') {
                continue;
            }
            // context->$.old.aff=100
            $where[] = 'json_valid(' . $k . ')=1 and ' . $k . '->' . $v;
        }
        return $where;
    }
}