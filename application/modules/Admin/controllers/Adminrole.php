<?php


class AdminroleController extends BackendBaseController
{

    use \repositories\HoutaiRepository;

    protected function listAjaxIteration()
    {
        return function ($item) {
            /** @var RoleModel $item */
            $item->rule = $item->role_action_ids;

            return $item;
        };
    }


    /**
     * 获取对应的model名称
     *
     * @return string
     * @author xiongba
     * @date 2019-11-04 17:20:15
     */
    protected function getModelClass(): string
    {
        return \RoleModel::class;
    }


    protected function postArray($setPost = null)
    {
        $post = $_POST;
        if (!empty($post['rule'])) {
            $post['role_action_ids'] = join(',', $post['rule']);
        }

        return $post;
    }


    /**
     * 定义数据操作的表主键名称
     *
     * @return string
     * @author xiongba
     * @date 2019-11-04 17:19:41
     */
    protected function getPkName(): string
    {
        return 'role_id';
    }


    public function indexAction()
    {
        $this->display();
    }


    public function getLogDesc(): string
    {
        return '';
    }
}