<?php

use service\ContentsService;

/**
 * 内容管理 API 控制器 (RESTful - 优雅参数设计)
 */
class ContentsController extends AdminV2BaseController
{
    /**
     * 内容列表
     * GET /adminv2/contents/list
     *
     * 参数:
     * - status: 状态筛选 (publish/draft/hidden等)
     * - is_home: 首页显示 (0/1)
     * - category_id: 分类ID (或 'no_category')
     * - hot_search: 热搜状态 (0/1)
     * - keyword: 标题关键词搜索
     * - author: 作者名搜索
     * - date_from: 开始日期 (YYYY-MM-DD)
     * - date_to: 结束日期 (YYYY-MM-DD)
     * - order_by: 排序字段 (默认 cid)
     * - order_dir: 排序方向 (asc/desc, 默认 desc)
     * - page: 页码
     * - limit: 每页数量
     */
    public function listAction()
    {
        // var_dump($this->data);die();
        [$list, $total] = ContentsModel::getPageList($this->data, $this->limit, $this->offset);
        return $this->pageJson($list, $total);
    }

    /**
     * 内容详情
     * GET /adminv2/contents/detail
     *
     * 参数:
     * - cid: 文章ID (必填)
     */
    public function detailAction()
    {
        $cid = (int)($this->data['cid'] ?? 0);
        if (!$cid) {
            return $this->validationError('缺少文章ID');
        }

        $post = ContentsModel::getOneDetail($cid);
        if (!$post) {
            return $this->notFound('文章不存在');
        }

        // 确保返回的数据包含 author_id 字段（兼容前端）
        $postData = $post->toArray();
        if (isset($postData['authorId']) && !isset($postData['author_id'])) {
            $postData['author_id'] = $postData['authorId'];
        }

        return $this->showJson($postData);
    }

    /**
     * 根据ID获取文章详情（兼容前端调用）
     * POST /adminv2/contents/getById
     *
     * 参数:
     * - id: 文章ID (必填)
     */
    public function getByIdAction()
    {
        $id = (int)($this->data['id'] ?? $this->data['cid'] ?? 0);
        if (!$id) {
            return $this->validationError('缺少文章ID');
        }

        $post = ContentsModel::getOneDetail($id);
        if (!$post) {
            return $this->notFound('文章不存在');
        }

        // 确保返回的数据包含 author_id 字段（兼容前端）
        $postData = $post->toArray();
        if (isset($postData['authorId']) && !isset($postData['author_id'])) {
            $postData['author_id'] = $postData['authorId'];
        }

        return $this->showJson($postData);
    }

    /**
     * 保存内容 (创建/更新)
     * POST /adminv2/contents/save
     *
     * 参数:
     * - cid: 文章ID (更新时必填)
     * - title: 标题
     * - content: 内容
     * - status: 状态
     * - author_id: 作者ID
     * - category_ids: 分类ID数组
     * - tags: 标签字符串 (逗号分隔)
     * - custom_fields: 自定义字段对象 {banner, hot_search, ads_field等}
     */
    public function saveAction()
    {
        if (empty($this->data['title'])) {
            return $this->validationError('标题不能为空');
        }

        $content = transaction(function () {
            return $this->processContentSave();
        });

        return $content
            ? $this->showJson(['cid' => $content->cid], self::STATUS_SUCCESS, '保存成功')
            : $this->errorJson('保存失败');
    }

    /**
     * 处理内容保存流程
     */
    private function processContentSave()
    {
        $service = new ContentsService();
        $content = $service->saveBasicInfo($this->data);

        $this->applyContentRelations($service, $content);
        $this->processContentAttachments($service, $content);

        // 仅草稿写入独立表供「最新草稿」快捷入口；发布状态则移除，不再参与
        $uid = (int) $this->user->uid;
        draft_log('[processContentSave] 请求 status=' . ($this->data['status'] ?? 'null') . ', 保存后 content->status=' . ($content->status ?? 'null') . ', uid=' . $uid . ', cid=' . ($content->cid ?? 'null') . ', title=' . ($content->title ?? ''));
        try {
            if ($content->status === ContentsModel::STATUS_DRAFT) {
                draft_log('[processContentSave] 进入 setLatestDraft 分支');
                AdminLatestDraftModel::setLatestDraft($uid, $content->cid, (string) ($content->title ?? ''));
            } else {
                draft_log('[processContentSave] 进入 removeDraft 分支 (非草稿)');
                AdminLatestDraftModel::removeDraft($uid, $content->cid);
            }
        } catch (\Throwable $e) {
            draft_log('[processContentSave] 异常: ' . $e->getMessage() . ' | ' . $e->getFile() . ':' . $e->getLine());
            trigger_log('AdminLatestDraft setLatestDraft/removeDraft 失败: ' . $e->getMessage());
        }

        return $content;
    }

