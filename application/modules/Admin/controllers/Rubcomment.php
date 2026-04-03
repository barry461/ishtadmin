<?php

class RubcommentController extends BackendBaseController
{

    use \repositories\HoutaiRepository;

    /**
     * 列表数据过滤
     * @return Closure
     */
    protected function listAjaxIteration()
    {
        $handle = SensitiveWordsModel::sensitiveHandle();
        return function (RubCommentModel $item) use($handle) {
            if ($item->comment && $handle->islegal($item->comment)){
                $item->comment = $handle->mark($item->comment, '<mark>', '</mark>');
            }

            $item->type_str = RubCommentModel::TYPE_TIPS[$item->type];
            return $item;
        };
    }

    /**
     * 试图渲染
     * @return void
     */
    public function indexAction()
    {
        $this->display();
    }

    /**
     * 获取本控制器和哪个model绑定
     * @return string
     */
    protected function getModelClass(): string
    {
        return RubCommentModel::class;
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
    protected function getLogDesc(): string
    {
        return '';
    }

    public function delAllDataAction(){
        try {
            RubCommentModel::query()->truncate();
            return $this->ajaxSuccess("操作成功");
        }catch (Throwable $e){
            return $this->ajaxError($e->getMessage());
        }
    }

    public function passBatchAction(){
        try {
            if (!$this->getRequest()->isPost())
                throw new Exception('数据异常');
            $data = $this->postArray();
            $ids = explode(',', $data['value']);
            transaction(function () use($ids){
                collect($ids)->filter()->map(function ($id){
                    $rub = RubCommentModel::find($id);
                    $data = json_decode($rub->data, true);
                    $data['admin_id'] = $this->getUser()->uid;
                    if ($rub->type == RubCommentModel::TYPE_CONTENTS){
                        $data['status'] = CommentsModel::STATUS_APPROVED;
                        $comment = CommentsModel::create($data);
                    }elseif ($rub->type == RubCommentModel::TYPE_POST){
                        $data['status'] = PostCommentModel::STATUS_PASS;
                        $comment = PostCommentModel::create($data);
                    }else{
                        throw new Exception('数据异常');
                    }
                    test_assert($comment, '数据异常');
                    $rub->delete();
                });
            });
            return $this->ajaxSuccessMsg('操作成功');
        }catch (Exception $e){
            return $this->ajaxError($e->getMessage());
        }
    }
}