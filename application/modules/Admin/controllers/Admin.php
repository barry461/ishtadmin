<?php

/**
 * Class AdminlogController
 * @author xiongba
 * @date 2020-01-17 18:57:38
 */
class AdminController extends BackendBaseController
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
       
        $roleArray = RoleModel::get()->toArray();
        return function ($item) use ($roleArray) {
            /** @var ManagerModel $item */
            $role = RoleModel::find($item->role_id);
            if (empty($role)) {
                $item->role_name = '未知';
            } else {
                $item->role_name = $role->role_name;
            }
            $item->lastvisit = date('Y-m-d H:i:s', $item->lastvisit);
            $item->rule = $roleArray;
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
        $roleData = RoleModel::get();
        $this->assign('roleArray', array_column($roleData->toArray(), 'role_name', 'role_id'));
        $this->display();
    }


    public function setPassword($value, $data, $pk)
    {
        if (empty($value)) {
            return null;
        }
        return md5($value);
    }


    /**
     * 获取对应的model名称
     * @return string
     * @author xiongba
     * @date 2020-01-17 18:57:38
     */
    protected function getModelClass(): string
    {
        return ManagerModel::class;
    }

    /**
     * 定义数据操作的表主键名称
     * @return string
     * @author xiongba
     * @date 2020-01-17 18:57:38
     */
    protected function getPkName(): string
    {
        return 'uid';
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

    public function qrcodeAction()
    {
        $uid = $_POST['uid'] ?? '';
        $user = \ManagerModel::query()->where("uid", $uid)->first();
        if (!$user) {
            $this->showJson('操作成功');
        }
        $info = $this->getCode($user->username);
        $user->secret = $info['scret'];
        if (!$user->save()) {
            $this->showJson('操作异常');
        }
        $this->showJson($info['url']);
    }

    public function banAction()
    {
        try {
            $uid = $_POST['uid'] ?? '';
            $user = \ManagerModel::query()
                ->where("uid", $uid)
                ->first();
            test_assert($user, '管理员不存在');

            if ($user->newpm == 1) {
                $user->newpm = 0;
                $isOk = $user->save();
                test_assert($isOk, '系统异常');
                redis()->del('manager_lock_' . $user->username);
                $this->ajaxSuccessMsg('解封成功');
            } else {
                $user->newpm = 1;
                $isOk = $user->save();
                test_assert($isOk, '系统异常');
                $this->ajaxSuccessMsg('封禁成功');
            }
        } catch (Throwable $e) {
            $this->ajaxError($e->getMessage());
        }
    }
}