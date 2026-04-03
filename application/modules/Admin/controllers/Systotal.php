<?php

/**
 * Class SystotalController
 */
class SystotalController extends BackendBaseController
{

    use \repositories\HoutaiRepository;

    /**
     * 列表数据过滤
     * @return Closure
     */
    protected function listAjaxIteration()
    {
        return function (SysTotalModel $item) {
            $item->setHidden([]);
            return $item;
        };
    }

    /**
     * @description 同步数据
     */
    public function sys_dataAction(){
        $url = "https://www.51cg1.com/ping.php?_yaf=_sys_total";
        try {
            \tools\HttpCurl::get($url);

            $this->ajaxSuccessMsg('发起数据同步请求成功，20秒过后再查看数据');
        }catch (Exception $e){
            $this->ajaxError('发起数据同步请求失败');
        }
    }

    protected function listAjaxOrder()
    {
        return  ['value' => 'desc'];
    }

    /**
     * 添加默认条件
     */
    public function listAjaxWhere()
    {
        return [
            [
                'name',
                'like',
                'visit:%'
            ]
        ];
    }

    /**
     * 试图渲染
     * @return void
     */
    public function indexAction()
    {
        $currDate = date('Y-m-d');
        $this->assign('currDate',$currDate);
        $this->display('successrate');
    }

    /**
     * 获取本控制器和哪个model绑定
     * @return string
     */
    protected function getModelClass(): string
    {
       return SysTotalModel::class;
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