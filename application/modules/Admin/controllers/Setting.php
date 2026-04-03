<?php

/**
 * Class SettingController
 * @author xiongba
 * @date 2020-02-26 15:19:34
 */
class SettingController extends BackendBaseController
{

    use \repositories\HoutaiRepository;

    public function init()
    {
        parent::init();
    }

    /**
     * 列表数据过滤
     * @return Closure
     * @author xiongba
     * @date 2019-12-02 17:08:03
     */
    protected function listAjaxIteration()
    {
        return function ($item) {
            return my_addslashes($item->toArray());
        };
    }

    public function refreshAction(): bool
    {
        SettingModel::pushCached();
        yac()->expire('system:setting', 1);
        return $this->ajaxSuccessMsg('操作成功', 0, \tools\RedisService::instance()->hGetAll('system:setting'));
    }

    // 缓存清理
    public function clearAction(): bool
    {
        $name = trim($_POST['name'] ?? '');
        if ($name != 'group_list'){
            CacheKeysModel::where('name' , $name)->chunkById(1000 , function ($items){
                collect($items)->each(function (CacheKeysModel $item){
                    redis()->expire($item->key , 5);
                });
                CacheKeysModel::whereIn('id' , collect($items)->pluck('id'))->delete();
            });
        }
       return $this->ajaxSuccessMsg('释放成功');
    }

    /**
     * 试图渲染
     */
    public function indexAction()
    {
        $this->assign('key_list' , CacheKeysModel::where('name' , 'group_list')->pluck('key'));
        $this->display();
    }


    /**
     * 获取对应的model名称
     * @return string
     */
    protected function getModelClass(): string
    {
        return SettingModel::class;
    }

    /**
     * 定义数据操作的表主键名称
     * @return string
     */
    protected function getPkName(): string
    {
        return 'id';
    }


    public function saveAfterCallback($model , $oldModel = null)
    {
        SettingModel::pushCached();
    }


    public function _deleteActionAfter()
    {
        SettingModel::pushCached();
    }

    protected function getLogDesc(): string {
        return '';
    }


}