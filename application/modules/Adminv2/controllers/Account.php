<?php

class AccountController extends AdminV2BaseController
{
    /**
     * 获取当前登录用户信息
     *
     * GET /adminv2/account/profile
     *
     * 返回：
     * - uid: 管理员ID
     * - username: 登录账号
     * - role_id: 角色ID
     * - role_name: 角色名称
     * - role_type: 角色类型（admin/normal）
     * - permissions: 权限列表
     */
    public function profileAction()
    {
        $user = $this->getUser();

        // 获取角色信息
        $role = RoleModel::find($user->role_id);
        $roleName = $role ? $role->role_name : '未知';

        // 获取权限列表
        $permissions = [];
        if ($role && !empty($role->permissions)) {
            $permissions = json_decode($role->permissions, true) ?: [];
        }

        // 上次登录时间
        $lastVisit = (int) $user->lastvisit;
        $lastVisitText = $lastVisit > 0 ? date('Y-m-d H:i:s', $lastVisit) : '';

        // 显示昵称，优先使用 nickname 字段，没有则回退到用户名
        $displayName = $user->nickname ?: $user->username;

        return $this->showJson([
            'uid' => (int) $user->uid,
            'username' => (string) $user->username,
            'nickname' => (string) $displayName,
            'role_id' => (int) $user->role_id,
            'role_name' => $roleName,
            'role_type' => (string) $user->role_type,
            'permissions' => $permissions,
            'lastvisit' => $lastVisit,
            'lastvisit_text' => $lastVisitText,
        ]);
    }

    /**
     * 管理员列表
     *
     * GET /adminv2/account/list
     *
     * 参数：
     * - keyword: 搜索关键字（按 username 模糊匹配，可扩展）
     * - role_id: 按角色ID筛选
     * - status: 状态筛选（0=正常，1=禁用），对应 managers.newpm
     * - page: 页码（继承自基类）
     * - limit: 每页数量（继承自基类）
     *
     * 返回：
     * - list: 管理员数组，每项包含：
     *   - uid
     *   - username
     *   - role_id
     *   - role_name
     *   - role_type
     *   - status (0=正常,1=禁用)
     *   - lastvisit (时间戳)
     *   - lastvisit_text (格式化时间)
     * - total, page, limit, pages
     */
    public function listAction()
    {
        $keyword = trim((string) ($this->data['keyword'] ?? ''));
        $roleId = isset($this->data['role_id']) ? (int) $this->data['role_id'] : 0;
        $statusFilter = $this->data['status'] ?? null;

        $query = ManagerModel::query();

        if ($keyword !== '') {
            $query->where('username', 'like', '%' . $keyword . '%');
        }

        if ($roleId > 0) {
            $query->where('role_id', $roleId);
        }

        if ($statusFilter !== null && $statusFilter !== '') {
            $status = (int) $statusFilter ? 1 : 0;
            $query->where('newpm', $status);
        }

        // 统计总数
        $total = $query->count();

        // 分页
        $list = $query
            ->orderByDesc('uid')
            ->offset($this->offset)
            ->limit($this->limit)
            ->get();

        // 一次性取出角色映射
        $roles = RoleModel::query()->get();
        $roleMap = [];
        foreach ($roles as $role) {
            $roleMap[(int) $role->role_id] = $role->role_name;
        }

        // 拼装返回列表
        $result = [];
        foreach ($list as $item) {
            /** @var ManagerModel $item */
            $roleId = (int) $item->role_id;
            $roleName = $roleMap[$roleId] ?? '未知';

            $lastVisit = (int) $item->lastvisit;
            $lastVisitText = $lastVisit > 0 ? date('Y-m-d H:i:s', $lastVisit) : '';

            $result[] = [
                'uid' => (int) $item->uid,
                'username' => (string) $item->username,
                'role_id' => $roleId,
                'role_name' => $roleName,
                'role_type' => (string) $item->role_type,
                'status' => (int) $item->newpm, // 0=正常,1=禁用
                'lastvisit' => $lastVisit,
                'lastvisit_text' => $lastVisitText,
            ];
        }

        return $this->pageJson($result, $total);
    }

