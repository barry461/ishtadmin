<?php

/**
 * 独立页管理 API 控制器
 * 独立页本质上是 type='page' 的内容,复用 ContentsModel 的方法
 */
class IndependentController extends AdminV2BaseController
{
    /**
     * 列表查询
     * GET /adminv2/independent/list
     *
     * 参数:
     * - status: 状态筛选
     * - keyword: 标题搜索
     * - author: 作者昵称搜索
     * - date_from: 开始日期
     * - date_to: 结束日期
     * - page, limit: 分页参数
     */
    public function listAction()
    {

        [$list, $total] = ContentsModel::getPageList($this->data, $this->limit, $this->offset, ContentsModel::TYPE_PAGE);
        return $this->pageJson($list, $total);
    }

    /**
     * 获取详情
     * GET /adminv2/independent/detail
     *
     * 参数:
     * - cid: 内容ID (必填)
     */
    public function detailAction()
    {
        if (empty($this->data['cid'])) {
            return $this->validationError('cid 参数必填');
        }

        $detail = ContentsModel::getOneDetail((int) $this->data['cid']);

        if (!$detail || $detail->type !== ContentsModel::TYPE_PAGE) {
            return $this->notFound('独立页不存在');
        }

        return $this->showJson($detail);
    }

    /**
     * 创建/更新独立页
     * POST /adminv2/independent/save
     */
    public function saveAction()
    {
        if (empty($this->data['title']) || empty($this->data['text'])) {
            return $this->validationError('标题和内容不能为空');
        }

        $page = $this->savePage();

        return $this->showJson(['cid' => $page->cid], self::STATUS_SUCCESS, '保存成功');
    }

    /**
     * 自动保存
     * POST /adminv2/independent/autoSave
     */
    public function autoSaveAction()
    {
        if (empty($this->data['title']) || empty($this->data['text'])) {
            return $this->validationError('标题和内容不能为空');
        }

        // 自动保存时,如果是新文章且没有cid,则设置为草稿状态
        if (empty($this->data['cid'])) {
            $this->data['status'] = 'draft';
        } else {
            // 编辑文章时,保留原文章的状态
            $existingPost = ContentsModel::find($this->data['cid']);
            if ($existingPost && !empty($existingPost->status)) {
                $this->data['status'] = $existingPost->status;
            } else {
                // 如果找不到原文章或原状态为空,按新建处理,设为待审核
                $this->data['status'] = 'waiting';
            }
        }

        $page = $this->savePage();

        return $this->showJson([
            'cid' => $page->cid,
            'saved_at' => date('Y-m-d H:i:s')
        ], self::STATUS_SUCCESS, '自动保存成功');
    }

    /**
     * 保存页面数据
     */
    private function savePage()
    {
        return transaction(function () {
            $service = new \service\ContentsService();

            // 1. 保存基本信息
            $post = $service->saveBasicInfo(
                array_merge($this->data, ['type' => ContentsModel::TYPE_PAGE])
            );

            // 2. 处理标签
            if (!empty($this->data['tags'])) {
                $service->handleTags($post, $this->data['tags']);
            }

            // 3. 处理分类
            if (!empty($this->data['categories'])) {
                $service->handleCategories($post, $this->data['categories']);
            }

            // 4. 处理视频附件
            $service->handleVideoAttachments($post->text, $post->cid);

            // 5. 处理自定义字段
            $customFields = $this->data['custom_fields'] ?? [];
            if (!empty($customFields)) {
                $service->handleCustomFields($post, $customFields);
            }

            // 6. 批量提交未切片的视频
            $service->handelVideoMakeSlice($post->cid);

            return $post;
        });
    }

    /**
     * 删除独立页
     * POST /adminv2/independent/delete
     *
     * 参数:
     * - ids: 内容ID数组 (必填)
     */
    public function deleteAction()
    {
        if (empty($this->data['ids']) || !is_array($this->data['ids'])) {
            return $this->validationError('ids 参数必填且必须为数组');
        }

        ContentsModel::deleteByCids($this->data['ids']);

        return $this->successMsg('删除成功');
    }

    /**
     * 批量更新状态
     * POST /adminv2/independent/batchStatus
     *
     * 参数:
     * - ids: 内容ID数组 (必填)
     * - status: 状态 (必填)
     */
    public function batchStatusAction()
    {
        if (empty($ids = $this->data['ids'] ?? []) || !is_array($ids)) {
            return $this->validationError('ids 参数必填且必须为数组');
        }

        if (empty($status = $this->data['status'] ?? '')) {
            return $this->validationError('status 参数必填');
        }

        ContentsModel::batchUpdateStatus($ids, $status);

        return $this->successMsg('状态更新成功');
    }

    /**
     * 设置首页显示
     * POST /adminv2/independent/setHome
     *
     * 参数:
     * - ids: 内容ID数组 (必填)
     * - is_home: 0或1 (必填)
     */
    public function setHomeAction()
    {
        if (empty($this->data['ids']) || !is_array($this->data['ids'])) {
            return $this->validationError('ids 参数必填且必须为数组');
        }

        if (!isset($this->data['is_home'])) {
            return $this->validationError('is_home 参数必填');
        }

        ContentsModel::batchUpdateHome($this->data['ids'], (int) $this->data['is_home']);

        return $this->successMsg('设置成功');
    }

    /**
     * 设置置顶
     * POST /adminv2/independent/setTop
     *
     * 参数:
     * - cid: 内容ID (必填)
     * - home_top: 置顶值 (必填)
     */
    public function setTopAction()
    {
        if (empty($this->data['cid'])) {
            return $this->validationError('cid 参数必填');
        }

        if (!isset($this->data['home_top'])) {
            return $this->validationError('home_top 参数必填');
        }

        ContentsModel::updateHomeTop((int) $this->data['cid'], (int) $this->data['home_top']);

        return $this->successMsg('设置成功');
    }

    /**
     * 切换APP显示
     * POST /adminv2/independent/toggleAppHide
     *
     * 参数:
     * - cid: 内容ID (必填)
     */
    public function toggleAppHideAction()
    {
        if (empty($this->data['cid'])) {
            return $this->validationError('cid 参数必填');
        }

        ContentsModel::toggleAppHide((int) $this->data['cid']);

        return $this->successMsg('切换成功');
    }

    /**
     * 切换WEB显示
     * POST /adminv2/independent/toggleWebShow
     *
     * 参数:
     * - cid: 内容ID (必填)
     */
    public function toggleWebShowAction()
    {
        if (empty($this->data['cid'])) {
            return $this->validationError('cid 参数必填');
        }

        ContentsModel::toggleWebShow((int) $this->data['cid']);

        return $this->successMsg('切换成功');
    }

    /**
     * 获取分类列表
     * GET /adminv2/independent/categories
     */
    public function categoriesAction()
    {
        $categories = CategoriesModel::orderBy('sort_order')->get(['id', 'name', 'slug']);

        return $this->showJson($categories);
    }

    /**
     * 获取作者列表
     * GET /adminv2/independent/authors
     */
    public function authorsAction()
    {
        $authors = UsersModel::whereExists(function ($query) {
            $query->select(\DB::raw(1))
                ->from('contents')
                ->whereColumn('contents.authorId', 'users.uid')
                ->where('contents.type', ContentsModel::TYPE_PAGE);
        })
            ->orderByDesc('uid')
            ->get(['uid', 'screenName']);

        return $this->showJson($authors);
    }
}