    /**
     * 应用内容关联关系
     */
    private function applyContentRelations(ContentsService $service, $content): void
    {
        $relations = [
            'custom_fields' => 'handleCustomFields',
            'category_ids' => 'handleCategories',
            'tags' => 'handleTags',
        ];

        foreach ($relations as $key => $method) {
            if (isset($this->data[$key]) && (!empty($this->data[$key]) || $key === 'tags')) {
                $service->$method($content, $this->data[$key]);
            }
        }
    }

    /**
     * 处理内容附件
     */
    private function processContentAttachments(ContentsService $service, $content): void
    {
        $service->handleVideoAttachments($content->text, $content->cid);
        $service->handelVideoMakeSlice($content->cid);
    }

    /**
     * 自动保存 (草稿)
     * POST /adminv2/contents/autoSave
     *
     * 参数: 同 save,但会强制设置 status 为 draft
     */
    public function autoSaveAction()
    {
        $content = transaction(function () {
            return (new ContentsService())->saveBasicInfo(
                array_merge($this->data, ['status' => ContentsModel::STATUS_DRAFT])
            );
        });

        if ($content) {
            $uid = (int) $this->user->uid;
            draft_log('[autoSaveAction] content 存在, uid=' . $uid . ', cid=' . $content->cid . ', title=' . ($content->title ?? ''));
            try {
                AdminLatestDraftModel::setLatestDraft($uid, $content->cid, (string) ($content->title ?? ''));
                draft_log('[autoSaveAction] setLatestDraft 调用完成');
            } catch (\Throwable $e) {
                draft_log('[autoSaveAction] 异常: ' . $e->getMessage() . ' | ' . $e->getFile() . ':' . $e->getLine());
                trigger_log('AdminLatestDraft setLatestDraft 失败: ' . $e->getMessage());
            }
        } else {
            draft_log('[autoSaveAction] content 为空，未调用 setLatestDraft');
        }

        return $content
            ? $this->showJson(['cid' => $content->cid], self::STATUS_SUCCESS, '自动保存成功')
            : $this->errorJson('自动保存失败');
    }

    /**
     * 当前登录管理员的最新草稿
     * GET /adminv2/contents/latestDraft
     *
     * 查 admin_latest_draft（与 contents 同前缀），若该文章已发布则清除脏数据并返回空（读时自愈）
     */
    public function latestDraftAction()
    {
        $uid = (int) $this->user->uid;

        // 0. 优先读取「最近从文章列表进入编辑的文章」（纯 Redis，不落库）
        try {
            $lastEdit = AdminLatestDraftModel::getLastEditArticleFromRedis($uid);
            if (is_array($lastEdit) && !empty($lastEdit['cid'])) {
                $cid = (int) $lastEdit['cid'];
                // 确认文章仍然存在
                $exists = ContentsModel::where('cid', $cid)->value('cid');
                if ($exists) {
                    return $this->showJson([
                        'cid'   => $cid,
                        'title' => $lastEdit['title'] ?? null,
                    ]);
                }
                // 文章不存在则清理这条 Redis 记录
                AdminLatestDraftModel::clearLastEditArticle($uid);
            }
        } catch (\Throwable $e) {
            // 缓存异常时静默降级，继续后续逻辑
        }

        // 1. 优先从 Redis 读取，避免频繁访问数据库
        $row = null;
        try {
            $cache = AdminLatestDraftModel::getLatestFromRedis($uid);
            if (is_array($cache) && !empty($cache['cid'])) {
                $row = (object) [
                    'cid'   => (int) $cache['cid'],
                    'title' => $cache['title'] ?? null,
                ];
            }
        } catch (\Throwable $e) {
            // 忽略缓存异常，降级走数据库
        }

        // 2. 缓存未命中时再查数据库，并顺手刷新 Redis
        if (!$row) {
            $row = AdminLatestDraftModel::query()
                ->where('admin_id', $uid)
                ->orderByDesc('updated_at')
                ->first(['cid', 'title']);
            if (!$row) {
                return $this->showJson(['cid' => null, 'title' => null]);
            }

            try {
                AdminLatestDraftModel::setLatestDraft($uid, (int) $row->cid, (string) ($row->title ?? ''));
            } catch (\Throwable $e) {
                // 忽略缓存写入异常
            }
        }

        $cid = (int) $row->cid;
        // 读时自愈：若该文章已非草稿（已发布等），从 typecho_admin_latest_draft 清除脏数据并返回空
        $articleStatus = ContentsModel::where('cid', $cid)->value('status');
        if ($articleStatus === null || $articleStatus !== ContentsModel::STATUS_DRAFT) {
            AdminLatestDraftModel::removeDraft($uid, $cid);
            return $this->showJson(['cid' => null, 'title' => null]);
        }
        return $this->showJson(['cid' => $cid, 'title' => $row->title ?? null]);
    }

