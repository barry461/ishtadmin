<?php

class FeedbackController extends BackendBaseController
{

    use \repositories\HoutaiRepository;

    /**
     * 列表数据过滤
     * @return Closure
     */
    protected function listAjaxIteration()
    {
        return function (FeedbackModel $item) {
            $item->img_url_full1 = url_image($item->img_url1);
            $item->img_url_full2 = url_image($item->img_url2);
            $item->img_url_full3 = url_image($item->img_url3);
            $item->status_str = FeedbackModel::STATUS[$item->status] ?? '未知';
            $item->type_str = FeedbackModel::TYPE[$item->type] ?? '未知';
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
        return FeedbackModel::class;
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

            $record = AdsModel::where('url_config', $from)->first();
            test_assert($record, '未找到此原始网址' . $from);

            $isOk = AdsModel::where('url_config', $from)->update(['url_config' => $to,]);
            test_assert($isOk, '系统异常');

            CacheKeysModel::where('name', '广告列表')->chunkById(1000, function ($items) {
                collect($items)->each(function (CacheKeysModel $item) {
                    redis()->expire($item->key, 3);
                });
                CacheKeysModel::whereIn('id', collect($items)->pluck('id'))->delete();
            });
            return $this->ajaxSuccess('已成功替换');
        } catch (Throwable $e) {
            return $this->ajaxError($e->getMessage());
        }
    }
}