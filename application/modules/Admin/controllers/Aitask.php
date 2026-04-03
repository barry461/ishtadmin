<?php

class AitaskController extends BackendBaseController
{
    use \repositories\HoutaiRepository;
    /**
     * 列表数据过滤
     * @return Closure
     */
    protected function listAjaxIteration()
    {
        return function (AiTaskModel $item) {
            $item->status_str = AiTaskModel::STATUS_TIPS[$item->status] ?? '';
            $item->pay_type_str = AiTaskModel::PAY_TYPE[$item->pay_type] ?? '';
            $item->media_url = url_image($item->media_url);
            $item->media1_url = $item->media_1;
            $item->media2_url = url_image($item->media_2);
            return $item;
        };
    }

    /**
     * 试图渲染
     * @return string
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
        return AiTaskModel::class;
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
     * @desc AI重试
     */
    public function aiDrawAction()
    {
        $id = $this->post['_pk'] ?? null;
        try {
            $model = AiTaskModel::onWriteConnection()
                ->where('id',$id)
                ->whereIn('status',[1,3])->first();
            test_assert($model, '处理中和失败才可以重试');
            \service\AiService::processTask($model);
            return $this->ajaxSuccess('重试成功');
        }catch (Throwable $e){
            return $this->ajaxError($e->getMessage());
        }
    }

    /**
     * 批量重试
     */
    public function batRetryAction(){
        if (!$this->getRequest()->isPost()) {
            return $this->ajaxError('请求错误');
        }
        try {
            $post = $this->postArray();
            $ary = explode(',', $post['ids'] ?? '');
            $ary = array_filter($ary);
            AiTaskModel::onWriteConnection()
                ->whereIn('id',$ary)
                ->get()->map(function ( AiTaskModel $item){
                    if(in_array($item->status,[1,3]) && $item->refunded == 0){
                        \service\AiService::processTask($item);
                    }
                });
            return $this->ajaxSuccess('操作成功');
        }catch (Throwable $e){
            return $this->ajaxError($e->getMessage());
        }
    }

    public function retundAction(){
        $id = $this->post['_pk'] ?? null;
        try {
            /** @var AiTaskModel $model */
            $model = AiTaskModel::onWriteConnection()
                ->where('id',$id)
                ->first();
            if($model->refunded == 0){
                $member = MemberModel::firstAff($model->aff);
                //失败返回 次数或者金币
                if($model->pay_type == AiTaskModel::PAY_TIMES){
                    $member->increment('ty_times');
                }
                if($model->pay_type == AiTaskModel::PAY_COINS){
                    $total = setting('ai_need_coins',20);
                    $member->addMoney($total,MoneyLogModel::SOURCE_AI_TY,'失败返回',$model);
                }
                $model->update(['refunded'=>1]);
            }
            return $this->ajaxSuccess('重试成功');
        } catch (\Throwable $e) {
            return $this->ajaxError($e->getMessage());
        }

    }
}