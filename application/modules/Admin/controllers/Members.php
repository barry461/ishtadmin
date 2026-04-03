<?php

use service\UserService;

/**
 * Class MembersController
 * @author xiongba
 * @date 2020-05-22 09:34:46
 */
class MembersController extends BackendBaseController
{

    use \repositories\HoutaiRepository;

    const UNBAN_USERS = [160, 130, 176,196];

    /**
     * 列表数据过滤
     * @return Closure
     */
    protected function listAjaxIteration()
    {
        return function (UsersModel $item) {
            // 添加虚拟属性用于前端显示
            $item->role_str = $this->getRoleStr($item->group);
            return $item;
        };
    }

    protected function whereSelectBefore(&$query)
    {
        $ip = data_get($_GET, 'where.regip');
        if (!$this->argsEmpty($ip)) {
            $query->reorder();
        }
    }

    /**
     * 获取角色字符串
     * @param string $group
     * @return string
     */
    protected function getRoleStr($group)
    {
        return '远程系统用户';
    }

    /**
     * 保存数据
     * @return bool
     */
    public function saveAction(): bool
    {
        if (!$this->getRequest()->isPost()) {
            return $this->ajaxError('请求错误');
        }
        $className = $this->getModelClass();
        $pkName = $this->getPkName();
        $post = $this->postArray();
        try {
            // 如果是添加用户，验证密码确认
            if (empty($post['_pk'])) {
                if (empty($post['password'])) {
                    return $this->ajaxError('密码不能为空');
                }
                if (empty($post['confirmPassword'])) {
                    return $this->ajaxError('确认密码不能为空');
                }
                if ($post['password'] !== $post['confirmPassword']) {
                    return $this->ajaxError('两次输入的密码不一致');
                }
                // 移除确认密码字段，避免保存到数据库
                unset($post['confirmPassword']);
            } else {
                $where = [[$pkName, '=', $post['_pk']]];
                $model = $className::where($where)->first();
                test_assert($model, '数据不存在');
            }
            if ($model = $this->doSave($post)) {
                return $this->ajaxSuccessMsg('操作成功', 0, call_user_func($this->listAjaxIteration(), $model));
            } else {
                return $this->ajaxError('操作错误');
            }
        } catch (\Throwable $e) {
            return $this->ajaxError($e->getMessage());
        }
    }

    /**
     * 修改密码
     * @return bool
     */
    public function change_pwdAction(): bool
    {
        $id = $this->post['uid'] ?? null;
        $model = UsersModel::find($id);
        if (empty($model)) {
            return $this->ajaxError('用户不存在');
        }
        $password = $_POST['pwd'] ?? null;
        if (empty($password)) {
            return $this->ajaxError('密码为空');
        }
        
        $model->password = password_hash($password, PASSWORD_DEFAULT);
        $isOk = $model->save();
        if (!$isOk) {
            return $this->ajaxError('更新失败');
        }
        return $this->ajaxSuccessMsg('密码修改成功');
    }



    /**
     * 试图渲染
     * @return string
     */
    public function indexAction()
    {
        $this->assignThisGlobal();
        $this->display();
    }

    protected function assignThisGlobal()
    {
        $roles = [
            'remote_user' => '远程系统用户'
        ];
        $this->assign('roles', $roles);
        $this->assign('rolesJson', json_encode($roles));
    }

    protected function setName($value, $data, $pk)
    {
        if (empty($value)) {
            return $value;
        }
        $model = UsersModel::where('name', $value)->where('uid', '!=', $pk)->first();
        if (empty($model)) { // 验证通过
            return $value;
        }
        throw new RuntimeException('此用户名已经存在');
    }



    protected function saveAfterCallback($model, $oldModel = null)
    {
        // 清除用户相关缓存
        if ($model instanceof UsersModel) {
            // 可以在这里清除用户相关的缓存
            trigger_log("User saved: {$model->uid}");
        }
    }

    protected function createBeforeCallback($model)
    {
        // 如果是新用户且设置了密码，需要哈希密码
        if ($model instanceof UsersModel && !empty($model->password)) {
            $model->password = password_hash($model->password, PASSWORD_DEFAULT);
        }
        // 设置创建时间
        if (empty($model->created)) {
            $model->created = time();
        }
        // 统一设置为远程系统用户
        $model->group = 'remote_user';
        // 设置默认激活状态
        $model->activated = 1;
        // 设置最后登录时间为空
        $model->logged = 0;
    }

    protected function formatSearchVal($columnName, $val)
    {
        return trim($val);
    }

    protected function formatKey($key, $value)
    {
        if (!preg_match_all("#^([a-zA-Z_\d]+)$#i", trim($key))) {
            return [false, $value];
        }
        return [$key, $value];
    }

    protected function getSearchWhereParam()
    {
        $get = $this->getRequest()->getQuery();
        $get['where'] = $get['where'] ?? [];
        $where = [];
        foreach ($get['where'] as $key => $value) {
            if ($value === '__undefined__') {
                continue;
            }
            $value = $this->formatSearchVal($key, $value);

            list($key, $value) = $this->formatKey($key, $value);
            if (empty($key)) {
                continue;
            }
            if ($value !== '') {
                $where[] = [$key, '=', $value];
            }
        }
        return $where;
    }

    /**
     * 获取本控制器和哪个model绑定
     * @return string
     */
    protected function getModelClass(): string
    {
        return UsersModel::class;
    }

    /**
     * 定义数据操作的表主键名称
     * @return string
     */
    protected function getPkName(): string
    {
        return 'uid';
    }

    /**
     * 定义数据操作日志
     * @return string
     * @author xiongba
     */
    protected function getLogDesc(): string
    {
        return '';
    }
}