    /**
     * 记住「最近从文章列表进入编辑的文章」
     * POST /adminv2/contents/rememberLastEdit
     *
     * 说明：
     * - 仅写入 Redis，不落库，用于新后台“继续编辑xxx文章”快捷入口
     * - 文章真实状态不改变（仍然按原有 publish/draft 等逻辑）
     */
    public function rememberLastEditAction()
    {
        $uid = (int) $this->user->uid;
        $cid = (int) ($this->data['cid'] ?? 0);
        $title = (string) ($this->data['title'] ?? '');

        if (!$cid) {
            return $this->validationError('缺少文章ID');
        }

        // 若前端未传标题，则从数据库补充一次标题（仅一次性查询）
        if ($title === '') {
            $titleFromDb = ContentsModel::where('cid', $cid)->value('title');
            if ($titleFromDb !== null) {
                $title = (string) $titleFromDb;
            }
        }

        try {
            AdminLatestDraftModel::rememberLastEditArticle($uid, $cid, $title);
        } catch (\Throwable $e) {
            // 纯辅助功能，出现异常时不影响主流程
        }

        return $this->showJson(
            [
                'cid'   => $cid,
                'title' => $title,
            ],
            self::STATUS_SUCCESS,
            'OK'
        );
    }

    /**
     * 删除文章 (支持批量)
     * POST /adminv2/contents/delete
     *
     * 参数:
     * - ids: 文章ID数组 (必填)
     */
    public function deleteAction()
    {
        if (empty($ids = (array)($this->data['ids'] ?? []))) {
            return $this->validationError('缺少文章ID');
        }

        $result = transaction(function () use ($ids) {
            return ContentsModel::deleteByCids($ids);
        });

        if ($result !== false) {
            AdminLatestDraftModel::removeByCids($ids);
            return $this->successMsg('删除成功');
        }
        return $this->errorJson('删除失败');
    }

    /**
     * 创建前台预览草稿
     * POST /adminv2/contents/preview/createDraft
     *
     * 说明：
     * - 仅用于后台编辑页面在「尚未保存（无 cid）」时，生成一个短期有效的前台预览链接
     * - 不写入正式 contents 表，只把渲染后的 HTML 及少量元信息放入 Redis，TTL 默认 1 小时
     *
     * 参数：
     * - text: Markdown 内容（必填）
     * - content: 兼容字段，等同于 text
     * - title: 文章标题（可选）
     */
    public function previewCreateDraftAction()
    {
        $text = (string)($this->data['text'] ?? $this->data['content'] ?? '');
        $title = (string)($this->data['title'] ?? '');

        if ($text === '') {
            return $this->validationError('预览内容不能为空');
        }

        // 与现有 previewAction 保持一致的 Markdown 渲染效果
        $html = \tools\LibMarkdown::loadWebMarkdown($text, false, $title);

        // 将懒加载图片转换为直接可见的图片，便于预览
        $html = preg_replace_callback(
            '#<img([^>]*?)src=[\'"]([^"\']*)[\'"]([^>]*?)>#i',
            function ($match) {
                $fullTag = $match[0];

                if (preg_match('/data-src=[\'"]([^"\']+)[\'"]/i', $fullTag, $dm)) {
                    $realSrc = $dm[1];

                    $fullTag = preg_replace(
                        '/src=[\'"][^"\']*[\'"]/i',
                        'src="' . $realSrc . '"',
                        $fullTag
                    );

                    $fullTag = preg_replace('/\sdata-src=[\'"][^"\']*[\'"]/i', '', $fullTag);

                    $fullTag = preg_replace_callback(
                        '/\sclass=[\'"]([^"\']*)[\'"]/i',
                        function ($cm) {
                            $classes = preg_split('/\s+/', $cm[1]);
                            $classes = array_filter($classes, function ($c) {
                                return strtolower($c) !== 'lazy' && $c !== '';
                            });
                            if (empty($classes)) {
                                return '';
                            }
                            return ' class="' . implode(' ', $classes) . '"';
                        },
                        $fullTag
                    );
                }

                return $fullTag;
            },
            $html
        );

        // 生成预览 token 并写入 Redis（短期有效）
        $token = bin2hex(random_bytes(16));
        $adminId = (int)($this->user->uid ?? 0);
        $payload = [
            'admin_id' => $adminId,
            'title' => $title,
            'html' => $html,
            'created_at' => time(),
        ];

        // 默认 1 小时过期
        $ttl = 3600;
        $key = sprintf('admin:preview:article:%s', $token);
        redis()->setex($key, $ttl, json_encode($payload, JSON_UNESCAPED_UNICODE));

        $siteUrl = rtrim(options('siteUrl'), '/');
        $previewUrl = $siteUrl . '/preview/article?token=' . $token;

        return $this->showJson(
            [
                'token' => $token,
                'preview_url' => $previewUrl,
            ],
            self::STATUS_SUCCESS,
            '预览链接已创建'
        );
    }

