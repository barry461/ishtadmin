<?php

/**
 * Class InfovipController
 * @date 2025-04-09 10:24:24
 */
class InfovipController extends BackendBaseController
{

    use \repositories\HoutaiRepository;

    /**
     * 列表数据过滤
     * @return Closure
     */
    protected function listAjaxIteration()
    {
        return function (InfoVipModel $item) {
            $item->setHidden([]);
            $item->status && $item->status_str = InfoVipModel::STATUS[$item->status];
            $item->girl_cup && $item->cup_str = InfoVipModel::CUP[$item->girl_cup];
            $item->type && $item->type_str = InfoVipModel::TYPE[$item->type];
            $item->category_str = InfoVipModel::CATEGORY[$item->category];
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


    public function acceptAction(): bool
    {
        $id = $this->postArray();
        $id = $id['_pk'];

        $this->acceptInfoVip($id);

        return $this->ajaxSuccessMsg("操作成功!");
    }

    public function batAcceptAction(): bool
    {
        $ids = $this->postArray();
        $ids = array_map( "intval", explode(',',$ids['value']));
        foreach ($ids as $id) {
            $this->acceptInfoVip($id);
        }

        return $this->ajaxSuccessMsg("操作成功");
    }


    private function acceptInfoVip($id){
        try {
            $onlyImage = true;
            InfoVipResourcesModel::where("info_id", $id)
                ->get()
                ->each(function (InfoVipResourcesModel $item) use (&$onlyImage) {


                    if ($item->status !== InfoVipResourcesModel::STATUS_WAITING) {
                        return false;
                    }

                    if ($item->type === InfoVipResourcesModel::TYPE_IMAGE) {
                        $item->status = InfoVipResourcesModel::STATUS_ACCEPTED;
                        $item->save();
                        return true;
                    }

                    $onlyImage = false;

//                $msg = "make slice";
//                error_log($msg . PHP_EOL, 3, APP_PATH . '/storage/logs/slice.log');
                    InfoVipResourcesModel::makeSlice($item);
                });

            if ($onlyImage) {
                $status = InfoVipModel::STATUS_PASS;
            } else {
                $status = InfoVipModel::STATUS_SLICE;
            }

            InfoVipModel::find($id)->update(['status' => $status, "updated_at" => time()]);
        }catch (RuntimeException $e){
            trigger_log("包养信息审核失败:" . $e->getMessage());
        }
    }


    public function rejectAction(): bool
    {
        $id = $this->postArray();
        $id = $id['_pk'];
        $info = InfoVipModel::find($id);
        if($info-> status !== InfoVipModel::STATUS_INIT){
            return $this->ajaxError('状态不正确');
        }

        $info->status = InfoVipModel::STATUS_FAIL;
        $info->save();
        return $this->ajaxSuccessMsg('操作成功');
    }


    public function batRejectAction(): bool
    {
        try {
            $ids = $this->postArray();
            $ids = array_map("intval", explode(',', $ids['value']));


            InfoVipModel::whereIn("id", $ids)
                ->each(function (InfoVipModel $info) {
                    if ($info->status !== InfoVipModel::STATUS_INIT) {
                        throw new RuntimeException("包养信息状态不正确");
                    }

                    $info->status = InfoVipModel::STATUS_FAIL;
                    $info->save();
                });

            return $this->ajaxSuccessMsg('操作成功');
        } catch (RuntimeException $e) {

            trigger_log("包养信息审核err:" . $e->getMessage());
            return $this->ajaxError('操作失败');
        }
    }


    /**
     * 获取本控制器和哪个model绑定
     * @return string
     */
    protected function getModelClass(): string
    {
        return InfoVipModel::class;
    }

    protected function saveAfterCallback($model, $oldModel = null)
    {
        redis()->del(InfoVipModel::REDIS_KEY_DETAIL . $model->id);
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
}