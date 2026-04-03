<?php

class FacematerialController extends BackendBaseController
{
    use \repositories\HoutaiRepository;
    use \repositories\HoutaiRepository {
        doSave as fatherSave;
    }

    protected function listAjaxIteration()
    {
        return function (FaceMaterialModel $item) {
            $item->setHidden([]);
            $item->status_str = FaceMaterialModel::STATUS_TIPS[$item->status] ?? '';
            $item->size_str = $item->thumb_w . ' X ' . $item->thumb_h;
            $item->cate_name = $item->cate ? $item->cate->name : '';
            $item->has_member = false;
            if ($item->member && $item->member->aff != 0) {
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
        return FaceMaterialModel::class;
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
        $data['up_at'] = date('Y-m-d H:i:s');
        return $this->fatherSave($data);
    }
}