    /**
     * 更新发布状态
     * POST /adminv2/contents/updateStatus
     *
     * 参数:
     * - ids: 文章ID数组 (必填)
     * - status: 目标状态 (必填)
     */
    public function updateStatusAction()
    {
        $ids = (array)($this->data['ids'] ?? []);
        $status = $this->data['status'] ?? '';

        if (empty($ids) || !$status) {
            return $this->validationError('参数错误');
        }

        $result = transaction(function () use ($ids, $status) {
            return ContentsModel::batchUpdateStatus($ids, $status);
        });

        if ($result !== false) {
            if ($status !== ContentsModel::STATUS_DRAFT) {
                AdminLatestDraftModel::removeByCids($ids);
            }
            return $this->successMsg('更新成功');
        }
        return $this->errorJson('更新失败');
    }

    /**
     * 批量设置首页显示
     * POST /adminv2/contents/setHome
     *
     * 参数:
     * - ids: 文章ID数组 (必填)
     * - is_home: 是否首页显示 (0/1, 必填)
     */
    public function setHomeAction()
    {
        $ids = (array)($this->data['ids'] ?? []);
        $isHome = isset($this->data['is_home']) ? (int)$this->data['is_home'] : null;

        if (empty($ids) || $isHome === null) {
            return $this->validationError('参数错误');
        }

        $res = transaction(function () use ($ids, $isHome) {
            return ContentsModel::batchUpdateHome($ids, $isHome);
        });

        if ($res !== false) {
            return $this->successMsg('设置成功');
        }
        return $this->errorJson('设置失败');
    }

    /**
     * 设置置顶权重
     * POST /adminv2/contents/setTop
     *
     * 参数:
     * - cid: 文章ID (必填)
     * - top: 置顶权重 (必填)
     */
    public function setTopAction()
    {
        $cid = (int)($this->data['cid'] ?? 0);
        $top = isset($this->data['top']) ? (int)$this->data['top'] : null;

        if (!$cid || $top === null) {
            return $this->validationError('参数错误');
        }

        $res = transaction(function () use ($cid, $top) {
            return ContentsModel::updateHomeTop($cid, $top);
        });

        if ($res) {
            return $this->successMsg('设置成功');
        }
        return $this->errorJson('设置失败');
    }

    /**
     * 切换热搜状态
     * POST /adminv2/contents/toggleHotSearch
     *
     * 参数:
     * - cid: 文章ID (必填)
     * - hotSearch: 显式热搜值 (0/1，可选；不传时按旧逻辑切换)
     */
    public function toggleHotSearchAction()
    {
        $cid = (int)($this->data['cid'] ?? 0);
        if (!$cid) {
            return $this->validationError('缺少文章ID');
        }

        $hasExplicitValue = array_key_exists('hotSearch', $this->data);
        $hotSearch = isset($this->data['hotSearch']) ? (int) $this->data['hotSearch'] : null;

        $res = transaction(function () use ($cid, $hasExplicitValue, $hotSearch) {
            if ($hasExplicitValue) {
                return FieldsModel::setHotSearch($cid, $hotSearch);
            }
            return FieldsModel::toggleHotSearch($cid);
        });

        if ($res) {
            return $this->successMsg('切换成功');
        }
        return $this->errorJson('切换失败');
    }

