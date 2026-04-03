<?php

class PostcommentkeywordController extends BackendBaseController
{

    use \repositories\HoutaiRepository;

    /**
     * 列表数据过滤
     * @return Closure
     */
    protected function listAjaxIteration()
    {
        return function (SensitiveWordsModel $item) {
            $item->setHidden([]);
            // 将 word 字段映射为 keyword 以兼容前端
            $item->keyword = $item->word;
            // 添加状态说明文本
            $item->status_str = SensitiveWordsModel::STATUS_TIPS[$item->status] ?? '未知';
            return $item;
        };
    }

    /**
     * 搜索参数处理：将前端的 keyword 映射为数据库的 word
     * @return array
     */
    protected function getSearchDoubleLikeParam()
    {
        $get = $_GET;
        $get['like'] = $get['like'] ?? [];
        $where = [];
        foreach ($get['like'] as $key => $value) {
            if ($value === '__undefined__') {
                continue;
            }
            // 将 keyword 映射为 word
            if ($key === 'keyword') {
                $key = 'word';
            }
            $value = $this->formatSearchVal($key, $value);
            list($key, $value) = $this->formatKey($key, $value);
            if (empty($key)) {
                continue;
            }
            $where[] = [$key, 'like', "%$value%"];
        }
        return $where;
    }

    /**
     * 重写保存方法，在处理前完成字段映射和验证
     */
    public function saveAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->ajaxError('请求错误');
        }
        
        // 将 keyword 映射为 word，并移除 keyword 字段避免干扰
        if (isset($_POST['keyword'])) {
            $_POST['word'] = trim($_POST['keyword']);
            unset($_POST['keyword']);
        }
        
        // 服务端验证：确保敏感词不为空
        if (empty($_POST['word'])) {
            return $this->ajaxError('敏感词不能为空');
        }
        
        // 调用父类方法继续处理
        $post = $this->postArray();
        try {
            if ($model = $this->doSave($post)) {
                return $this->ajaxSuccessMsg('操作成功', 0, call_user_func($this->listAjaxIteration(), $model));
            } else {
                return $this->ajaxError('操作错误');
            }
        } catch (\Throwable $e) {
            trigger_log($e);
            return $this->ajaxError($e->getMessage());
        }
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
        return SensitiveWordsModel::class;
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
    protected function getLogDesc(): string {
        return '';
    }

    /**
     * 设置状态：新增时默认为正常，编辑时使用提交的值
     */
    protected function setstatus($value, $data, $pk)
    {
        // 如果是新增且未传状态，默认为正常
        if (empty($pk) && ($value === '' || $value === null)) {
            return SensitiveWordsModel::STATUS_YES;
        }
        // 返回整数值（0 或 1）
        // 注意：必须使用 intval 确保类型正确，0 是有效值（失效状态）
        return intval($value);
    }

    protected function saveAfterCallback($model, $oldModel = null)
    {
        // 清理敏感词缓存
        cached('sensitive_words')->clearCached();
    }

    /**
     * 删除后回调：清理缓存
     */
    protected function deleteAfterCallback($model, $isDelete)
    {
        // 删除成功后清理缓存
        if ($isDelete) {
            cached('sensitive_words')->clearCached();
        }
    }
}