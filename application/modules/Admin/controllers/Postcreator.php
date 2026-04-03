<?php

use service\UserService;

/**
 * Class PostcreatorController
 * @date 2023-06-06 12:17:12
 */
class PostcreatorController extends BackendBaseController
{

    use \repositories\HoutaiRepository;

    /**
     * 列表数据过滤
     * @return Closure
     */
    protected function listAjaxIteration()
    {
        return function (PostCreatorModel $item) {
            $item->setHidden([]);
            $item->status_str = MemberModel::AUTH_STATUS[$item->status];
            $item->ban_post_str = MemberModel::BAN_POST[$item->ban_post];
            $item->month_income_str = $item->clubs->month_income;
            $item->quarter_income_str = $item->clubs->quarter_income;
            $item->year_income_str = $item->clubs->year_income;
            $item->income_str = $item->month_income_str + $item->quarter_income_str + $item->year_income_str;
            $item->income_money = $item->member->income_money;
            return $item;
        };
    }

    public function saveAfterCallback($model, $oldModel = null)
    {
        $aff = $model->aff;
        //post_club
        $club = \PostClubsModel::findByAff($aff, true);
        if (empty($club)) {
            $club = \PostClubsModel::make();
            $club->aff = $aff;
        }
        $club->month = $model->post_club_month;
        $club->quarter = $model->post_club_quarter;
        $club->year = $model->post_club_year;
        $club->save();

        $member = MemberModel::findByAff($aff);
        //修改用户表
        $member->post_club_month = $model->post_club_month;
        $member->post_club_quarter = $model->post_club_quarter;
        $member->post_club_year = $model->post_club_year;
        $member->post_club_id = $club->id;
        $member->save();

        $member->clearCached();
        //删除帖子列表缓存
        jobs([\PostModel::class, 'clearAllCache']);
    }

    public function banInfoAction()
    {
        $id = $this->post['id'] ?? null;
        $content = $this->post['reply'] ?? '';
        $model = PostCreatorModel::find($id);
        if (empty($model)) {
            return $this->ajaxError('此用户不存在');
        }
        try {
            transaction(function () use($model,$content){
                $data = [
                    'role_id' => MemberModel::ROLE_BAN,
                    'ban_post' => MemberModel::BAN_POST_YES,
                    'person_signnatrue' => $content
                ];
                //修改用户表
                $is_ok = UserService::updateUser($model->aff, $data);
                test_assert($is_ok,'数据异常，请重试');
                //修改post_creator
                $model->ban_post = MemberModel::BAN_POST_YES;
                $model->updated_at = \Carbon\Carbon::now();
                $is_ok = $model->save();
                test_assert($is_ok,'数据异常，请重试');
            });
        }catch (Exception $e){
            return $this->ajaxError($e->getMessage());
        }

        return $this->ajaxSuccessMsg('操作成功');
    }

    // 解封
    public function unbanInfoAction()
    {
        $id = $this->post['id'] ?? null;
        $model = PostCreatorModel::find($id);
        if (empty($model)) {
            return $this->ajaxError('用户不存在');
        }

        try {
            transaction(function () use($model){
                $data = [
                    'role_id' => MemberModel::ROLE_NORMAL,
                    'ban_post' => MemberModel::BAN_POST_NO,
                    'person_signnatrue' => ''
                ];
                //修改用户表
                $is_ok = UserService::updateUser($model->aff, $data);
                test_assert($is_ok,'数据异常，请重试');
                //修改post_creator
                $model->ban_post = MemberModel::BAN_POST_NO;
                $model->updated_at = \Carbon\Carbon::now();
                $is_ok = $model->save();
                test_assert($is_ok,'数据异常，请重试');
            });
        }catch (Exception $e){
            return $this->ajaxError($e->getMessage());
        }

        return $this->ajaxSuccessMsg('操作成功');
    }

    //认证
    public function creatorAction()
    {
        $id = $this->post['id'] ?? null;
        $creator = PostCreatorModel::find($id);
        if (empty($creator)) {
            return $this->ajaxError('用户不存在');
        }
        if ($creator->status == MemberModel::AUTH_STATUS_YES){
            return $this->ajaxError('用户已经是创作者～～～～');
        }
        try {
            transaction(function () use($creator){
                //修改用户表
                $member = MemberModel::findByAff($creator->aff);
                $member->auth_status = MemberModel::AUTH_STATUS_YES;
                $isOk = $member->save();
                test_assert($isOk,"系统异常，请重试");

                //post_creator
                $creator = \PostCreatorModel::findByAff($member->aff, true);
                $creator->status = \MemberModel::AUTH_STATUS_YES;
                $creator->updated_at = \Carbon\Carbon::now();
                $isOk = $creator->save();
                test_assert($isOk,"系统异常，请重试");

                $member->clearCached();
            });
        } catch (Exception $e) {
            return $this->ajaxError($e->getMessage());
        }

        return $this->ajaxSuccessMsg('认证成功');
    }

    //取消认证
    public function uncreatorAction()
    {
        $id = $this->post['id'] ?? null;
        $creator = PostCreatorModel::find($id);
        if (empty($creator)) {
            return $this->ajaxError('用户不存在');
        }
        if ($creator->status == MemberModel::AUTH_STATUS_NO){
            return $this->ajaxError('用户创作者身份已经取消～～～～');
        }
        try {
            transaction(function () use($creator){
                //修改用户表
                $member = MemberModel::findByAff($creator->aff);
                $member->auth_status = MemberModel::AUTH_STATUS_NO;
                $isOk = $member->save();
                test_assert($isOk,"系统异常，请重试");
                //post_creator
                $creator->status = \MemberModel::AUTH_STATUS_NO;
                $creator->updated_at = \Carbon\Carbon::now();
                $isOk = $creator->save();
                test_assert($isOk,"系统异常，请重试");
                $member->clearCached();
            });
        } catch (Exception $e) {
            return $this->ajaxError($e->getMessage());
        }

        return $this->ajaxSuccessMsg('认证成功');
    }

    /**
     * 试图渲染
     * @return void
     */
    public function indexAction()
    {
        $this->display();
    }

    public function getModelObject()
    {
        return PostCreatorModel::with('clubs', 'member');
    }

    /**
     * 获取本控制器和哪个model绑定
     * @return string
     */
    protected function getModelClass(): string
    {
       return PostCreatorModel::class;
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