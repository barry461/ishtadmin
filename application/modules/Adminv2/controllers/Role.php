<?php

/**
 * 角色管理 API 控制器
 *
 * 支持：
 * - 角色列表
 * - 角色详情
 * - 角色保存（新增 / 修改，含权限分配、名称修改）
 * - 角色删除
 */
class RoleController extends AdminV2BaseController
{
    /**
     * 角色列表
     *
     * GET /adminv2/role/list
     *
     * 参数：
     * - keyword: 搜索关键词（按 role_name 模糊匹配）
     */
    public function listAction()
    {
        $keyword = trim((string)($this->data['keyword'] ?? ''));

        $query = RoleModel::query();
        if ($keyword !== '') {
            $query->where('role_name', 'like', '%' . $keyword . '%');
        }

        $total = $query->count();

        $list = $query
            ->orderByDesc('role_id')
            ->offset($this->offset)
            ->limit($this->limit)
            ->get();

        $result = [];
        foreach ($list as $item) {
            /** @var RoleModel $item */
            $permissionIds = [];
            $roleActionIds = trim((string)$item->role_action_ids);
            if ($roleActionIds !== '') {
                $permissionIds = array_values(array_filter(array_map('intval', explode(',', $roleActionIds))));
            }

            $result[] = [
                'role_id' => (int)$item->role_id,
                'role_name' => (string)$item->role_name,
                'permission_ids' => $permissionIds,
            ];
        }

        return $this->pageJson($result, $total);
    }

    /**
     * 角色详情
     *
     * GET /adminv2/role/detail
     *
     * 参数：
     * - role_id / id: 角色ID（必填）
     *
     * 返回：
     * - role_id
     * - role_name
     * - permission_ids: 权限ID数组
     */
    public function detailAction()
    {
        $roleId = (int)($this->data['role_id'] ?? ($this->data['id'] ?? 0));
        if ($roleId <= 0) {
            return $this->validationError('缺少角色ID');
        }

        /** @var RoleModel|null $role */
        $role = RoleModel::find($roleId);
        if (!$role) {
            return $this->notFound('角色不存在');
        }

        $permissionIds = [];
        $roleActionIds = trim((string)$role->role_action_ids);
        if ($roleActionIds !== '') {
            $permissionIds = array_values(array_filter(array_map('intval', explode(',', $roleActionIds))));
        }

        return $this->showJson([
            'role_id' => (int)$role->role_id,
            'role_name' => (string)$role->role_name,
            'permission_ids' => $permissionIds,
        ]);
    }

    /**
     * 保存角色（新增 / 修改）
     *
     * POST /adminv2/role/save
     *
     * 参数：
     * - role_id / id: 角色ID（修改时必填；新增不填或为0）
     * - role_name: 角色名称（必填，需唯一）
     * - permission_ids: 权限ID数组（可选，不传则无权限）
     */
    public function saveAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->errorJson('请求方式错误');
        }

        $roleId = (int)($this->data['role_id'] ?? ($this->data['id'] ?? 0));
        $roleName = trim((string)($this->data['role_name'] ?? ($this->data['name'] ?? '')));
        $permissionIds = $this->data['permission_ids'] ?? [];

        if ($roleName === '') {
            return $this->validationError('角色名称不能为空');
        }

        if (!is_array($permissionIds)) {
            return $this->validationError('permission_ids 必须为数组');
        }

        // 过滤权限ID
        $permissionIds = array_values(array_unique(array_filter(array_map('intval', $permissionIds))));

        try {
            /** @var RoleModel $role */
            $role = transaction(function () use ($roleId, $roleName, $permissionIds) {
                if ($roleId > 0) {
                    $role = RoleModel::find($roleId);
                    if (empty($role)) {
                        throw new \Exception('角色不存在');
                    }
                } else {
                    $role = new RoleModel();
                }

                // 角色名称唯一性校验
                $existsQuery = RoleModel::query()->where('role_name', $roleName);
                if ($roleId > 0) {
                    $existsQuery->where('role_id', '!=', $roleId);
                }
                if ($existsQuery->exists()) {
                    throw new \Exception('角色名称已存在');
                }

                $role->role_name = $roleName;
                $role->role_action_ids = empty($permissionIds)
                    ? ''
                    : implode(',', $permissionIds);

                if (!$role->save()) {
                    throw new \Exception('保存失败');
                }

                return $role;
            });

            return $this->showJson(
                [
                    'role_id' => (int)$role->role_id,
                    'role_name' => (string)$role->role_name,
                ],
                self::STATUS_SUCCESS,
                '保存成功'
            );
        } catch (\Throwable $e) {
            return $this->errorJson('保存失败：' . $e->getMessage());
        }
    }

    /**
     * 删除角色
     *
     * POST /adminv2/role/delete
     *
     * 参数：
     * - ids: 角色ID数组（必填）
     *
     * 注意：
     * - 若有管理员正在使用该角色，则不允许删除
     */
    public function deleteAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->errorJson('请求方式错误');
        }

        $ids = (array)($this->data['ids'] ?? []);
        $ids = array_values(array_unique(array_map('intval', $ids)));

        if (empty($ids)) {
            return $this->validationError('ids 参数必填且必须为数组');
        }

        try {
            transaction(function () use ($ids) {
                // 检查是否有管理员使用这些角色
                $usedCount = ManagerModel::query()
                    ->whereIn('role_id', $ids)
                    ->count();
                if ($usedCount > 0) {
                    throw new \Exception('有管理员正在使用该角色，无法删除');
                }

                RoleModel::query()
                    ->whereIn('role_id', $ids)
                    ->delete();
            });

            return $this->successMsg('删除成功');
        } catch (\Throwable $e) {
            return $this->errorJson('删除失败：' . $e->getMessage());
        }
    }

    /**
     * 获取全部权限树（用于前端分配权限时展示）
     *
     * GET /adminv2/role/permissionTree
     *
     * 返回：PermissionModel::getTreeAll 的树状结构
     */
    public function permissionTreeAction()
    {
        $tree = PermissionModel::getTreeAll([]);
        return $this->showJson($tree);
    }
}


