<?php

use service\AiService;
use helper\QueryHelper;
use helper\Validator;

class AiController extends BaseController
{
    /**
     * 申请脱衣
     * @return bool
     */
    public function applyAction()
    {
        try {
            $Validator = \helper\Validator::make($this->data, [
                'media_url'   => 'required',
            ]);
            if ($Validator->fail($msg)) {
                throw new Exception($msg);
            }
            $img['media_url'] = $this->data['media_url']??'';
            $img['thumb_width'] = $this->data['thumb_width']??0;
            $img['thumb_height'] = $this->data['thumb_height']??0;
            AiService::addTask($img,$this->member);
            return $this->successMsg('申请成功');
        } catch (\Throwable $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    public function retryAction(){
        try {
            $Validator = \helper\Validator::make($this->data, [
                'id'   => 'required',
            ]);
            if ($Validator->fail($msg)) {
                throw new Exception($msg);
            }
            $id = $this->data['id'];
            AiService::retry($id);
            return $this->successMsg('重新申请成功');
        } catch (\Throwable $e) {
            return $this->errorJson($e->getMessage());
        }
    }


    public function listAction(){
        try {
            list($page, $limit) = QueryHelper::pageLimit();
            $service = new AiService();
            $aff = $this->member->aff;
            $list = $service->getList($aff, $page, $limit);
            return $this->listJson($list);
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }
    public function my_stripAction(){
        try {
            $status = (int)$this->data['status']?? 'all';
            list($page, $limit) = QueryHelper::pageLimit();
            $service = new AiService();
            $aff = $this->member->aff;
            $list = $service->myStrip($aff,$page, $limit,$status);
            return $this->listJson($list);
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }



    public function list_face_materialAction(): bool
    {
        try {
//            $validator = Validator::make($this->data, [
//                'id'   => 'required|numeric|min:0',
//                'type' => 'required|enum:rec,use,up',
//                'sort' => 'required|enum:asc,desc',
//            ]);
//            $rs = $validator->fail($msg);
//            test_assert(!$rs, $msg);
//
//            $id = (int)$this->data['id'];
//            $type = trim($this->data['type']);
//            $sort = trim($this->data['sort']);
//            $member = $this->member;
            list($page, $limit) = QueryHelper::pageLimit();
            $service = new AiService();
            $data = $service->list_face_material($page, $limit);
            return $this->showJson($data);
        } catch (Throwable $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    public function change_faceAction(): bool
    {
        try {
            $validator = Validator::make($this->data, [
                'id'      => 'required|numeric|min:1',
                'thumb'   => 'required',
                'thumb_w' => 'required',
                'thumb_h' => 'required',
            ]);
            $rs = $validator->fail($msg);
            test_assert(!$rs, $msg);

            $id = (int)$this->data['id'];
            $thumb = trim($this->data['thumb']);
            $thumb_w = (int)$this->data['thumb_w'];
            $thumb_h = (int)$this->data['thumb_h'];
            $member = $this->member;
            $service = new AiService();
            $service->change_face($member, $id, $thumb, $thumb_w, $thumb_h);
            return $this->successMsg('上传成功,等待处理');
        } catch (Throwable $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    public function customize_faceAction(): bool
    {
        try {
            $validator = Validator::make($this->data, [
                'ground'   => 'required',
                'ground_w' => 'required',
                'ground_h' => 'required',
                'thumb'    => 'required',
                'thumb_w'  => 'required',
                'thumb_h'  => 'required',
            ]);
            $rs = $validator->fail($msg);
            test_assert(!$rs, $msg);

            $ground = trim($this->data['ground']);
            $ground_w = (int)$this->data['ground_w'];
            $ground_h = (int)$this->data['ground_h'];
            $thumb = trim($this->data['thumb']);
            $thumb_w = (int)$this->data['thumb_w'];
            $thumb_h = (int)$this->data['thumb_h'];
            $member = $this->member;
            $service = new AiService();
            $service->customize_face($member, $ground, $ground_w, $ground_h, $thumb, $thumb_w, $thumb_h);
            return $this->successMsg('上传成功,等待处理');
        } catch (Throwable $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    public function my_faceAction(): bool
    {
        try {
            $validator = Validator::make($this->data, [
                'status' => 'required|numeric',
            ]);
            $rs = $validator->fail($msg);
            test_assert(!$rs, $msg);

            $status = (int)$this->data['status'];
            list($page, $limit) = QueryHelper::pageLimit();
            $member = $this->member;
            $service = new AiService();
            $data = $service->list_my_face($member, $status, $page, $limit);
            return $this->showJson($data);
        } catch (Throwable $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    public function del_faceAction(): bool
    {
        try {
            $validator = Validator::make($this->data, [
                'ids' => 'required',
            ]);
            $rs = $validator->fail($msg);
            test_assert(!$rs, $msg);

            $member = $this->member;
            $ids = $this->data['ids'];
            $service = new AiService();
            $service->del_face($member, $ids);

            return $this->successMsg('操作成功');
        } catch (Throwable $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    public function del_stripAction(): bool
    {
        try {
            $validator = Validator::make($this->data, [
                'ids' => 'required',
            ]);
            $rs = $validator->fail($msg);
            test_assert(!$rs, $msg);

            $member = $this->member;
            $ids = $this->data['ids'];
            $service = new AiService();
            $service->del_strip($member, $ids);

            return $this->successMsg('操作成功');
        } catch (Throwable $e) {
            return $this->errorJson($e->getMessage());
        }
    }

}