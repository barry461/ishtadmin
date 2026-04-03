<?php

class AttachmentController extends BackendBaseController
{

    use \repositories\HoutaiRepository;

    /**
     * 列表数据过滤
     * @return Closure
     */
    protected function listAjaxIteration()
    {
        return function (AttachmentModel $item) {

           $item->slice_status = AttachmentModel::SLICE_TIPS[$item->slice_status];

            return $item;
        };
    }

    /**
     * 试图渲染
     * @return void
     */
    public function indexAction()
    {
         $this->assign('get' , $_GET);
        $this->display();
    }


    /**
     * 获取本控制器和哪个model绑定
     * @return string
     */
    protected function getModelClass(): string
    {
        return AttachmentModel::class;
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

        /**
     * 视频上传
     * @return bool
     */
    public function upload_mvAction()
    {
        try {
           
             $input = file_get_contents('php://input');
             $data = json_decode($input, true);

            // $data= json_decode($data,true);
           
            if (!$data) {

               return  $this->ajaxError('无效的数据格式');
            }
            $mp4_url = $data['mp4_url'];
            $cover = $data['cover_url'];
            $name = $data['name'];
            $cid = $data['cid'] ?? '0';

            if (!$mp4_url || !$name) {
                
               return $this->ajaxError('参数不完整');
            }
           
            $upload_type = $data['upload_type'] ?? AttachmentModel::UPLOAD_TYPE_COM;
            $service = new \service\RemoteUserContentsService();
            $service->uploadAttachment(1, $cid, $name, $mp4_url, $cover, $upload_type);
            return $this->ajaxSuccess('保存成功');
        }catch (Throwable $e){
            return $e->getMessage();
        }
    }


     public function upload_imageAction()
    {
        try {
           
             $input = file_get_contents('php://input');
             $data = json_decode($input, true);

            // $data= json_decode($data,true);
           
            if (!$data) {

               return  $this->ajaxError('无效的数据格式');
            }
            $image_url = $data['image_url'];
            $image_src = $data['image_src'];
            $name = $data['name'];
            $cid = $data['cid'] ?? '0';

            if (!$image_url || !$name) {
                
               return $this->ajaxError('参数不完整');
            }
           
            $service = new \service\RemoteUserContentsService();
            $service->uploadAttachmentImage(1, $cid, $name, $image_url, $image_src);
            return $this->ajaxSuccess('保存成功');
        }catch (Throwable $e){
            return $e->getMessage();
        }
    }

    /**
     * 获取上传地址
     * @return void
     */
    public function getr2uploadurlAction()
    {
        $data = \service\ObjectR2Service::r2UploadInfo();
        if (empty($data)) {
            return $this->ajaxError('获取上传地址失败');
        }
        return $this->ajaxSuccess($data);
    }




}