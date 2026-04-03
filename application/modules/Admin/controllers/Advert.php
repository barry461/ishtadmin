<?php


class AdvertController extends BackendBaseController
{

    use \repositories\HoutaiRepository;

    /**
     * 列表数据过滤
     *
     * @return Closure
     */
   protected function listAjaxIteration()
{
    return function (AdvertModel $item) {

        // 转换状态、位置、图片地址
        $item->status_str = AdvertModel::STATUS_OPT[$item->status] ?? '未知状态';
        $item->position_str = AdvertModel::POSITION_OPT[$item->position] ?? '未知位置';
        $item->img_url_full = url_image($item->img_url) ?? '未知图片';

        // 默认分类处理
        $cid = 0;
        $categoryName = '未分类';

        try {
            // 查询分类 ID（可能为空）
            $cid = AdsCategoryModel::query()
                ->where('aid', $item->id)
                ->value('cid') ?? 0;

            // 分类名称匹配（前提 ADVERT_CATEGORY 存在）
            if (!empty(AdvertModel::ADVERT_CATEGORY) && is_array(AdvertModel::ADVERT_CATEGORY)) {
                foreach (AdvertModel::ADVERT_CATEGORY as $cat) {
                    if (isset($cat['id']) && $cat['id'] == $cid) {
                        $categoryName = $cat['name'] ?? '未分类';
                        break;
                    }
                }
            }

        } catch (\Throwable $e) {
            // 可选：记录异常日志
            // logger()->error('广告分类查询失败: ' . $e->getMessage());
            $cid = 0;
            $categoryName = '未分类';
        }

        $item->category = $cid;
        $item->category_name = $categoryName;

        return $item;
    };
}

    public function saveAction()
    {
        try {
            $data = $_POST;

            $id = (int)($data['_pk'] ?? 0);
            $title = trim($data['title'] ?? '');
            $link = trim($data['slug'] ?? '');
            $img_url = trim($data['img_url'] ?? '');
            $position = trim($data['position'] ?? '');
            $status = (int)($data['status'] ?? 0);
            $sort = (int)($data['sort'] ?? 0);
            $category = (int)($data['category'] ?? 0); 
            // var_dump($_POST);die();
            if (!$title) {
                return $this->ajaxError('广告标题不能为空');
            }

            if($position == AdvertModel::POSITION_ARTICLE_BOTTOM_BTN ) {
                test_assert($category, '此广告为应用,请选择应用类型');
            }

            $advertData = [
                'title'    => $title,
                'link'     => $link,
                'img_url'  => $img_url,
                'position' => $position,
                'status'   => $status,
                'sort'     => $sort,
            ];

            if ($id) {
                AdvertModel::query()->where('id', $id)->update($advertData);
            } else {
                $id = AdvertModel::query()->insertGetId($advertData);
            }

            if ($category) {
                AdsCategoryModel::query()->updateOrInsert(
                    ['aid' => $id],
                    ['cid' => $category]
                );
            } else {
                AdsCategoryModel::query()->where('aid', $id)->delete();
            }

            return $this->ajaxSuccess('保存成功');
        } catch (\Exception $e) {
            return $this->ajaxError($e->getMessage());
        }
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
        return AdvertModel::class;
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

    /**
     * 批量更新链接
     *
     * @return bool
     */
    public function batch_replaceAction(): bool
    {
        try {
            $from = trim($_POST['from'] ?? '');
            $to = trim($_POST['to'] ?? '');
            test_assert($from, '原网址不能为空');
            test_assert($to, '新网址不能为空');
            test_assert($from != $to, '两个网址不能相同');

            $record = AdvertModel::where('link', $from)->first();
            test_assert($record, '未找到此原始网址：' . $from);

            $isOk = AdvertModel::where('link', $from)->update(['link' => $to]);
            test_assert($isOk, '系统异常');

            return $this->ajaxSuccess('已成功替换');
        } catch (Throwable $e) {
            return $this->ajaxError($e->getMessage());
        }
    }
}