<?php


use service\AiService;

class MemberfaceController extends BackendBaseController
{
    use \repositories\HoutaiRepository;
    use \repositories\HoutaiRepository {
        doSave as fatherSave;
    }

    protected function listAjaxIteration()
    {
        return function (MemberFaceModel $item) {
            $item->setHidden([]);
            $item->status_str = MemberFaceModel::STATUS_TIPS[$item->status] ?? '';
            $item->delete_str = MemberFaceModel::DELETE_TIPS[$item->is_delete] ?? '';
            $item->ground_size_str = $item->ground_w . ' X ' . $item->ground_h;
            $item->thumb_size_str = $item->thumb_w . ' X ' . $item->thumb_h;
            $item->face_size_str = $item->face_thumb ? $item->face_thumb_w . ' X ' . $item->face_thumb_h : '';
            $item->has_member = false;
            if ($item->member) {
                $item->member_aff = $item->member->aff;
                $item->member_username = $item->member->username;
                $item->member_nickname = $item->member->nickname;
                $item->member_vip_str = $item->member->vip_str;
                $item->member_thumb = $item->member->thumb;
                $item->member_expired_at = $item->member->expired_at;
                $item->member_role_str = MemberModel::ROLE[$item->member->role_id] ?? '未知';
                $item->has_member = true;
            }
            unset($item->cate);
            unset($item->member);
            return $item;
        };
    }

    public function indexAction()
    {
        $this->display();
    }

    protected function getModelClass(): string
    {
        return MemberFaceModel::class;
    }

    protected function getModelObject()
    {
        $class = $this->getModelClass();
        return $class::query()->with(['member']);
    }

    protected function getPkName(): string
    {
        return 'id';
    }

    protected function getLogDesc(): string
    {
        return '';
    }

    protected function doSave($data)
    {
        $member = MemberModel::where('aff', $data['aff'])->first();
        test_assert($member, '用户不存在');
        test_assert($data['ground'], '底版不能为空');
        test_assert($data['thumb'], '头像不能为空');
        return $this->fatherSave($data);
    }


    public function retryAction(): bool
    {
        try {
            $post = $this->postArray();
            $ary = explode(',', $post['ids'] ?? '');
            $ary = array_filter($ary);
            MemberFaceModel::whereIn('id', $ary)
                ->where('status', MemberFaceModel::STATUS_FAIL)
                ->get()
                ->map(function ($item) {
                    $item->status = MemberFaceModel::STATUS_WAIT;
                    $item->reason = '';
                    $isOk = $item->save();
                    test_assert($isOk, '系统异常');
                    jobs([AiService::class, 'image_face'], [$item->id]);
                });
            return $this->ajaxSuccessMsg('操作成功');
        } catch (Exception $e) {
            return $this->ajaxError($e->getMessage());
        }
    }
}