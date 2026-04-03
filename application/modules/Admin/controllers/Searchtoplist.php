<?php

/**
 * Class SettingController
 */
class SearchtoplistController extends BackendBaseController
{

    const KEY = 'search:toplist';

    public function init()
    {
        parent::init();
    }


    public function listAjaxAction(): bool
    {
        $page = $_GET['page'] ?? 1;
        $limit = $_GET['limit'] ?? 10;

        $offset = ($page - 1) * $limit;
        $limit = $offset + $limit;

        $toplist = redis()->zRevRange(self::KEY, $offset,  $limit -1, true);
        $list = [];

        foreach ($toplist as $name=>$value){
            $list[] = ['name' => $name , 'value' => $value];
        }

        $result = [
            'count' => redis()->zCard(self::KEY),
            'data'  => $list,
            "msg"   => '',
            "desc"  => '',
            'code'  => 0
        ];
        return $this->ajaxReturn($result);
    }

    public function saveAction()
    {
        $_name = trim($_POST['_name'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $value = intval($_POST['value'] ?? 0);
        if ($_name) {
            redis()->zRem(self::KEY, $_name);
        }
        if (empty($name) && empty($value)) {
            return $this->ajaxError('参数错误');
        }
        redis()->zAdd(self::KEY , $value, $name);
        return $this->ajaxSuccessMsg('操作成功');
    }


    public function delAction()
    {
        return $this->delAllAction();
    }

    public function delAllAction()
    {
        $name = trim($_POST['name'] ?? '');
        foreach (explode(',' , $name) as $k){
            redis()->zRem(self::KEY , $k);
        }
        return $this->ajaxSuccessMsg('操作成功');
    }


    /**
     * 试图渲染
     */
    public function indexAction()
    {
        $this->display();
    }



}