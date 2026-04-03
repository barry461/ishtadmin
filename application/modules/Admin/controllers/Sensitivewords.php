<?php

/**
 * Class SensitivewordsController
 *
 * @date 2023-08-03 04:19:32
 */
class SensitivewordsController extends BackendBaseController
{

    use \repositories\HoutaiRepository;

    /**
     * 列表数据过滤
     *
     * @return Closure
     */
    protected function listAjaxIteration()
    {
        return function (SensitiveWordsModel $item) {
            $item->setHidden([]);
            $item->status_str = SensitiveWordsModel::STATUS_TIPS[$item->status];
            return $item;
        };
    }

    public function importAction(){
        $text = trim($_POST['text'] ?? '');
        
        if (empty($text)) {
            return $this->ajaxError('请输入敏感词');
        }
        
        $ary = collect(explode("\n", $text))->unique()->map(function ($v) {
            $v = trim($v);
            if (empty($v)) {
                return null;
            }

            return ['word' => $v, 'status' => SensitiveWordsModel::STATUS_YES];
        })->filter()->values();
        
        if ($ary->isEmpty()) {
            return $this->ajaxError('没有有效的敏感词');
        }
        
        $count = 0;
        $ary->chunk(500)->each(function ($items) use (&$count) {
            SensitiveWordsModel::insert($items->toArray());
            $count += $items->count();
        });
        
        // 清理缓存
        cached('sensitive_words')->clearCached();
        
        return $this->ajaxSuccessMsg("成功导入 {$count} 个敏感词");
    }

    public function clearCacheAction(){
        cached('sensitive_words')->clearCached();
        return $this->ajaxSuccessMsg('ok');
    }

    /**
     * 试图渲染
     *
     * @return void
     */
    public function indexAction()
    {
        $this->display();
    }

    /**
     * 获取本控制器和哪个model绑定
     *
     * @return string
     */
    protected function getModelClass(): string
    {
        return SensitiveWordsModel::class;
    }

    /**
     * 定义数据操作的表主键名称
     *
     * @return string
     */
    protected function getPkName(): string
    {
        return 'id';
    }

    /**
     * 定义数据操作日志
     *
     * @return string
     */
    protected function getLogDesc(): string
    {
        return '';
    }

    /**
     * 保存后回调：清理缓存
     */
    protected function saveAfterCallback($model, $oldModel = null)
    {
        cached('sensitive_words')->clearCached();
    }

    /**
     * 删除后回调：清理缓存
     */
    protected function deleteAfterCallback($model, $isDelete)
    {
        if ($isDelete) {
            cached('sensitive_words')->clearCached();
        }
    }
}