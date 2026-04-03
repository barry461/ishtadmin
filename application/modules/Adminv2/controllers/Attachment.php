<?php

/**
 * 附件管理 API 控制器 (RESTful)
 */
class AttachmentController extends AdminV2BaseController
{
    /**
     * 附件列表
     * GET /adminv2/attachment/list
     * 
     * 参数:
     * - keyword: 名称搜索
     * - slice_status: 切片状态 (0/1/2)
     * - upload_status: 上传状态 (0/1)
     * - user_id: 用户ID
     * - page: 页码
     * - limit: 每页数量
     */
    public function listAction()
    {
        [$list, $total] = AttachmentModel::getPageList($this->data, $this->limit, $this->offset);
        return $this->pageJson($list, $total);
    }

    /**
     * 附件详情
     * GET /adminv2/attachment/detail
     * 
     * 参数:
     * - id: 附件ID (必填)
     */
    public function detailAction()
    {
        $id = (int) ($this->data['id'] ?? 0);
        if (!$id) {
            return $this->validationError('缺少附件ID');
        }

        $attachment = AttachmentModel::find($id);
        if (!$attachment) {
            return $this->notFound('附件不存在');
        }

        return $this->showJson($attachment);
    }

    /**
     * 上传视频
     * POST /adminv2/attachment/uploadVideo
     * 
     * 参数:
     * - mp4_url: MP4地址 (必填)
     * - cover_url: 封面地址
     * - name: 名称 (必填)
     * - cid: 文章ID
     * - upload_type: 上传类型 (0普通/1分段)
     */
    public function uploadVideoAction()
    {
        $mp4Url = $this->data['mp4_url'] ?? '';
        $cover = $this->data['cover_url'] ?? '';
        $name = $this->data['name'] ?? '';
        $cid = $this->data['cid'] ?? '0';
        $uploadType = $this->data['upload_type'] ?? AttachmentModel::UPLOAD_TYPE_COM;

        if (empty($mp4Url)) {
            return $this->validationError('MP4地址不能为空');
        }
        if (empty($name)) {
            return $this->validationError('名称不能为空');
        }

        try {
            $service = new \service\RemoteUserContentsService();
            $service->uploadAttachment(1, $cid, $name, $mp4Url, $cover, $uploadType);
            return $this->successMsg('保存成功');
        } catch (\Throwable $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    /**
     * 上传图片
     * POST /adminv2/attachment/uploadImage
     * 
     * 参数:
     * - image_url: 图片URL (必填)
     * - image_src: 图片源
     * - name: 名称 (必填)
     * - cid: 文章ID
     */
    public function uploadImageAction()
    {
        $imageUrl = $this->data['image_url'] ?? '';
        $imageSrc = $this->data['image_src'] ?? '';
        $name = $this->data['name'] ?? '';
        $cid = $this->data['cid'] ?? '0';

        if (empty($imageUrl)) {
            return $this->validationError('图片地址不能为空');
        }
        if (empty($name)) {
            return $this->validationError('名称不能为空');
        }

        try {
            $service = new \service\RemoteUserContentsService();
            $service->uploadAttachmentImage(1, $cid, $name, $imageUrl, $imageSrc);
            return $this->successMsg('保存成功');
        } catch (\Throwable $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    /**
     * 获取R2上传地址
     * GET /adminv2/attachment/r2UploadUrl
     */
    public function r2UploadUrlAction()
    {
        $data = \service\ObjectR2Service::r2UploadInfo();
        if (empty($data)) {
            return $this->errorJson('获取上传地址失败');
        }
        return $this->showJson($data);
    }

    /**
     * 删除附件
     * POST /adminv2/attachment/delete
     * 
     * 参数:
     * - ids: 附件ID数组 (必填)
     */
    public function deleteAction()
    {
        $ids = (array) ($this->data['ids'] ?? []);
        if (empty($ids)) {
            return $this->validationError('缺少附件ID');
        }

        $res = AttachmentModel::whereIn('id', $ids)->delete();

        if ($res) {
            return $this->successMsg('删除成功');
        }
        return $this->errorJson('删除失败');
    }

    /**
     * 获取切片状态选项
     * GET /adminv2/attachment/sliceStatusOptions
     */
    public function sliceStatusOptionsAction()
    {
        $options = [];
        foreach (AttachmentModel::SLICE_TIPS as $value => $label) {
            $options[] = ['value' => $value, 'label' => $label];
        }
        return $this->showJson($options);
    }

    /**
     * 获取上传状态选项
     * GET /adminv2/attachment/uploadStatusOptions
     */
    public function uploadStatusOptionsAction()
    {
        $options = [];
        foreach (AttachmentModel::UPLOAD_STATUS_TIPS as $value => $label) {
            $options[] = ['value' => $value, 'label' => $label];
        }
        return $this->showJson($options);
    }
}