    /**
     * 退出登录接口
     * 
     * POST /adminv2/account/logout
     * 
     * 需要携带有效的 token，退出后会清除 Redis 中的 token
     */
    public function logoutAction()
    {
        // 获取当前登录用户
        $user = $this->getUser();

        if (empty($user)) {
            // 如果用户未登录，也返回成功（幂等性）
            return $this->successMsg('退出成功');
        }

        try {
            // 从 Redis 中删除 token
            $uid = $user->uid;
            redis()->hDel('manager:token', $uid);

            return $this->successMsg('退出成功');
        } catch (\Throwable $e) {
            // 即使删除失败，也返回成功（保证接口的幂等性）
            return $this->successMsg('退出成功');
        }
    }

    /**
     * 保存管理员（新增 / 修改）
     *
     * POST /adminv2/account/save
     *
     * 参数：
     * - uid/id: 管理员ID（修改时必填）
     * - username: 登录账号（必填，唯一）
     * - password: 登录密码（新增必填，修改时可选，非空则重置密码）
     * - role_id: 角色ID（必填）
     * - role_type: 角色类型（admin/normal，默认 normal）
     * - secret: 谷歌验证密钥（可选，支持修改）
     * - status: 状态（0=正常，1=禁用，可选，对应 newpm 字段）
     */
    public function saveAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->errorJson('请求方式错误');
        }

        // 兼容前端传 uid 或 id 作为主键
        $uid = (int) ($this->data['uid'] ?? ($this->data['id'] ?? 0));
        $username = trim((string) ($this->data['username'] ?? ''));
        $password = (string) ($this->data['password'] ?? '');
        $roleId = (int) ($this->data['role_id'] ?? 0);
        $roleType = (string) ($this->data['role_type'] ?? ManagerModel::ROLE_TYPE_NORMAL);
        $secret = trim((string) ($this->data['secret'] ?? ''));
        $status = isset($this->data['status']) ? (int) $this->data['status'] : null;

        // 编辑时：若未传 username（如仅重置密码），沿用原账号；新增时 username 必填
        if ($uid > 0 && $username === '') {
            $existManager = ManagerModel::find($uid);
            if ($existManager) {
                $username = (string) $existManager->username;
            }
        }
        if ($username === '') {
            return $this->validationError('登录账号不能为空');
        }
        if ($roleId <= 0 && $uid <= 0) {
            return $this->validationError('角色ID不能为空');
        }
        // 编辑时未传 role_id / role_type 则沿用原值（如仅重置密码时只传 id+password）
        if ($uid > 0 && $roleId <= 0) {
            $existManager = $existManager ?? ManagerModel::find($uid);
            if ($existManager) {
                $roleId = (int) $existManager->role_id;
                $roleType = (string) ($existManager->role_type ?: ManagerModel::ROLE_TYPE_NORMAL);
            }
        }
        if ($roleId <= 0) {
            return $this->validationError('角色ID不能为空');
        }

        // 新增管理员时必须设置密码
        if ($uid <= 0 && $password === '') {
            return $this->validationError('新增管理员时必须设置密码');
        }

        if (!in_array($roleType, array_keys(ManagerModel::ROLE_TYPE), true)) {
            return $this->validationError('角色类型不合法');
        }

        // 检查角色是否存在
        $role = RoleModel::find($roleId);
        if (empty($role)) {
            return $this->validationError('角色不存在');
        }

        try {
            /** @var ManagerModel $manager */
            $manager = transaction(function () use ($uid, $username, $password, $roleId, $roleType, $secret, $status) {
                if ($uid > 0) {
                    $manager = ManagerModel::find($uid);
                    if (empty($manager)) {
                        throw new \Exception('管理员不存在');
                    }
                } else {
                    $manager = new ManagerModel();
                    $manager->regdate = time();
                    $manager->regip = function_exists('client_ip') ? client_ip() : ($_SERVER['REMOTE_ADDR'] ?? '');
                    $manager->login_count = 0;
                }

                // 检查登录账号是否唯一（编辑且账号未改则跳过）
                $usernameUnchanged = ($manager->uid && (string) $manager->username === (string) $username);
                if (!$usernameUnchanged) {
                    $existsQuery = ManagerModel::query()->where('username', $username);
                    if ($manager->uid) {
                        $existsQuery->where('uid', '!=', $manager->uid);
                    }
                    if ($existsQuery->exists()) {
                        throw new \Exception('登录账号已存在');
                    }
                }

                $manager->username = $username;
                $manager->role_id = $roleId;
                $manager->role_type = $roleType;

                // 修改管理员时可选择是否重置密码
                if ($password !== '') {
                    $manager->password = ManagerModel::makePasswordHash($password);
                }

                // 支持修改谷歌密钥
                if ($secret !== '') {
                    $manager->secret = $secret;
                }

                // 状态：0 正常，1 禁用（映射到 newpm 字段）
                if ($status !== null) {
                    $manager->newpm = $status ? 1 : 0;
                }

                if (!$manager->save()) {
                    throw new \Exception('保存失败');
                }

                return $manager;
            });

            return $this->showJson(
                [
                    'uid' => $manager->uid,
                    'username' => $manager->username,
                ],
                self::STATUS_SUCCESS,
                '保存成功'
            );
        } catch (\Throwable $e) {
            return $this->errorJson('保存失败：' . $e->getMessage());
        }
    }

    /**
     * 删除管理员
     *
     * POST /adminv2/account/delete
     *
     * 参数：
     * - ids: 管理员ID数组（uid 列表，必填）
     */
    public function deleteAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->errorJson('请求方式错误');
        }

        $ids = (array) ($this->data['ids'] ?? []);
        $ids = array_values(array_unique(array_map('intval', $ids)));

        if (empty($ids)) {
            return $this->validationError('ids 参数必填且必须为数组');
        }

        // 不允许删除自己
        $currentUser = $this->getUser();
        if ($currentUser && in_array((int) $currentUser->uid, $ids, true)) {
            return $this->errorJson('不能删除当前登录管理员');
        }

        try {
            transaction(function () use ($ids) {
                $managers = ManagerModel::query()
                    ->whereIn('uid', $ids)
                    ->get();

                foreach ($managers as $manager) {
                    /** @var ManagerModel $manager */
                    // 删除 Redis 中的 token
                    redis()->hDel('manager:token', $manager->uid);
                    $manager->delete();
                }
            });

            return $this->successMsg('删除成功');
        } catch (\Throwable $e) {
            return $this->errorJson('删除失败：' . $e->getMessage());
        }
    }

    /**
     * 禁用 / 启用 管理员
     *
     * POST /adminv2/account/ban
     *
     * 参数：
     * - uid: 管理员ID（必填）
     *
     * 说明：
     * - 使用 managers.newpm 字段作为禁用标记：1=禁用，0=正常
     * - 禁用时会清除 Redis 中的登录 token
     */
    public function banAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->errorJson('请求方式错误');
        }

        $uid = (int) ($this->data['uid'] ?? 0);
        if ($uid <= 0) {
            return $this->validationError('uid 参数错误');
        }

        $currentUser = $this->getUser();
        if ($currentUser && (int) $currentUser->uid === $uid) {
            return $this->errorJson('不能禁用当前登录管理员');
        }

        try {
            /** @var ManagerModel|null $user */
            $user = ManagerModel::query()
                ->where('uid', $uid)
                ->first();

            if (!$user) {
                return $this->notFound('管理员不存在');
            }

            if ((int) $user->newpm === 1) {
                // 当前为禁用状态 -> 解封
                $user->newpm = 0;
                if (!$user->save()) {
                    throw new \Exception('系统异常');
                }
                // 解封不需要处理 token
                return $this->successMsg('解封成功', ['status' => 0]);
            } else {
                // 当前为正常状态 -> 封禁
                $user->newpm = 1;
                if (!$user->save()) {
                    throw new \Exception('系统异常');
                }
                // 禁用时清除登录 token
                redis()->hDel('manager:token', $user->uid);
                return $this->successMsg('封禁成功', ['status' => 1]);
            }
        } catch (\Throwable $e) {
            return $this->errorJson('操作失败：' . $e->getMessage());
        }
    }
}