    /**
     * 设置内容类型 (短剧/大事件)
     * POST /adminv2/contents/setType
     *
     * 参数:
     * - cid: 文章ID (必填)
     * - type: 类型 (skits/big_went, 必填)
     * - sid: 关联ID (必填)
     */
    public function setTypeAction()
    {
        $cid = (int)($this->data['cid'] ?? 0);
        $type = $this->data['type'] ?? '';
        $sid = (int)($this->data['sid'] ?? 0);

        if (!$cid || !$type) {
            return $this->validationError('参数错误');
        }

        $res = transaction(function () use ($cid, $type, $sid) {
            return ContentsModel::setArticleType($cid, $type, $sid);
        });

        if ($res) {
            return $this->successMsg('设置成功');
        }
        return $this->errorJson('设置失败');
    }

    /**
     * 切换 APP 隐藏状态
     * POST /adminv2/contents/toggleAppHide
     *
     * 参数:
     * - cid: 文章ID (必填)
     */
    public function toggleAppHideAction()
    {
        $cid = (int)($this->data['cid'] ?? 0);
        if (!$cid) {
            return $this->validationError('缺少文章ID');
        }

        $res = transaction(function () use ($cid) {
            return ContentsModel::toggleAppHide($cid);
        });

        if ($res) {
            return $this->successMsg('切换成功');
        }
        return $this->errorJson('切换失败');
    }

    /**
     * 切换 Web 显示状态
     * POST /adminv2/contents/toggleWebShow
     *
     * 参数:
     * - cid: 文章ID (必填)
     */
    public function toggleWebShowAction()
    {
        $cid = (int)($this->data['cid'] ?? 0);
        if (!$cid) {
            return $this->validationError('缺少文章ID');
        }

        $res = transaction(function () use ($cid) {
            return ContentsModel::toggleWebShow($cid);
        });

        if ($res) {
            return $this->successMsg('切换成功');
        }
        return $this->errorJson('切换失败');
    }

    /**
     * 批量编辑
     * POST /adminv2/contents/batchEdit
     *
     * 参数:
     * - cid: 文章ID (必填)
     * - title: 新标题 (可选)
     * - created_at: 创建时间 (可选)
     * - category_ids: 分类ID数组 (可选)
     * - tags: 标签字符串 (可选)
     * - banner: Banner图 (可选)
     * - hot_search: 热搜状态 (可选)
     */
    public function batchEditAction()
    {
        $cid = (int)($this->data['cid'] ?? 0);
        if (!$cid) {
            return $this->validationError('缺少文章ID');
        }

        // 转换参数格式
        $updateData = [];
        if (isset($this->data['title'])) {
            $updateData['title'] = $this->data['title'];
        }
        if (isset($this->data['created_at'])) {
            $updateData['created'] = $this->data['created_at'];
        }
        if (isset($this->data['category_ids'])) {
            $updateData['category_ids'] = $this->data['category_ids'];
        }
        if (isset($this->data['tags'])) {
            $updateData['tags'] = $this->data['tags'];
        }
        if (isset($this->data['banner'])) {
            $updateData['banner'] = $this->data['banner'];
        }
        if (isset($this->data['hot_search'])) {
            $updateData['hotSearch'] = $this->data['hot_search'];
        }

        $res = transaction(function () use ($cid, $updateData) {
            return ContentsModel::updateSpecial($cid, $updateData);
        });

        if ($res) {
            return $this->successMsg('编辑成功');
        }
        return $this->errorJson('编辑失败');
    }

    /**
     * 获取分类列表
     * GET /adminv2/contents/categories
     */
    public function categoriesAction()
    {
        $list = CategoriesModel::select('id', 'name', 'parent_id as parent')
            ->orderBy('sort_order', 'asc')
            ->get();
        $this->showJson($list);
        return false;
    }

