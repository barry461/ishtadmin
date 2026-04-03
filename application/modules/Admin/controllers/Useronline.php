<?php

/**
 * Class UseronlineController
 * @date 2024-04-26 08:52:01
 */
class UseronlineController extends BackendBaseController
{
    use \repositories\HoutaiRepository;

    /**
     * 列表数据过滤
     * @return Closure
     */
    protected function listAjaxIteration()
    {
        return function (UserOnlineModel $item) {
            $item->setHidden([]);
            $range = range(0,23);
            $count = 0;
            $pre7date = date("Y-m-d",strtotime($item->date)-86400*7);
            $pre7data = UserOnlineModel::where("date",$pre7date)->first();
            foreach ($range as $row){
                $key = "t".$row;
                $count += $item->$key;
                if ($item->$key==0){
                    $data = [
                        "type" => "sub",
                        "number" =>$item->$key,
                        "change"=>-100.00
                    ];
                }else{
                    if ($pre7data){
                        $percent = 100.00;
                        if ($pre7data->$key>0){
                            $percent = number_format(($item->$key-$pre7data->$key)*100 / $pre7data->$key, 2, '.', '');
                        }
                        $data = [
                            "type" => $pre7data->$key > $item->$key ? "sub" : "add",
                            "number" =>$item->$key,
                            "change"=>$percent
                        ];
                    }else{
                        $data = [
                            "type"=>"add",
                            "number"=>$item->$key,
                            "change"=>100.00
                        ];
                    }
                }

                $item->$key = $data;
            }
            $item->sum = $count;
            return $item;
        };
    }

    /**
     * 试图渲染
     * @return void
     */
    public function indexAction()
    {
        $end = date('Y-m-d');
        $start = date('Y-m-d', strtotime('-6 days', strtotime($end)));
        $this->assign('start', $start);
        $this->assign('end', $end);
        $this->display();
    }

    /**
     * 获取本控制器和哪个model绑定
     * @return string
     */
    protected function getModelClass(): string
    {
       return UserOnlineModel::class;
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