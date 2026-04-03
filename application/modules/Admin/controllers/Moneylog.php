<?php

/**
 * Class MoneylogController
 * @author xiongba
 * @date 2020-07-09 07:11:13
 */
class MoneylogController extends BackendBaseController
{

    use \repositories\HoutaiRepository;

    /**
     * 列表数据过滤
     * @return Closure
     */
    protected function listAjaxIteration()
    {
        return function (MoneyLogModel $item) {
            $item->setHidden([]);
            if ($item->type == MoneyLogModel::TYPE_SUB){
                $prev = $item->prev_coin;
                $item->prev_coin = $item->next_coin;
                $item->next_coin = $prev;
            }
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

    public function delAction()
    {
    }


    public function revokeAction()
    {
        $id = $_POST['pk'] ?? null;
        $value = $_POST['value'] ?? null;
        if (empty($id) || empty($value)) {
            return $this->ajaxError('参数错误');
        }
        $model = MoneyLogModel::find($id);
        if (empty($model)) {
            return $this->ajaxError('资源不存在');
        }

        try {

            DB::beginTransaction();
            $data = [
                'aff'        => $model->aff,
                'source'     => MoneyLogModel::SOURCE_REVOKE,
                'coinCnt'    => $model->coinCnt,
                'source_aff' => $model->source_aff,
                'desc'       => sprintf("经检测您的哩币日志【%d】为无效数据，检测报告：%s", $model->id, $value),
                'created_at' => TIMESTAMP
            ];
            if ($model->type == MoneyLogModel::TYPE_ADD) {
                $data['type'] = MoneyLogModel::TYPE_SUB;
                $isOk = MemberModel::where('aff', $model->aff)->decrement('money', $model->coinCnt);
            } else {
                $data['type'] = MoneyLogModel::TYPE_ADD;
                $isOk = MemberModel::where('aff', $model->aff)->increment('money', $model->coinCnt);
            }
            if (empty($isOk)) {
                throw new \Exception('操作失败');
            }
            $newModel = MoneyLogModel::create($data);
            if (empty($newModel)) {
                throw new \Exception('操作失败');
            }

            DB::commit();
            $this->ajaxSuccessMsg('操作成功');
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->ajaxError($e->getMessage());
        }


    }


    /**
     * 获取本控制器和哪个model绑定
     * @return string
     */
    protected function getModelClass(): string
    {
        return MoneyLogModel::class;
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
}