    /**
     * 获取作者列表
     * GET /adminv2/contents/authors
     *
     * 返回所有作者列表（简化版本，不统计发帖数和评论数，提升性能）
     * 
     * 参数:
     * - keyword: 搜索关键词（可选，搜索screenName）
     * - limit: 返回数量限制（默认500）
     */
    public function authorsAction()
    {
        $keyword = trim($this->data['keyword'] ?? '');
        $limit = (int)($this->data['limit'] ?? 500);
        $limit = min(max($limit, 1), 1000); // 限制在1-1000之间

        // 使用Redis缓存提升性能，避免使用cached()触发Eloquent timestamps
        $cacheKey = 'admin:authors:list:' . md5($keyword . ':' . $limit);
        $cachedData = redis()->get($cacheKey);
        
        if ($cachedData !== false) {
            $list = json_decode($cachedData, true);
            if (is_array($list)) {
                $this->showJson($list);
                return false;
            }
        }

        // 缓存未命中，查询数据库
        $query = UsersModel::query()
            ->select('uid', 'screenName as name')
            ->orderBy('uid', 'asc');

        // 如果有关键词，进行模糊搜索
        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->where('screenName', 'like', '%' . $keyword . '%')
                  ->orWhere('name', 'like', '%' . $keyword . '%');
            });
        }

        // 限制返回数量
        if ($limit > 0) {
            $query->limit($limit);
        }

        $results = $query->get();

        $list = [];
        foreach ($results as $author) {
            $list[] = [
                'id' => (int)$author->uid,
                'name' => $author->name ?: '未知作者',
            ];
        }

        // 缓存结果到Redis，5分钟过期
        redis()->setex($cacheKey, 300, json_encode($list, JSON_UNESCAPED_UNICODE));

        $this->showJson($list);
        return false;
    }

    /**
     * 清理缓存
     * POST /adminv2/contents/clearCache
     *
     * 参数:
     * - cid: 文章ID (必填)
     */
    public function clearCacheAction()
    {
        $cid = (int)($this->data['cid'] ?? 0);
        if (!$cid) {
            return $this->validationError('缺少文章ID');
        }

        cached($cid)->clearCached();

        return $this->successMsg('缓存已清理');
    }

    /**
     * 视频附件列表
     * GET /adminv2/contents/videos
     *
     * 参数:
     * - cid: 文章ID (必填)
     * - page: 页码
     * - limit: 每页数量
     */
    public function videosAction()
    {
        $cid = (int)($this->data['cid'] ?? 0);
        if (!$cid) {
            return $this->validationError('缺少文章ID');
        }

        $service = new ContentsService();
        $list = $service->atachmentList($cid, $this->page, $this->limit);
        return $this->showJson($list);
    }

    /**
     * 图片附件列表
     * GET /adminv2/contents/images
     *
     * 参数:
     * - cid: 文章ID (必填)
     * - page: 页码
     * - limit: 每页数量
     */
    public function imagesAction()
    {
        $cid = (int)($this->data['cid'] ?? 0);

        $service = new ContentsService();
        $list = $service->atachmentImagesList($cid, $this->page, $this->limit);
        return $this->showJson($list);
    }

    /**
     * 最近发布的文章
     * GET /adminv2/contents/recent
     *
     * 返回最近发布的10条文章信息(日期、标题、文章URL)
     */
    public function recentAction()
    {
        $list = ContentsModel::getRecentPublished(10);
        return $this->showJson($list);
    }

    /**
     * 获取自定义排序字段选项
     * GET /adminv2/contents/sortFields
     */
    public function sortFieldsAction()
    {
        $list = CustomSortModel::where('status', CustomSortModel::OPTION_STATUS_OPEN)
            ->select('id', 'name', 'slug')
            ->orderBy('id', 'asc')
            ->get();

        return $this->showJson($list);
    }

    /**
     * 更新文章自定义排序字段值
     * POST /adminv2/contents/updateSortField
     */
    public function updateSortFieldAction()
    {
        $cid = (int)($this->data['cid'] ?? 0);
        $field = $this->data['field'] ?? '';
        $value = $this->data['value'] ?? 0;

        if (!$cid) {
            return $this->validationError('缺少文章ID');
        }
        if (!$field) {
            return $this->validationError('缺少字段名');
        }

        $content = ContentsModel::find($cid);
        if (!$content) {
            return $this->notFound('文章不存在');
        }

        // 验证字段是否为有效的自定义排序字段
        $sortFields = $content->getMergeFields();
        if (!isset($sortFields[$field])) {
            return $this->validationError('无效的排序字段');
        }

        $content->{$field} = (int)$value;
        $content->save();

        return $this->showJson([
            'cid' => $cid,
            'field' => $field,
            'value' => (int)$value,
        ], self::STATUS_SUCCESS, '更新成功');
    }

    /**
     * 批量更新文章自定义排序字段值
     * POST /adminv2/contents/batchUpdateSortField
     */
    public function batchUpdateSortFieldAction()
    {
        $items = $this->data['items'] ?? [];

        if (empty($items) || !is_array($items)) {
            return $this->validationError('items 参数必填且必须为数组');
        }

        $results = [];
        $errors = [];

        transaction(function () use ($items, &$results, &$errors) {
            $contentModel = new ContentsModel();
            $sortFields = $contentModel->getMergeFields();

            foreach ($items as $item) {
                $cid = (int)($item['cid'] ?? 0);
                $field = $item['field'] ?? '';
                $value = $item['value'] ?? 0;

                if (!$cid || !$field) {
                    $errors[] = "无效参数: cid={$cid}, field={$field}";
                    continue;
                }

                if (!isset($sortFields[$field])) {
                    $errors[] = "无效排序字段: {$field}";
                    continue;
                }

                $content = ContentsModel::find($cid);
                if (!$content) {
                    $errors[] = "文章不存在: cid={$cid}";
                    continue;
                }

                $content->{$field} = (int)$value;
                $content->save();
                $results[] = ['cid' => $cid, 'field' => $field, 'value' => (int)$value];
            }
        });

        return $this->showJson([
            'success' => $results,
            'errors' => $errors,
        ], self::STATUS_SUCCESS, '批量更新完成');
    }

    /**
     * 批量添加评论
     * POST /adminv2/contents/batchAddComments
     *
     * 参数:
     * - user_nicknames: 用户昵称 (逗号分隔)
     * - article_ids: 文章ID (逗号分隔)
     * - comment_contents: 评论内容 (换行分隔)
     * - time_from: 开始时间小时偏移 (int)
     * - time_to: 结束时间小时偏移 (int)
     * - is_top: 是否置顶 (0/1)
     */
    public function batchAddCommentsAction()
    {
        $userNicknames = trim($this->data['user_nicknames'] ?? '');
        $articleIds = trim($this->data['article_ids'] ?? '');
        $commentContents = trim($this->data['comment_contents'] ?? '');
        $timeFrom = (int)($this->data['time_from'] ?? 0);
        $timeTo = (int)($this->data['time_to'] ?? 0);
        $isTop = (int)($this->data['is_top'] ?? 0);

        if (empty($userNicknames) || empty($articleIds) || empty($commentContents)) {
            return $this->validationError('请填写完整信息');
        }

        if ($timeFrom < 0 || $timeTo < 0 || $timeFrom > $timeTo) {
            return $this->validationError('时间范围设置不正确');
        }

        // 解析用户昵称（多个用逗号分隔）
        $nicknames = array_filter(array_map('trim', explode(',', $userNicknames)));
        if (empty($nicknames)) {
            return $this->validationError('请至少输入一个用户昵称');
        }

        // 解析文章ID（多个用逗号分隔）
        $cids = array_filter(array_map('trim', explode(',', $articleIds)));
        if (empty($cids)) {
            return $this->validationError('请至少输入一个文章ID');
        }

        // 解析评论内容（一行一条）
        $comments = array_filter(array_map('trim', explode("\n", $commentContents)));
        if (empty($comments)) {
            return $this->validationError('请至少输入一条评论内容');
        }

        $successCount = 0;
        $failCount = 0;
        $errorMessages = [];

        transaction(function () use (&$successCount, &$failCount, &$errorMessages, $nicknames, $cids, $comments, $timeFrom, $timeTo, $isTop) {
            foreach ($cids as $cid) {
                $cid = (int)$cid;
                if ($cid <= 0) {
                    $failCount++;
                    $errorMessages[] = "文章ID {$cid} 无效";
                    continue;
                }

                // 查找文章
                $content = ContentsModel::find($cid);
                if (!$content) {
                    $failCount++;
                    $errorMessages[] = "文章ID {$cid} 不存在";
                    continue;
                }

                // 检查文章类型是否支持评论
                if (!in_array($content->type, [ContentsModel::TYPE_POST, ContentsModel::TYPE_SKITS])) {
                    $failCount++;
                    $errorMessages[] = "文章ID {$cid} 的类型不支持评论";
                    continue;
                }

                // 获取文章发布时间
                $articleCreated = is_numeric($content->created) ? $content->created : strtotime($content->created);
                if (!$articleCreated) {
                    $failCount++;
                    $errorMessages[] = "文章ID {$cid} 发布时间无效";
                    continue;
                }

                // 为每篇文章生成评论
                foreach ($comments as $commentText) {
                    if (empty($commentText)) {
                        continue;
                    }

                    // 随机选择一个用户昵称
                    $nickname = $nicknames[array_rand($nicknames)];

                    // 在时间范围内随机生成评论时间
                    $timeOffset = rand($timeFrom * 3600, $timeTo * 3600);
                    $commentCreated = $articleCreated + $timeOffset;

                    // 创建评论数据
                    $commentData = [
                        'cid' => $cid,
                        'created' => $commentCreated,
                        'author' => $nickname,
                        'reply_author' => '',
                        'reply_aff' => 0,
                        'thumb' => '',
                        'app_aff' => 0,
                        'authorId' => 0,
                        'ownerId' => $content->authorId,
                        'mail' => '',
                        'url' => '',
                        'ip' => client_ip(),
                        'agent' => 'web',
                        'text' => htmlspecialchars($commentText, ENT_QUOTES, 'UTF-8'),
                        'type' => CommentsModel::TYPE_COMMENT,
                        'status' => CommentsModel::STATUS_APPROVED, // 默认通过
                        'is_top' => $isTop,
                        'parent' => 0,
                        'sec_parent' => 0,
                        'admin_id' => $this->getUser()->uid ?? 0
                    ];

                    $comment = CommentsModel::create($commentData);
                    if ($comment) {
                        $successCount++;
                        // 更新文章评论数
                        ContentsModel::find($cid)->increment('commentsNum');
                    } else {
                        $failCount++;
                        $errorMessages[] = "文章ID {$cid} 添加评论失败";
                    }
                }
            }
        });

        return $this->showJson([
            'success_count' => $successCount,
            'fail_count' => $failCount,
            'messages' => $errorMessages
        ], self::STATUS_SUCCESS, '批量操作完成');
    }


    /**
     * 批量设置分类
     */
    public function batchSetCategoryAction()
    {
        try {
            $cids = $this->data['cids'] ?? [];
            $categoryIds = $this->data['category_ids'] ?? [];
            if (empty($cids)) {
                $this->errorJson('cids 为空');
                return;
            }

            // 过滤空值
            $cids = array_filter($cids);
            $categoryIds = array_values(array_filter((array) $categoryIds));

            transaction(function () use ($cids, $categoryIds) {
                $service = new ContentsService();
                $service->handleBatchUpdateCategory($cids, $categoryIds);
            });

            $this->showJson([
                'cid' => $cids,
                'field' => $categoryIds,
            ], self::STATUS_SUCCESS, empty($categoryIds) ? '分类已清空' : '批量设置成功');
        } catch (\Throwable $e) {
            $this->errorJson($e->getMessage());
        }
    }

    /**
     * Markdown 预览（尽量模拟前台 Archives 详情页效果）
     * POST /adminv2/contents/preview
     *
     * 参数:
     * - text: Markdown 内容
     * - content: 兼容字段，等同于 text
     * - title: 文章标题（用于 img alt 等）
     */
    public function previewAction()
    {
        $text = (string)($this->data['text'] ?? $this->data['content'] ?? '');
        $title = (string)($this->data['title'] ?? '');

        if ($text === '') {
            return $this->showJson(['html' => '']);
        }

        // 尽量与前台 ArchivesController->indexAction 中的 loadWebMarkdown 保持一致
        // 前台是：$content->loadWebMarkdown(); 内部调用 LibMarkdown::loadWebMarkdown($this->text, false, $this->title)
        $html = \tools\LibMarkdown::loadWebMarkdown($text, false, $title);

        // 为了在后台预览时直接看到真实图片，而不是占位图 + data-src 懒加载，
        // 这里将 <img class="lazy" src="占位图" data-src="真实地址"> 转换为 <img src="真实地址"> 形式
        $html = preg_replace_callback(
            '#<img([^>]*?)src=[\'"]([^"\']*)[\'"]([^>]*?)>#i',
            function ($match) {
                $fullTag = $match[0];

                // 提取 data-src
                if (preg_match('/data-src=[\'"]([^"\']+)[\'"]/i', $fullTag, $dm)) {
                    $realSrc = $dm[1];

                    // 用真实地址替换 src
                    $fullTag = preg_replace(
                        '/src=[\'"][^"\']*[\'"]/i',
                        'src="' . $realSrc . '"',
                        $fullTag
                    );

                    // 删除 data-src 属性
                    $fullTag = preg_replace('/\sdata-src=[\'"][^"\']*[\'"]/i', '', $fullTag);

                    // 去掉 lazy 类（可选）
                    $fullTag = preg_replace_callback(
                        '/\sclass=[\'"]([^"\']*)[\'"]/i',
                        function ($cm) {
                            $classes = preg_split('/\s+/', $cm[1]);
                            $classes = array_filter($classes, function ($c) {
                                return strtolower($c) !== 'lazy' && $c !== '';
                            });
                            if (empty($classes)) {
                                return '';
                            }
                            return ' class="' . implode(' ', $classes) . '"';
                        },
                        $fullTag
                    );
                }

                return $fullTag;
            },
            $html
        );

        return $this->showJson(['html' => $html]);
    }
}
