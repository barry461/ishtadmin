<?php

/**
 * Class MoneyincomelogController
 * @author xiongba
 * @date 2022-03-08 04:36:54
 */
class MoneyincomelogController extends BackendBaseController
{

    use \repositories\HoutaiRepository;

    /**
     * 列表数据过滤
     * @return Closure
     */
    protected function listAjaxIteration()
    {
        return function (MoneyIncomeLogModel $item) {
            $item->setHidden([]);
            if ($item->type == MoneyIncomeLogModel::TYPE_SUB){
                $prev = $item->prev_coin;
                $item->prev_coin = $item->next_coin;
                $item->next_coin = $prev;
            }
            return $item;
        };
    }

    /**
     * 试图渲染
     * @return void
     */
    public function indexAction()
    {
        $aff = $_GET['aff'];
        $this->assign('aff',$aff);
        $this->display();
    }


    /**
     * 获取本控制器和哪个model绑定
     * @return string
     */
    protected function getModelClass(): string
    {
       return MoneyIncomeLogModel::class;
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
    protected function getLogDesc(): string {
        return '';
    }
}