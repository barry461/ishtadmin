<?php


class CategoriesController extends BackendBaseController
{

    use \repositories\HoutaiRepository;

    /**
     * 列表数据过滤
     *
     * @return Closure
     */
    protected function listAjaxIteration()
    {
        return function (CategoriesModel $item) {
          

            return $item;
        };
    }

    /**
     * 试图渲染
     *
     * @return void
     */
    public function indexAction()
    {
        // 获取排序字段列表
        $customsorts = CustomSortModel::where('status', CustomSortModel::OPTION_STATUS_OPEN)->pluck('name', 'slug')->toArray(); // [id => name]

        $this->assign('customsort_options', $customsorts);
        $this->display();
    }

    public function editAction()
    {
        $id = $_GET['id'] ?? 0;

        $post = CategoriesModel::where('id', $id)->first();

        // 获取排序字段列表
        $customsorts = CustomSortModel::where('status', CustomSortModel::OPTION_STATUS_OPEN)->pluck('name', 'slug')->toArray(); // [id => name]

        $this->assign('customsort_options', $customsorts);
        $this->assign('post', $post);

        $this->display();
    }

    public function edit_saveAction()
    {
        try {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);

            if (!$data || !isset($data['name']) || !isset($data['slug']) || empty($data['_pk'])) {
                return $this->ajaxError('必填字段缺失');
            }

            $this->doSave($data);

            return $this->ajaxSuccessMsg('保存成功');
        } catch (Throwable $e) {
            error_log('分类保存失败 :' . $e->getMessage() . PHP_EOL, 3, APP_PATH . '/storage/logs/log.log');
            return $this->ajaxError($e->getMessage());
        }
    }

    /**
     * 获取本控制器和哪个model绑定
     *
     * @return string
     */
    protected function getModelClass(): string
    {
        return CategoriesModel::class;
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
     * @author xiongba
     */
    protected function getLogDesc(): string
    {
        return '';
    }
}