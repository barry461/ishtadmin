<?php

class NoticeController extends BackendBaseController
{
    use \repositories\HoutaiRepository;

    /**
     * 列表数据过滤
     * @return Closure
     */
    protected function listAjaxIteration()
    {
        return function (NoticeModel $item) {
            $item->setHidden([]);
            $item->type_str = NoticeModel::TYPE[$item->type] ?? '';
            $item->pos_str = NoticeModel::POS[$item->pos] ?? '';
            $item->status_str = NoticeModel::STATUS[$item->status] ?? '';
            return $item;
        };
    }

    protected function saveAfterCallback($model, $oldModel = null)
    {
        cached('')->clearGroup(\NoticeModel::REDIS_KEY_NOTICE_LIST);
    }

    protected function deleteAfterCallback($model, $isDelete)
    {
        cached('')->clearGroup(\NoticeModel::REDIS_KEY_NOTICE_LIST);
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
        return NoticeModel::class;
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

            $record = NoticeModel::where('url', $from)->first();
            test_assert($record, '未找到此原始网址' . $from);

            $isOk = NoticeModel::where('url', $from)->update(['url' => $to]);
            test_assert($isOk, '系统异常');
            cached('')->clearGroup(\NoticeModel::REDIS_KEY_NOTICE_LIST);
            return $this->ajaxSuccess('已成功替换');
        } catch (Throwable $e) {
            return $this->ajaxError($e->getMessage());
        }
    }
}