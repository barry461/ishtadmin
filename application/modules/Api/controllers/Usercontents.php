<?php


/**
 *
 * Usercontents
 *
 */
class UsercontentsController extends BaseController
{

    public function create_updateAction()
    {
        try {
            $title = $this->data['title'] ?? null;
            $body = $this->data['body'] ?? null;
            $tags = $this->data['tags'] ?? null;
            $cover = $this->data['cover'] ?? null;
            $id = intval($this->data['id'] ?? 0);
            if (empty($title) || empty($body) || empty($cover)) {
                return $this->errorJson('参数错误');
            }
            $service = new \service\UserContentsService;
            $title = html_entity_decode($title);
            $title = strip_tags($title);
            $body = html_entity_decode($body);
            $body = strip_tags($body);
            $tags = html_entity_decode($tags);

            $service->createContents($this->member, $title, $body,$cover, $tags, $id);
            return $this->successMsg('操作成功，请耐心等待审核');
        } catch (\Throwable $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    public function list_usercontentsAction(): bool
    {
        try {
            $status = $this->data['status'] ?? UserContentsModel::STATUS_PASSED;
            $service = new \service\UserContentsService;

            $list = $service->listContents($this->member, $status, $this->page , $this->limit);
            return $this->listJson($list);
        } catch (\Throwable $e) {
            return $this->errorJson($e->getMessage());
        }
    }


    public function previewAction(): ?bool
    {
        $body = $this->data['body'] ?? '';
        if (empty($body)) {
            return $this->errorJson('参数错误');
        }
        try {
            $body = strip_tags($body);
            $body = \tools\LibMarkdown::parseContent($body);
            return $this->showJson($body);
        } catch (\Throwable $e) {
            return $this->errorJson('参数错误');
        }
    }


}
