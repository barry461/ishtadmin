<?php

class AdsController extends BackendBaseController
{

    use \repositories\HoutaiRepository;

    /**
     * 列表数据过滤
     * @return Closure
     */
    protected function listAjaxIteration()
    {
        return function (AdsModel $item) {
            $item->setHidden([])->append(['position_str', 'type_str']);
            $item->img_url_full = url_image($item->img_url);
            $item->status_str = AdsModel::STATUS[$item->status];
            $item->device_str = AdsModel::DEVICE_TYPE[$item->oauth_type] ?? '未知';
            $item->size_tip = AdsModel::SIZE_TIPS[$item->position] ?? '未知';
            $item->product_type_str = AdsModel::PRODUCT_TYPE_TIPS[$item->product_type] ?? '未知';
            return $item;
        };
    }

    /**
     * 试图渲染
     * @return void
     */
    public function indexAction()
    {
        $tips = [];
        foreach (AdsModel::POSITION as $k => $v) {
            $tips[$k] = $v . '---' . (AdsModel::SIZE_TIPS[$k] ?? '');
        }
        $this->assign('tips', $tips);
        $this->display();
    }


    /**
     * 获取本控制器和哪个model绑定
     * @return string
     */
    protected function getModelClass(): string
    {
        return AdsModel::class;
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

    protected function saveAfterCallback($model, $oldModel = null)
    {
        cached('')->clearGroup(\NoticeModel::REDIS_KEY_NOTICE_LIST);
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