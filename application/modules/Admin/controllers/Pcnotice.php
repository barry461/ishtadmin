<?php

class PcnoticeController extends BackendBaseController
{
    use \repositories\HoutaiRepository;

    /**
     * 列表数据过滤
     * @return Closure
     */
    protected function listAjaxIteration()
    {
        return function (PcNoticeModel $item) {
            $item->setHidden([]);
            $item->type_str = PcNoticeModel::TYPE[$item->type] ?? '';
            $item->pos_str = PcNoticeModel::POS[$item->pos] ?? '';
            $item->status_str = PcNoticeModel::STATUS[$item->status] ?? '';
            return $item;
        };
    }

    protected function saveAfterCallback($model, $oldModel = null)
    {
        cached('')->clearGroup(\PcNoticeModel::REDIS_KEY_NOTICE_LIST);
    }

    protected function deleteAfterCallback($model, $isDelete)
    {
        cached('')->clearGroup(\PcNoticeModel::REDIS_KEY_NOTICE_LIST);
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
        return PcNoticeModel::class;
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

    public function batch_replaceAction(): bool
    {
        try {
            $from = trim($_POST['from'] ?? '');
            $to = trim($_POST['to'] ?? '');
            test_assert($from, '原网址不能为空');
            test_assert($to, '新网址不能为空');
            test_assert($from != $to, '两个网址不能相同');

            $record = PcNoticeModel::where('url', $from)->first();
            test_assert($record, '未找到此原始网址' . $from);

            $isOk = PcNoticeModel::where('url', $from)->update(['url' => $to]);
            test_assert($isOk, '系统异常');
            cached('')->clearGroup(\PcNoticeModel::REDIS_KEY_NOTICE_LIST);
            return $this->ajaxSuccess('已成功替换');
        } catch (Throwable $e) {
            return $this->ajaxError($e->getMessage());
        }
    }
}