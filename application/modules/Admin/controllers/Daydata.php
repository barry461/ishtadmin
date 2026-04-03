<?php

class DaydataController extends BackendBaseController
{
    use \repositories\HoutaiRepository;

    /**
     * 列表数据过滤
     * @return Closure
     */
    protected function listAjaxIteration()
    {
        return function ($item) {
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
        return DayDataModel::class;
    }

    /**
     * 定义数据操作的表主键名称
     * @return string
     */
    protected function getPkName(): string
    {
        return 'id';
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
     * 定义数据操作日志
     * @return string
     * @author xiongba
     */
    protected function getDesc($query): string
    {
        $q = clone $query;
        $day = $q->count('id');
        $regTotal = $q->sum('reg_total');
        $activeTotal = $q->sum('active_total');
        $iosTotal = $q->sum('active_ios');
        $androidTotal = $q->sum('active_android');
        $webTotal = $q->sum('active_web');
        $payTotal = $q->sum('pay_total');
        $vipTotal = $q->sum('vip_total');
        $coinsTotal = $q->sum('coins_total');
        $regPayTotal = $q->sum('reg_pay_total');
        $msg = '天数:%s,新增:%s,活跃:%s,苹果活跃:%s,安卓活跃:%s,PWA活跃:%s,充值:%s,vip充值:%s,金币充值:%s,新增充值:%s';
        return sprintf($msg, $day, $regTotal, $activeTotal, $iosTotal, $androidTotal, $webTotal, $payTotal, $vipTotal, $coinsTotal, $regPayTotal);
    }
}