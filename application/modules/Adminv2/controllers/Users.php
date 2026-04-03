<?php

/**
 * 用户管理 API 控制器
 * 包括作者管理、远程用户管理、用户分组管理
 */
class UsersController extends AdminV2BaseController
{
    // ========== 作者管理 ==========

    /**
     * 作者列表
     * GET /adminv2/users/authors
     * 
     * 参数:
     * - keyword: 搜索关键词 (name/screenName/mail)
     * - group: 用户分组筛选
     * - page, limit: 分页
     */
    public function authorsAction()
    {
        [$list, $total] = UsersModel::getAuthorsList($this->data, $this->limit, $this->offset);

        return $this->pageJson($list, $total);
    }

    /**
     * 作者详情
     * GET /adminv2/users/authorDetail
     * 
     * 参数:
     * - uid: 用户ID (必填)
     */
    public function authorDetailAction()
    {
        if (empty($uid = (int) ($this->data['uid'] ?? 0))) {
            return $this->validationError('uid 参数必填');
        }

        $author = UsersModel::getAuthorDetail($uid);

        if (!$author) {
            return $this->notFound('作者不存在');
        }

        return $this->showJson($author);
    }

    /**
     * 保存作者 (创建/更新)
     * POST /adminv2/users/saveAuthor
     * 
     * 参数:
     * - uid: 用户ID (更新时必填)
     * - name: 用户名 (必填)
     * - password: 密码 (创建时必填)
     * - mail: 邮箱 (必填)
     * - screenName: 显示名称 (必填)
     * - group: 用户分组
     * - url: 个人网址
     * - seo_title/seo_keywords/seo_description: SEO信息
     */
    public function saveAuthorAction()
    {
        // 参数验证
        $errors = $this->validateAuthorParams();
        if ($errors) {
            return $this->validationError('参数验证失败', $errors);
        }

        $author = transaction(function () {
            return UsersModel::saveAuthor($this->data);
        });

        return $this->showJson(
            ['uid' => $author->uid],
            self::STATUS_SUCCESS,
            '保存成功'
        );
    }

    /**
     * 删除作者
     * POST /adminv2/users/deleteAuthor
     * 
     * 参数:
     * - ids: 用户ID数组 (必填)
     */
    public function deleteAuthorAction()
    {
        if (empty($ids = (array) ($this->data['ids'] ?? []))) {
            return $this->validationError('ids 参数必填且必须为数组');
        }

        $result = transaction(function () use ($ids) {
            return UsersModel::deleteAuthors($ids);
        });

        if ($result !== false) {
            return $this->successMsg('删除成功');
        }
        return $this->errorJson('删除失败');
    }

    // ========== 远程用户管理 ==========

    /**
     * 远程用户列表
     * GET /adminv2/users/members
     * 
     * 参数:
     * - keyword: 搜索关键词 (nickname/oauth_id/username)
     * - oauth_type: 设备类型筛选
     * - vip: VIP等级筛选
     * - page, limit: 分页
     */
    public function membersAction()
    {
        [$list, $total] = MemberModel::getMembersList($this->data, $this->limit, $this->offset);

        return $this->pageJson($list, $total);
    }

    /**
     * 远程用户详情
     * GET /adminv2/users/memberDetail
     * 
     * 参数:
     * - uid: 用户ID (必填)
     */
    public function memberDetailAction()
    {
        if (empty($uid = (int) ($this->data['uid'] ?? 0))) {
            return $this->validationError('uid 参数必填');
        }

        $member = MemberModel::getMemberDetail($uid);

        if (!$member) {
            return $this->notFound('用户不存在');
        }

        return $this->showJson($member);
    }

    /**
     * 更新远程用户
     * POST /adminv2/users/updateMember
     * 
     * 参数:
     * - uid: 用户ID (必填)
     * - nickname: 昵称
     * - vip_level: VIP等级
     * - coins: 铜钱
     * - money: 哩币
     * - ban_post: 禁言状态
     * - role_id: 角色ID
     */
    public function updateMemberAction()
    {
        if (empty($uid = (int) ($this->data['uid'] ?? 0))) {
            return $this->validationError('uid 参数必填');
        }

        // 参数验证
        $errors = $this->validateMemberParams();
        if ($errors) {
            return $this->validationError('参数验证失败', $errors);
        }

        $result = transaction(function () use ($uid) {
            return MemberModel::updateMember($uid, $this->data);
        });

        if ($result) {
            return $this->successMsg('更新成功');
        }
        return $this->errorJson('更新失败');
    }

