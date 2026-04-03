<?php


use Carbon\Carbon;

class AdminmenuController extends BackendBaseController
{

    use \repositories\HoutaiRepository;

    /**
     * 获取对应的model名称
     * @return string
     * @author xiongba
     * @date 2019-11-04 17:20:15
     */
    protected function getModelClass(): string
    {
        return \PermissionModel::class;
    }

    /**
     * 定义数据操作的表主键名称
     * @return string
     * @author xiongba
     * @date 2019-11-04 17:19:41
     */
    protected function getPkName(): string
    {
        return 'id';
    }


    protected function postArray($setPost = null)
    {
        $post = $_POST;
        if (isset($post['p_id'])) {
            if (empty($post['p_id'])) {
                $post['level'] = 1;
            } else {
                $menu = PermissionModel::where(['id' => $post['p_id']])->first();
                if (!empty($menu)) {
                    $post['level'] = $menu->level + 1;
                    //var_dump($menu->toArray());
                } else {
                    $post['level'] = 1;
                }
            }
        }
        // 设置排序默认值
        if (!isset($post['sort']) || $post['sort'] === '') {
            $post['sort'] = 0;
        }
        if (empty($post['_pk'])) {
            $post['created_at'] = Carbon::now()->toDateTimeString();
        }
        //var_dump($post);
        return $post;
    }

    /**
     * 获取菜单树形数据
     * @return bool
     * @author xiongba
     * @date 2020-04-11 15:24:52
     */
    public function treeListAction()
    {
        $data = PermissionModel::getTreeAll([]);
        return $this->ajaxSuccess($data);
    }


    /**
     * @author xiongba
     * @date 2019-12-02 17:07:45
     */
    public function listAjaxAction()
    {
        $data = [];
        $data['data'] = PermissionModel::getAll();
        $data['total'] = count($data['data']);
        $data['code'] = 0;
        return $this->ajaxReturn($data);
    }

    public function indexAction()
    {
        return $this->display();
    }

    /**
     * 定义数据操作日志
     * @return string
     * @author xiongba
     */
    protected function getLogDesc(): string {
        return '';
    }

}