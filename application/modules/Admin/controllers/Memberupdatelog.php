<?php

use tools\mp4Upload;

class MemberupdatelogController extends BackendBaseController
{

    use \repositories\HoutaiRepository;

    /**
     * 列表数据过滤
     * @return Closure
     */
    protected function listAjaxIteration()
    {
        $handle = SensitiveWordsModel::sensitiveHandle();
        return function (MemberUpdateLogModel $item) use($handle) {
            $item->status_str = MemberUpdateLogModel::STATUS_TIPS[$item->status] ?? '未知';
            $update = json_decode($item->update);
            $thumb = $update->thumb ?? '';
            $item->nickname = $update->nickname ?? '';
            if ($item->nickname && $handle->islegal($item->nickname)){
                $item->nickname = $handle->mark($item->nickname, '<mark>', '</mark>');
            }
            $item->thumb = $thumb ? url_image($thumb) : '';
            $item->member = MemberModel::findByAff($item->aff);
            if ($item->member) {
                $item->member->vip_level_str = MemberModel::VIP_LEVEL[$item->member->vip_level] ?? '未知';
                $item->member->role_str = MemberModel::ROLE[$item->member->role_id] ?? '未知';
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
        $reasons = explode("\n", setting('member_reason', ''));
        $reasons = array_filter(array_unique($reasons));
        $reasons = array_combine(array_values($reasons), array_values($reasons));
        $this->assign('reasons', $reasons);
        $this->display();
    }

    /**
     * 获取本控制器和哪个model绑定
     * @return string
     */
    protected function getModelClass(): string
    {
        return MemberUpdateLogModel::class;
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
    protected function getLogDesc(): string
    {
        return '';
    }

    public function review_selectAction(): bool
    {
        try {
            $post = $this->postArray();
            $ary = explode(',', $post['ids'] ?? '');
            $ary = array_filter($ary);
            $status = $post['status'];
            $reason = $post['reason'];
            MemberUpdateLogModel::whereIn('id', $ary)
            ->get()
            ->map(function ($item) use ($status, $reason) {
                $member = MemberModel::findByAff($item->aff);
                test_assert($member, '用户不存在');

                if ($item->status != MemberUpdateLogModel::STATUS_WAIT) {
                    return;
                }

                if ($status == MemberUpdateLogModel::STATUS_PASS) {
                    $reason = '';
                }

                $item->status = $status;
                $item->refuse_reason = $reason;
                $isOk = $item->save();
                test_assert($isOk, '系统异常');

                if ($status == MemberUpdateLogModel::STATUS_PASS) {
                    $update = json_decode($item->update, true);
                    foreach ($update as $k => $v) {
                        $member->$k = $v;
                    }
                    $isOk = $member->save();
                    test_assert($isOk, '保存用户信息异常');
                    MemberModel::clearFor($member);
                }

                if ($item->status == MemberUpdateLogModel::STATUS_REJECT) {
                    $msg = sprintf(SystemNoticeModel::AUDIT_MEMBER_UNPASS_MSG, $reason);
                } else {
                    $msg = SystemNoticeModel::AUDIT_MEMBER_PASS_MSG;
                }

                $model = SystemNoticeModel::addNotice($item->aff, $msg, '审核消息');
                test_assert($model, '系统异常');
            });
            return $this->ajaxSuccessMsg('操作成功');
        } catch (Exception $e) {
            return $this->ajaxError($e->getMessage());
        }
    }
}