    /**
     * 删除远程用户
     * POST /adminv2/users/deleteMember
     * 
     * 参数:
     * - ids: 用户ID数组 (必填)
     */
    public function deleteMemberAction()
    {
        if (empty($ids = (array) ($this->data['ids'] ?? []))) {
            return $this->validationError('ids 参数必填且必须为数组');
        }

        $result = transaction(function () use ($ids) {
            return MemberModel::deleteMembers($ids);
        });

        if ($result !== false) {
            return $this->successMsg('删除成功');
        }
        return $this->errorJson('删除失败');
    }

    // ========== 用户分组管理 ==========

    /**
     * 获取用户分组列表
     * GET /adminv2/users/groups
     */
    public function groupsAction()
    {
        $groups = UsersModel::getAllGroups();
        return $this->showJson($groups);
    }

    // ========== 参数验证 ==========

    /**
     * 验证作者保存参数
     */
    private function validateAuthorParams(): array
    {
        $errors = [];
        $isCreate = empty($this->data['uid']);

        // name 必填，且不能包含中文，仅支持英文、数字、下划线
        if (empty(trim((string) ($this->data['name'] ?? '')))) {
            $errors['name'][] = '作者姓名不能为空';
        } elseif (preg_match('/[\x{4e00}-\x{9fa5}]/u', $this->data['name'])) {
            $errors['name'][] = '作者姓名不能包含中文，仅支持3-20位英文字母、数字或下划线';
        } elseif (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', trim($this->data['name']))) {
            $errors['name'][] = '作者姓名仅支持3-20位英文字母、数字或下划线';
        }

        // password 创建时必填
        if ($isCreate && empty($this->data['password'])) {
            $errors['password'][] = '密码不能为空';
        } elseif (!empty($this->data['password']) && mb_strlen($this->data['password']) < 6) {
            $errors['password'][] = '密码长度不能少于6个字符';
        }

        // mail 必填
        if (empty($this->data['mail'])) {
            $errors['mail'][] = '邮箱不能为空';
        } elseif (!filter_var($this->data['mail'], FILTER_VALIDATE_EMAIL)) {
            $errors['mail'][] = '邮箱格式不正确';
        }

        // screenName 必填
        if (empty($this->data['screenName'])) {
            $errors['screenName'][] = '显示名称不能为空';
        } elseif (mb_strlen($this->data['screenName']) > 50) {
            $errors['screenName'][] = '显示名称长度不能超过50个字符';
        }

        // group 可选,验证值
        if (isset($this->data['group'])) {
            $validGroups = array_column(UsersModel::getAllGroups(), 'value');
            if (!in_array($this->data['group'], $validGroups)) {
                $errors['group'][] = '用户分组不合法';
            }
        }

        return $errors;
    }

    /**
     * 验证远程用户更新参数
     */
    private function validateMemberParams(): array
    {
        $errors = [];

        // nickname 可选,长度限制
        if (isset($this->data['nickname']) && mb_strlen($this->data['nickname']) > 50) {
            $errors['nickname'][] = '昵称长度不能超过50个字符';
        }

        // vip_level 可选,范围限制
        if (isset($this->data['vip_level']) && !in_array($this->data['vip_level'], range(0, 10))) {
            $errors['vip_level'][] = 'VIP等级必须在0-10之间';
        }

        // coins 可选,非负整数
        if (isset($this->data['coins']) && (!is_numeric($this->data['coins']) || $this->data['coins'] < 0)) {
            $errors['coins'][] = '铜钱必须是非负整数';
        }

        // money 可选,非负整数
        if (isset($this->data['money']) && (!is_numeric($this->data['money']) || $this->data['money'] < 0)) {
            $errors['money'][] = '哩币必须是非负整数';
        }

        // ban_post 可选,0或1
        if (isset($this->data['ban_post']) && !in_array($this->data['ban_post'], [0, 1])) {
            $errors['ban_post'][] = '禁言状态必须是0或1';
        }

        // role_id 可选,有效值
        if (isset($this->data['role_id'])) {
            $validRoles = [
                MemberModel::ROLE_NORMAL,
                MemberModel::ROLE_FORBIDDEN,
                MemberModel::ROLE_BAN,
                MemberModel::ROLE_CHANNEL,
                MemberModel::ROLE_BROKER,
                MemberModel::ROLE_AUTH_AGENT,
                MemberModel::ROLE_AUTH_PERSONAL,
            ];
            if (!in_array($this->data['role_id'], $validRoles)) {
                $errors['role_id'][] = '角色ID不合法';
            }
        }

        return $errors;
    }
}
