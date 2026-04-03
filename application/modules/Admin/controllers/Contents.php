<?php

use plugins\PluginHandle;
use plugins\PluginUtils;
use service\ContentsService;

/**
 * Class ContentsController
 * @author xiongba
 * @date 2022-11-03 09:30:57
 */
class ContentsController extends BackendBaseController
{

    use \repositories\HoutaiRepository, \website\HtmlCache;

    /**
     * 列表数据过滤
     * @return Closure
     */
    protected function listAjaxIteration()
    {
        return function (ContentsModel $item) {


            $item->loadTagWithCategory();

            // 分类ID数组
            $category_ids = [];
            $category_names = [];

            if ($item->categories && $item->categories->count() > 0) {
                $category_ids = array_column($item->categories->toArray(), 'mid');
                $category_names = array_column($item->categories->toArray(), 'name');
            }

            $item->category_ids = $category_ids;
            $item->category_str = !empty($category_names) ? implode(',', $category_names) : '无分类';

            // 加载作者信息
            $item->load('author');
            $item->author_name = $item->author ? $item->author->screenName : '未知作者';

            // banner 和热搜字段
            $item->load('fields');
            $item->hotSearch = 0;
            $item->banner = '';
            collect($item->fields)->map(function ($field) use (&$item) {
                if ($field->name == 'banner') {
                    $item->banner = url_image(parse_url($field->str_value, PHP_URL_PATH));
                }
                if ($field->name == 'hotSearch') {
                    $item->hotSearch = $field->str_value;
                }
            });

            // 时间格式化 - 直接使用系统时区（created / modified 已由访问器转为字符串）
            $item->created = $item->created ? date('Y-m-d H:i:s', strtotime($item->created)) : null;
            $item->modified = $item->modified ? date('Y-m-d H:i:s', strtotime($item->modified)) : null;

            // 添加发布时间字段（使用格式化后的字符串）
            $item->published_at = $item->created;

            // 附加展示字段
            $item->view_str = $item->getRawOriginal('view');
            $item->status_str = ContentsModel::STATUS[$item->status] ?? '未知状态';
            $item->type_str = ContentsModel::TYPE[$item->type] ?? '未知类型';
            $item->home_str = ContentsModel::IS_HOME_TICP[$item->is_home] ?? '未知状态';

            $item->setHidden([]);

            return $item;
        };
    }

    public function listAjaxAction()
    {
        try {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                return $this->ajaxError('非法请求');
            }

            $pkName = $this->getPkName();
            $modelBuilder = $this->getModelObject();
            $orderBy = $this->listAjaxOrder();

            if (empty($orderBy)) {
                $modelBuilder->orderBy($pkName, 'desc');
            } else {
                foreach ($orderBy as $column => $direction) {
                    $modelBuilder->orderBy($column, $direction);
                }
            }
//            wf("完整请求参数", json_encode($this->getRequest()->getQuery()));
            $queryWhere = $this->getRequest()->getQuery() ?? [];
            $categoryId = $queryWhere['category_id'] ?? null;

//            wf("查询的where参数", $queryWhere, true);
//            wf("从where中获取的category_id", $categoryId, true);

            $where = $this->builderWhereArray();
            $likeWhere = $this->builderLikeArray();
            $betweenWhere = $this->getSearchBetweenParam(); // 新增：获取between条件

            // 调试信息
//            wf("WHERE条件", $where, true);
//            wf("LIKE条件", $likeWhere, true);
//            wf("BETWEEN条件", $betweenWhere, true); // 新增调试信息
//            wf("分类ID", $categoryId, true);

            // 应用WHERE条件
            if (!empty($where)) {
                foreach ($where as $field => $value) {
                    if ($value !== '' && $value !== null && $value !== '__undefined__') {
                        switch ($field) {
                            case 'custormsort_id':
                                $modelBuilder->where($value, '>', 0);
                                break;
                            default:
                                $modelBuilder->where($field, $value);
                        }

                        wf("应用WHERE条件: {$field} = {$value}", true);

                        // 特别调试status搜索
                        if ($field === 'status') {
                            wf("搜索状态值", $value, true);
                            wf("状态常量定义", ContentsModel::STATUS, true);
                        }
                    }
                }
            }

            // 应用LIKE条件
            if (!empty($likeWhere)) {
                foreach ($likeWhere as $likeCond) {
                    if ($likeCond[0] === 'author.screenName') {
                        $searchValue = trim($likeCond[2], '%');
                        wf("作者搜索值", $searchValue, true);
                        $modelBuilder->whereHas('author', function ($query) use ($searchValue) {
                            $query->where('screenName', 'like', "%{$searchValue}%");
                        });
                    } else {
                        $modelBuilder->where(...$likeCond);
                        wf("应用LIKE条件: {$likeCond[0]} {$likeCond[1]} {$likeCond[2]}", true);
                    }
                }
            }

            // 新增：应用BETWEEN条件
            if (!empty($betweenWhere)) {
                foreach ($betweenWhere as $betweenCond) {
                    $modelBuilder->where(...$betweenCond);
//                    wf("应用BETWEEN条件: {$betweenCond[0]} {$betweenCond[1]} {$betweenCond[2]}", true);
                }
            }

            // 应用分类搜索条件
            if (!empty($categoryId) && $categoryId !== '' && $categoryId !== '__undefined__') {
                if ($categoryId === 'no_category') {
                    // 筛选无分类的文章（没有关联任何分类）
                    $modelBuilder->whereDoesntHave('categories');
                } else {
                    // 筛选指定分类的文章
                    $modelBuilder->whereHas('categories', function ($q) use ($categoryId) {
                        $q->where('id', $categoryId);
                    });
                }
            }

            // 应用热搜筛选条件
            $hotSearchFilter = $queryWhere['where']['hotSearch'] ?? null;
            if ($hotSearchFilter !== null && $hotSearchFilter !== '' && $hotSearchFilter !== '__undefined__') {
//                wf("应用热搜筛选: hotSearch = {$hotSearchFilter}", true);
                $modelBuilder->whereHas('fields', function ($q) use ($hotSearchFilter) {
                    $q->where('name', 'hotSearch')->where('str_value', $hotSearchFilter);
                });
            }

            list($limit, $offset) = self::limitOffsetByGet('page', 'limit', 50);
            $oldBuilder = clone $modelBuilder;
            $modelBuilder->limit($limit)->offset($offset);

            $this->whereSelectBefore($modelBuilder);

            // 调试：输出最终的SQL查询
//            wf("最终SQL查询", $modelBuilder->toSql(), true);
//            wf("SQL绑定参数", $modelBuilder->getBindings(), true);

            // 调试：检查数据库中的状态分布
            $statusDistribution = ContentsModel::selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->get()
                ->pluck('count', 'status')
                ->toArray();
//            wf("数据库中状态分布", $statusDistribution, true);

            $data = $modelBuilder->get()->map($this->listAjaxIteration());

            // 调试：检查返回数据的实际状态
            if (isset($where['status'])) {
                $actualStatuses = $data->pluck('status')->unique()->values()->toArray();
//                wf("搜索状态 {$where['status']} 返回的实际状态", $actualStatuses, true);

                // 检查是否有不符合搜索条件的数据
                $wrongStatusData = $data->filter(function ($item) use ($where) {
                    return $item->status !== $where['status'];
                });
                if ($wrongStatusData->count() > 0) {
//                    wf("发现不符合搜索条件的数据", $wrongStatusData->pluck('cid', 'status')->toArray(), true);
                }
            }

            return $this->ajaxReturn([
                'count' => $data->count(),
                'data' => $data,
                'msg' => '',
                'desc' => $this->getDesc($oldBuilder),
                'code' => 0
            ]);
        } catch (\Throwable $e) {
            wf("错误", $e->getMessage());
            return $this->ajaxError('服务端异常');
        }
    }


    protected function listAjaxWhere(): array
    {
        $where = $this->getRequest()->getQuery('where') ?? [];

        return array_filter($where, function ($item, $key) {
            if ($key === 'category_id') return false;
            if ($item === '__undefined__' || $item === null || $item === '') return false;
            return true;
        }, ARRAY_FILTER_USE_BOTH);
    }


    /**
     * 构建基础 where 条件数组（支持自定义过滤非法字段）
     * @return array
     */
    protected function builderWhereArray(): array
    {
        $query = $this->getRequest()->getQuery('where');

        if (!is_array($query)) {
            return [];
        }

        $where = [];
        $validFields = ['cid', 'status', 'is_home', 'custormsort_id'];

        foreach ($query as $key => $val) {
            if ($val === '' || $val === null || $val === '__undefined__') {
                continue;
            }

            if (!in_array($key, $validFields)) {
                continue;
            }

            $where[$key] = $val;
        }

        return $where;
    }


    protected function builderLikeArray(): array
    {
        $query = $this->getRequest()->getQuery('like');

        if (!is_array($query)) {
            return [];
        }

        $like = [];
        $validFields = ['title', 'author_name'];

        foreach ($query as $key => $val) {
            if ($val === '' || $val === null || $val === '__undefined__') {
                continue;
            }

            if (!in_array($key, $validFields)) {
                continue;
            }

            // 如果是作者名字搜索，需要通过关联查询
            if ($key === 'author_name') {
                $like[] = ['author.screenName', 'like', "%{$val}%"];
            } else {
                $like[] = [$key, 'like', "%{$val}%"];
            }
        }

        return $like;
    }

    protected function whereSelectBefore(&$query)
    {
        // 检查是否有作者名字搜索
        $likeQuery = $this->getRequest()->getQuery('like') ?? [];
        if (isset($likeQuery['author_name']) && !empty($likeQuery['author_name'])) {
            // 如果有作者名字搜索，需要加载作者关联
            $query->with('author');
        }

        // 始终加载作者关联，确保作者名字能正常显示
        $query->with('author');
    }


    public function delAllAction()
    {
    }


    /**
     * 更新内容状态
     * @return void
     */
    public function updateStatusAction()
    {
        try {

            $status = $this->getRequest()->getPost('status');
            $cids = $this->getRequest()->getPost('cids');


            $cids = is_array($cids) ? $cids : [$cids];

            transaction(function () use ($cids, $status) {

                $service = new ContentsService();
                $service->handleUpdateStatus($cids, $status);
            });

            $this->ajaxSuccessMsg("操作成功");
        } catch (\Throwable $e) {
            $this->ajaxError($e->getMessage());
        }
    }

    /**
     * 试图渲染
     * @return void
     */
    public function indexAction()
    {
        $this->assign('get', $_GET);
        $list = CategoriesModel::query()
            ->selectRaw('id, name')
            ->orderBy('sort_order')
            ->get()->toArray();
        // 获取分类列表
        $categories = CategoriesModel::all()->pluck('name', 'id')->toArray(); // [id => name]
        // 获取排序字段列表
        $customsorts = CustomSortModel::where('status', CustomSortModel::OPTION_STATUS_OPEN)->pluck('name', 'slug')->toArray(); // [id => name]

        $this->assign('category_options', $categories);
        $this->assign('customsort_options', $customsorts);
        $this->assign('theme_json', json_encode($list, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        // $this->assign('theme_json', $theme_json);
        $this->display();
    }

    public function txtAction()
    {

        $id = $_GET['id'] ?? 0;

        $post = ContentsModel::where('cid', $id)->first();
        if (!$post) {
            $this->ajaxError('文章不存在');
        }

        $post->loadTagWithCategory();
        $post->load(['fields', 'author']);

        // 处理自定义字段
        $post->hotSearch = 0;
        $post->ads_field = 0;
        collect($post->fields)->map(function ($field) use (&$post) {
            if ($field->name == 'hotSearch') {
                $post->hotSearch = $field->str_value;
            }
            if ($field->name == 'ads_field') {
                $post->ads_field = $field->str_value;
            }
        });

        $txt = $post->text;

        //替换图片
        $reg = '/(\b(https|http):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|]\.(png|jpg|gif|jpeg))/i';
        preg_match_all($reg, $txt, $match);
        foreach ($match[1] as $img) {
            $img_path = parse_url($img, PHP_URL_PATH);
            $txt = str_replace($img, "{{img-cdn}}" . $img_path, $txt);
        }
        //替换视频
        $reg2 = "/\[dplayer[^<>]*url=\"([^\"]+)\"[^\]]*\]/Ui";
        preg_match_all($reg2, $txt, $matches);

        //修改原凯泽写的，有个bug当多个视频的时候会重复添加视频{{m3u8-cdn}}
        // 替换视频链接
        foreach ($matches[0] as $i => $fullMatch) {
            $url = trim($matches[1][$i]);

            // 如果已经包含 cdn 标记，跳过处理
            if (str_contains($url, '{{m3u8-cdn}}') || str_contains($url, '{{mp4-cdn}}')) {
                continue;
            }

            // 处理 mp4 链接
            if (str_ends_with($url, '.mp4')) {
                // 检查是否包含域名
                if (parse_url($url, PHP_URL_HOST)) {
                    continue; // 如果包含域名则跳过替换
                }
                $newUrl = "{{mp4-cdn}}/" . ltrim($url, '/');
            } else {
                $path = ltrim(parse_url($url, PHP_URL_PATH), '/');
                $newUrl = "{{m3u8-cdn}}/" . $path;
            }

            $newTag = str_replace($url, $newUrl, $fullMatch);
            $txt = str_replace($fullMatch, $newTag, $txt);
        }


        $txt = preg_replace('/\s*<!--markdown-->\s*/', '', $txt);

        $categoryList = CategoriesModel::query()->orderBy('sort_order')->get();
        $authorlist = UsersModel::query()
            ->selectRaw('uid, screenName')
            // 停用 有文章的用户才显示  by 20250912 qing
            //            ->whereExists(function ($query) {
            //                $query->select(DB::raw(1))
            //                    ->from('contents')
            //                    ->whereColumn('contents.authorId', 'users.uid')
            //                    ->where('contents.type', ContentsModel::TYPE_POST);
            //            })
            ->orderByDesc('uid')
            ->get();

        $this->assign("authorlist", $authorlist->toArray());
        $this->assign("categoryList", $categoryList);
        $this->assign('post_id', $post->cid);
        $this->assign('post', $post);
        $this->assign('post_txt', $txt);
        $this->assign('post_title', $post->title);
        $this->assign('mp4_domain', 'https://play.xmyy8.co');
        $this->assign('m3u8_domain', 'https://video.iwanna.tv');


        $this->display();
    }


    public function add_txtAction()
    {


        $categoryList = CategoriesModel::query()->orderBy('sort_order')->get();
        $authorlist = UsersModel::query()
            ->selectRaw('uid, screenName')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('contents')
                    ->whereColumn('contents.authorId', 'users.uid')
                    ->where('contents.type', ContentsModel::TYPE_POST);
            })
            ->orderByDesc('uid')
            ->get();


        //echo json_encode($post);
        $this->assign("authorlist", $authorlist->toArray());
        $this->assign("categoryList", $categoryList);
        $this->assign('mp4_domain', 'https://play.xmyy8.co');
        $this->assign('m3u8_domain', 'https://video.iwanna.tv');


        $this->display();
    }

    // public function txt_saveAction()
    // {
    //     try{

    //     $input = file_get_contents('php://input');
    //     $data = json_decode($input, true);

    //     if (!$data) {

    //          $this->ajaxError('无效的数据格式');
    //     }

    //     transaction(function () use ($data) {

    //         $post_id = isset($data['cid']) ? intval($data['cid']) : 0;

    //         if ($post_id > 0) {
    //             $post = ContentsModel::find($post_id);
    //             if (!$post) {
    //                 throw new Exception('文章不存在');
    //             }
    //         } else {

    //             $post = new ContentsModel();
    //             $post->created = time();
    //             $post->modified = time();

    //         }

    //          //解析markdown 获取类型
    //         $txt = str_replace("{{mp4-cdn}}/", '', $data['content']);
    //         $txt = str_replace("{{m3u8-cdn}}/", '', $data['content']);
    //         $txt = str_replace("{{img-cdn}}", TB_IMG_PWA_CN, $data['content']);
    //         $post->cid = $post_id;
    //         $post->title = $data['title'];
    //         $post->text = '<!--markdown-->'.$txt;
    //         $post->type = $data['post_type'];
    //         $post->status = $data['status'];
    //         $post->authorId = $data['author'];
    //         $post->allowPing = $data['allowPing']??'0';
    //         $post->allowFeed = $data['allowFeed']??'0';
    //         $post->allowComment = $data['allowComment']??'0';

    //         if ($data['post_type'] === 'page') {
    //             $post->slug = !empty($data['page_slug']) ? $data['page_slug'] : ContentsModel::setSulg();
    //         }

    //         $savePost = $post->save();


    //         if (!$savePost) {
    //             throw new Exception('文章保存失败');
    //         }

    //        if (!empty($data['tags'])) {

    //             DB::table('tag_relationships')
    //                 ->where('cid', $post->cid)
    //                 ->delete();


    //             $tags = explode(',', $data['tags']);
    //             $tags = array_map('trim', $tags);
    //             $tags = array_filter($tags);

    //             $existingTags = TagsModel::query()
    //                 ->whereIn('name', $tags)
    //                 ->get();
    //             $existingTagNames = $existingTags->pluck('name')->toArray();


    //             foreach ($tags as $tag) {
    //                 if (!in_array($tag, $existingTagNames)) {
    //                     $newTag = TagsModel::create([
    //                         'name' => $tag,
    //                     ]);
    //                     $existingTags->push($newTag);
    //                 }
    //             }


    //             foreach ($existingTags as $tag) {
    //                 DB::table('tag_relationships')->insert([
    //                     'tag_id' => $tag->id,
    //                     'cid'    => $post->cid,
    //                 ]);
    //             }
    //         }

    //        if ($post_id === 0) {

    //             $newId = $post->cid;

    //         } else {
    //             $newId = $post_id;
    //         }


    //         preg_match_all('/\[dplayer\s+url="([^"]+)"/i', $txt, $matches);
    //         $urls = array_filter(array_unique($matches[1] ?? []));

    //         if (empty($urls)) {
    //             $attachments = AttachmentModel::whereIn('mp4_url', $urls)
    //             ->get(['id', 'mp4_url'])
    //             ->keyBy('mp4_url');

    //             if ($attachments->isEmpty()) {

    //             throw new Exception('无视频');
    //             }


    //             $attachIds = $attachments->pluck('id')->all();


    //             AttachmentModel::whereIn('id', $attachIds)
    //                 ->update(['cid' => $newId]);


    //             $notFound = array_diff($urls, $attachments->keys()->all());

    //             if (!empty($notFound)) {
    //                 throw new Exception('以下视频 URL 未在附件表中找到记录，无法更新 cid：'. $notFound);
    //             }
    //         }


    //           // $upload_type = $data['upload_type'] ?? AttachmentModel::UPLOAD_TYPE_COM;
    //         // $service = new \service\RemoteUserContentsService();
    //         // $service->uploadAttachment(1, $cid, $name, $mp4_url, $cover, $upload_type);
    //     });

    //     $this->ajaxSuccessMsg('保存成功');

    //     } catch (Throwable $e){
    //             $this->ajaxError($e->getMessage());
    //         }
    // }

    /**
     * 上面原码废弃，使用下面后的代码
     */

    public function txt_saveAction()
    {
        try {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);

            if (!$data || !isset($data['title']) || !isset($data['content'])) {
                return $this->ajaxError('必填字段缺失');
            }
            // trigger_log('cid 类型是：' . gettype($data['cid']) . '；值是：' . json_encode($data['cid']) );

            // var_dump(1111);die();
            transaction(function () use ($data) {
                $service = new ContentsService();

                //保存基本信息
                $post = $service->saveBasicInfo($data);

                //处理标签（即使为空字符串也要处理，用于删除所有标签）
                if (isset($data['tags'])) {
                    $service->handleTags($post, $data['tags']);
                }

                //处理分类
                if (!empty($data['categories'])) {
                    $service->handleCategories($post, $data['categories']);
                }

                //处理附件
                $service->handleVideoAttachments($post->text, $post->cid);

                //处理自定义字段
                $customFields = $data['custom_fields'] ?? [];

                if (!empty($customFields)) {

                    $service->handleCustomFields($post, $customFields);
                }

                //批量提交未切片的视频
                $service->handelVideoMakeSlice($post->cid);
            });

            return $this->ajaxSuccessMsg('保存成功');
        } catch (Throwable $e) {
            error_log('文章保存失败 :' . $e->getMessage() . PHP_EOL, 3, APP_PATH . '/storage/logs/log.log');
            return $this->ajaxError($e->getMessage());
        }
    }

    /**
     * 自动保存接口
     * @return string
     */
    public function auto_saveAction()
    {
        try {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);

            if (!$data || !isset($data['title']) || !isset($data['content'])) {
                return $this->ajaxError('必填字段缺失');
            }

            $post = null;
            transaction(function () use ($data, &$post) {
                $service = new ContentsService();

                // 自动保存时，如果是新文章且没有cid，则设置为待审核状态
                if (empty($data['cid'])) {
                    $data['status'] = 'draft'; // 设置为草稿状态
                    //$data['status'] = 'waiting'; // 新建文章设置为待审核状态
                } else {
                    // 编辑文章时，保留原文章的状态
                    $existingPost = \ContentsModel::find($data['cid']);
                    if ($existingPost && !empty($existingPost->status)) {
                        $data['status'] = $existingPost->status; // 保留原状态
                    } else {
                        // 如果找不到原文章或原状态为空，按新建处理，设为待审核
                        $data['status'] = 'waiting';
                    }
                }

                //保存基本信息
                $post = $service->saveBasicInfo($data);

                //处理标签（即使为空字符串也要处理，用于删除所有标签）
                if (isset($data['tags'])) {
                    $service->handleTags($post, $data['tags']);
                }

                //处理分类
                if (!empty($data['categories'])) {
                    $service->handleCategories($post, $data['categories']);
                }

                //处理附件
                $service->handleVideoAttachments($post->text, $post->cid);

                //处理自定义字段
                $customFields = $data['custom_fields'] ?? [];
                if (!empty($customFields)) {
                    $service->handleCustomFields($post, $customFields);
                }

                //批量提交未切片的视频
                $service->handelVideoMakeSlice($post->cid);
            });

            return $this->ajaxReturn([
                'code' => 0,
                'msg' => '自动保存成功',
                'data' => [
                    'cid' => $post ? $post->cid : null,
                    'saved_at' => date('Y-m-d H:i:s')
                ]
            ]);
        } catch (Throwable $e) {
            error_log('自动保存失败 :' . $e->getMessage() . PHP_EOL, 3, APP_PATH . '/storage/logs/log.log');
            return $this->ajaxError('自动保存失败：' . $e->getMessage());
        }
    }

    /**
     * 删除文章
     * @return string
     */


    public function mv_listAction()
    {
        try {
            $cid = $_GET['cid'] ?? '0';
            list($page, $limit) = \helper\QueryHelper::pageLimit();
            $service = new \service\ContentsService();
            $list = $service->atachmentList($cid, $page, $limit);
            return $this->showJson($list);
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }

    public function images_listAction()
    {

        try {

            $cid = $_GET['cid'] ?? '0';
            list($page, $limit) = \helper\QueryHelper::pageLimit();
            $service = new \service\ContentsService();
            $list = $service->atachmentImagesList($cid, $page, $limit);
            return $this->showJson($list);
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }


    public function previewAction()
    {
        try {
            // 获取POST数据
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);

            if (!isset($data['content'])) {
                return $this->ajaxError('缺少内容参数');
            }

            $content = $data['content'];

            // 处理域名替换
            $content = $this->handlePreviewContent($content);

            return $this->ajaxSuccess([
                'content' => $content
            ]);
        } catch (Throwable $e) {
            return $this->ajaxError($e->getMessage());
        }
    }

    private function handlePreviewContent(string $content): string
    {
        $mp4_domain = "https://play.xmyy8.co";
        $m3u8_domain = "https://video.iwanna.tv";

        // 1. 替换图片域名
        $content = str_replace('{{img-cdn}}', BASE_IMG_URL, $content);

        // 2. 替换MP4域名 
        $content = str_replace('{{mp4-cdn}}', $mp4_domain, $content);

        // 3. 替换M3U8域名 - 修改正则匹配模式
        $pattern = '/\[dplayer url="((?:\{\{m3u8-cdn\}\}[\/}]*)+)([^"]+)"/i';
        $content = preg_replace_callback($pattern, function ($matches) use ($m3u8_domain) {
            // $matches[1] 包含所有重复的 {{m3u8-cdn}} 部分
            // $matches[2] 包含实际的视频路径
            return '[dplayer url="' . $m3u8_domain . '/' . ltrim($matches[2], '/') . '"';
        }, $content);

        return $content;
    }


    public function app_hideAction()
    {
        $id = $_POST['id'];
        $content = ContentsModel::find($id);
        if ($content->app_hide == ContentsModel::APP_HIDE_NO) {
            $content->app_hide = ContentsModel::APP_HIDE_YES;
        } else {
            $content->app_hide = ContentsModel::APP_HIDE_NO;
        }
        $content->save();
        $this->ajaxSuccessMsg('操作成功');
    }

    public function web_showAction()
    {
        $id = $_POST['id'];
        $content = ContentsModel::find($id);
        if ($content->web_show == ContentsModel::WEB_SHOW_NO) {
            $content->web_show = ContentsModel::WEB_SHOW_YES;
        } else {
            $content->web_show = ContentsModel::WEB_SHOW_NO;
        }
        $content->save();
        $this->ajaxSuccessMsg('操作成功');
    }

    public function setTypeAction()
    {
        try {
            $cid = $_POST['cid'];
            $type = $_POST['type'];
            $sid = $_POST['sid'];
            transaction(function () use ($cid, $type, $sid) {
                $content = ContentsModel::find($cid);
                test_assert($content, '文章不存在');
                if ($type == ContentsModel::TYPE_SKITS) {
                    $skits = SkitsModel::find($sid);
                    test_assert($skits, '短剧合集不存在');
                    $field = FieldsModel::where('cid', $cid)->where('name', 'skits')->first();
                    if (!empty($field)) {
                        $field->int_value = $sid;
                    } else {
                        $field = FieldsModel::make();
                        $field->cid = $cid;
                        $field->name = 'skits';
                        $field->type = 'int';
                        $field->int_value = $sid;
                        $field->str_value = 0;
                        $field->float_value = 0;
                    }
                    $isOK = $field->save();
                    test_assert($isOK, '短剧设置失败');
                } elseif ($type == ContentsModel::TYPE_BIG_WENT) {
                    $bigEvent = BigEventModel::find($sid);
                    test_assert($bigEvent, '大事件不存在');
                    $field = FieldsModel::where('cid', $cid)->where('name', 'bigEvent')->first();
                    if (!empty($field)) {
                        $field->int_value = $sid;
                    } else {
                        $field = FieldsModel::make();
                        $field->cid = $cid;
                        $field->name = 'bigEvent';
                        $field->type = 'int';
                        $field->int_value = $sid;
                        $field->str_value = 0;
                        $field->float_value = 0;
                    }
                    $isOK = $field->save();
                    test_assert($isOK, '大事件设置失败');
                }
                $content->type = $type;
                if (in_array($this, [ContentsModel::TYPE_SKITS, ContentsModel::TYPE_BIG_WENT])) {
                    $content->web_show = ContentsModel::WEB_SHOW_NO;
                    $content->app_hide = ContentsModel::APP_HIDE_NO;
                }
                $content->save();
            });
            $this->ajaxSuccessMsg('操作成功');
        } catch (Throwable $e) {
            $this->ajaxError($e->getMessage());
        }
    }

    public function batchSetStatusAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->ajaxError('请求错误');
        }
        $post = $this->postArray();
        $ary = explode(',', $post['pks_'] ?? '');
        $ary = array_filter($ary);
        $status = $post['status'];
        try {
            transaction(function () use ($ary, $status) {
                ContentsModel::whereIn('cid', $ary)->get()->map(function (ContentsModel $item) use ($status) {
                    $item->status = $status;
                    $isOk = $item->save();
                    test_assert($isOk, '状态更新失败');
                });
            });
            return $this->ajaxSuccessMsg('操作成功');
        } catch (Exception $exception) {
            return $this->ajaxError($exception->getMessage());
        }
    }

    public function specialEditAction()
    {
        try {
            $cid = $_POST['cid'];
            $title = $_POST['title'];
            $created = $_POST['created'];
            $category_ids = $_POST['category_ids'];
            $tags = $_POST['tags'];
            $banner = $_POST['banner'];
            $hotSearch = $_POST['hotSearch'];
            transaction(function () use ($cid, $title, $created, $category_ids, $tags, $banner, $hotSearch) {
                $content = ContentsModel::find($cid);
                test_assert($content, '文章不存在');
                $content->created = strtotime($created);
                $content->title = $title;
                $isOk = $content->save();
                test_assert($isOk, '文章保存失败');
                if (!$content->relationLoaded('relationships')) {
                    $content->load([
                        'relationships' => function ($query) {
                            $query->with('meta');
                        },
                    ]);
                }
                foreach ($content->relationships as $relationship) {
                    $meta = $relationship->meta;
                    if ($meta->count > 0) {
                        $meta->decrement('count');
                    }
                }

                //先删除关系
                RelationshipsModel::where('cid', $content->cid)->delete();
                foreach ($category_ids as $mid) {
                    DB::table('relationships')->insert([
                        'mid' => $mid,
                        'cid' => $content->cid,
                    ]);
                }

                // 支持 # 和 , 拆分标签
                $tags = preg_split('/[#,\s]+/u', str_replace('，', ',', $tags));
                $tags = collect($tags)->map(function ($tag) {
                    return trim($tag);
                })->filter(function ($tag) {
                    return !empty($tag);
                })->values();
                // 验证标签：只允许中文、字母、数字和横杠
                $tagPattern = '/^[\x{4e00}-\x{9fa5}a-zA-Z0-9\-]+$/u';
                foreach ($tags as $tag) {
                    if (!preg_match($tagPattern, $tag)) {
                        throw new \RuntimeException("标签 '{$tag}' 格式不正确，只能包含中文、字母、数字和横杠，禁止使用表情、符号和空格");
                    }
                }
                $tagsItems = MetasModel::useWritePdo()->where('type', MetasModel::TYPE_TAG)->whereIn('slug', $tags)->get();
                $diff = $tags->diff($tagsItems->pluck('slug'));
                foreach ($diff as $tag) {
                    $meta = MetasModel::create([
                        'name' => $tag,
                        'slug' => $tag,
                        'type' => MetasModel::TYPE_TAG,
                        'count' => 0,
                    ]);
                    $tagsItems->add($meta);
                }
                if ($tagsItems->count()) {
                    MetasModel::whereIn('mid', $tagsItems->pluck('mid'))->increment('count');
                    foreach ($tagsItems as $item) {
                        DB::table('relationships')->insert([
                            'mid' => $item->mid,
                            'cid' => $content->cid,
                        ]);
                    }
                }
                //修改banner 和 热搜
                if ($banner) {
                    $banner = 'https://www.51cg1.com' . parse_url($banner, PHP_URL_PATH);
                }
                $bannerModel = FieldsModel::where('cid', $content->cid)->where('name', 'banner')->first();
                if (empty($bannerModel)) {
                    $bannerModel = FieldsModel::make();
                    $bannerModel->cid = $content->cid;
                    $bannerModel->name = 'banner';
                    $bannerModel->type = 'str';
                    $bannerModel->str_value = $banner;
                } else {
                    $bannerModel->str_value = $banner;
                }
                $bannerModel->save();

                $hotSearchModel = FieldsModel::where('cid', $content->cid)->where('name', 'hotSearch')->first();
                if (empty($hotSearchModel)) {
                    $hotSearchModel = FieldsModel::make();
                    $hotSearchModel->cid = $content->cid;
                    $hotSearchModel->name = 'hotSearch';
                    $hotSearchModel->type = 'str';
                    $hotSearchModel->str_value = $hotSearch;
                } else {
                    $hotSearchModel->str_value = $hotSearch;
                }
                $hotSearchModel->save();
            });
            $this->ajaxSuccessMsg('操作成功');
        } catch (Throwable $e) {
            $this->ajaxError($e->getMessage());
        }
    }

    public function clear_by_idAction()
    {
        $id = $_POST['id'];
        cached("archive:$id")->clearCached();

        $options = require APP_PATH . '/application/html.php';
        $this->NewHtmlCache(is_array($options) ? $options : []);
        $this->del("/archives/{$id}/");

        $this->ajaxSuccessMsg('操作成功');
    }

    /**
     * 清理单个文章缓存
     */
    public function clear_cacheAction()
    {
        try {
            $cid = $this->getRequest()->getPost('cid');

            if (empty($cid)) {
                return $this->ajaxError('文章ID不能为空');
            }

            // 清理文章详情缓存
            cached("archive:$cid")->clearCached();

            // 清理HTML静态缓存
            $options = require APP_PATH . '/application/html.php';
            $this->NewHtmlCache(is_array($options) ? $options : []);
            $this->del("/archives/{$cid}/");

            // 清理首页列表缓存
            for ($i = 1; $i <= 3; $i++) {
                cached('content:home-' . $i)->clearCached();
            }
            cached('content:home:count')->clearCached();

            return $this->ajaxSuccessMsg('缓存清理成功');
        } catch (Throwable $e) {
            return $this->ajaxError('缓存清理失败: ' . $e->getMessage());
        }
    }

    /**
     * 更新首页列表缓存
     */

    public function updateHomeCacheAction()
    {

        for ($i = 1; $i <= 3; $i++) {
            cached('content:home-' . $i)->clearCached();
        }
        cached('content:home:count')->clearCached(); //更新总数缓存
        $this->ajaxSuccessMsg('操作成功');
    }

    /**
     * 批量修改文章首页显示is_home
     */
    public function batchSetHomeAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->ajaxError('请求错误');
        }
        $post = $this->postArray();
        $ary = explode(',', $post['cid'] ?? '');
        $ary = array_filter($ary);
        $is_home = $post['is_home'] ?? ContentsModel::IS_NOT_HOME;
        try {
            transaction(function () use ($ary, $is_home) {
                ContentsModel::whereIn('cid', $ary)->get()->map(function (ContentsModel $item) use ($is_home) {
                    $item->is_home = $is_home;
                    $isOk = $item->save();
                    test_assert($isOk, '首页显示状态更新失败');
                });
            });
            return $this->ajaxSuccessMsg('操作成功');
        } catch (Exception $exception) {
            return $this->ajaxError($exception->getMessage());
        }
    }

    /**
     * 设置home_top值
     */


    public function setHomeTopAction()
    {
        $cid = $this->getRequest()->getPost('cid');
        $home_top = $this->getRequest()->getPost('home_top', 0);
        try {
            transaction(function () use ($cid, $home_top) {
                $content = ContentsModel::find($cid);
                test_assert($content, '文章不存在');
                $content->home_top = $home_top;
                $isOk = $content->save();
                test_assert($isOk, '首页置顶设置失败');
            });
            return $this->ajaxSuccessMsg('操作成功');
        } catch (Throwable $e) {
            return $this->ajaxError($e->getMessage());
        }
    }

    /**
     * 设置文章自定义字段hotSearch为1，也就是热搜状态
     */

    public function setHotSearchAction()
    {


        $cid = $this->getRequest()->getPost('cid');


        if (empty($cid)) {
            return $this->ajaxError('文章ID不能为空');
        }

        try {
            transaction(function () use ($cid) {
                $content = ContentsModel::find($cid);
                test_assert($content, '文章不存在');

                // 检查是否已存在 hotSearch 字段
                $existingField = FieldsModel::where('cid', $cid)->where('name', 'hotSearch')->first();

                if (empty($existingField)) {
                    // 不存在字段，创建新字段并设置为热搜
                    $field = new FieldsModel([
                        'cid' => $cid,
                        'name' => 'hotSearch',
                        'type' => 'int',
                        'int_value' => 1,
                        'str_value' => '1',
                        'float_value' => 0
                    ]);


                    try {
                        $isOk = $field->save();

                        test_assert($isOk, '热搜设置失败');
                    } catch (\Exception $e) {
                        trigger_log("保存异常: " . $e->getMessage());
                    }
                } else {
                    // 存在字段，切换状态
                    $currentValue = $existingField->str_value;
                    $newValue = $currentValue == 1 ? 0 : 1;

                    $existingField->str_value = $newValue;

                    try {
                        $isOk = $existingField->save();

                        test_assert($isOk, '热搜状态切换失败');
                    } catch (\Exception $e) {
                        trigger_log("保存异常: " . $e->getMessage());
                    }
                }
            });
            return $this->ajaxSuccessMsg('操作成功');
        } catch (Throwable $e) {
            return $this->ajaxError($e->getMessage());
        }
    }

    protected function getModelObject()
    {
        // 内容管理不显示独立页面和附件，只显示文章类型
        return ContentsModel::query()
            ->where('type', '!=', ContentsModel::TYPE_PAGE)
            ->where('type', '!=', ContentsModel::TYPE_ATTACHMENT);
    }


    /**
     * 获取本控制器和哪个model绑定
     * @return string
     */
    protected function getModelClass(): string
    {
        return ContentsModel::class;
    }

    /**
     * 定义数据操作的表主键名称
     * @return string
     */
    protected function getPkName(): string
    {
        return 'cid';
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


    public function deleteArticlesAction()
    {
        $cids = explode(',', $this->getRequest()->getPost('cids', ''));

        if (empty($cids)) {
            return $this->ajaxError('未选择内容');
        }// 在删除前获取文章信息用于 IndexNow 通知
        $contents = ContentsModel::whereIn('cid', $cids)->get();
        $articleUrls = [];

        foreach ($contents as $content) {
            if ($content->type === 'post') {
                try {
                    $articleUrls[] = rtrim(options('siteUrl'),'/').'/archives/' . $content->cid . '/';
                } catch (\Exception $e) {
                    error_log('生成删除文章URL失败: ' . $e->getMessage() . PHP_EOL, 3, APP_PATH . '/storage/logs/log.log');
                }
            }
        }
        $deleted = ContentsModel::whereIn('cid', $cids)->delete();

        if ($deleted) {
            // 删除成功后通知 IndexNow
            foreach ($articleUrls as $url) {
                try {
                    \SeoController::indexnowEnqueue($url);
                } catch (\Exception $e) {
                    error_log('IndexNow 删除通知失败: ' . $e->getMessage() . PHP_EOL, 3, APP_PATH . '/storage/logs/log.log');
                }
            }
            return $this->ajaxSuccessMsg('删除成功');
        } else {
            return $this->ajaxError('删除失败');
        }
    }


    /**
     * 获取分类列表
     */
    public function getCategoriesAction()
    {
        try {
            $categories = CategoriesModel::select('id', 'name')
                ->orderBy('sort_order', 'asc')
                ->get();

            $data = [];
            foreach ($categories as $category) {
                $data[] = [
                    'id' => $category->id,
                    'name' => $category->name
                ];
            }

            return $this->ajaxSuccess($data);
        } catch (Throwable $e) {
            return $this->ajaxError("获取分类失败: " . $e->getMessage());
        }
    }

    /**
     * 获取作者列表
     */
    public function getAuthorsAction()
    {
        try {
            $authors = UsersModel::select('uid', 'screenName as name')
                ->orderBy('uid', 'asc')
                ->get();

            $data = [];
            foreach ($authors as $author) {
                $data[] = [
                    'id' => $author->uid,
                    'name' => $author->name
                ];
            }

            return $this->ajaxSuccess($data);
        } catch (Throwable $e) {
            return $this->ajaxError("获取作者失败: " . $e->getMessage());
        }
    }

    protected function getSearchBetweenParam()
    {
        $get = $this->getRequest()->getQuery();
        $get['between'] = $get['between'] ?? [];
        $where = [];
        foreach ($get['between'] as $key => $value) {
            if ($key == 'created'){
                if (!empty($value['from']) && $value['from'] !== '__undefined__'){
                    $where[] = [$key, '>=', strtotime($value['from'])];
                }
                if (!empty($value['to']) && $value['to'] !== '__undefined__'){
                    $where[] = [$key, '<=', strtotime($value['to'] . ' 23:59:59')];
                }
            }else{
                if (!empty($value['from']) &&$value['from'] !== '__undefined__'){
                    $where[] = [$key, '>=', $value['from']];
                }
                if (!empty($value['to']) && $value['to'] !== '__undefined__'){
                    $where[] = [$key, '<=', $value['to'] . ' 23:59:59'];
                }
            }
        }

        return $where;
    }

    /**
     * 批量添加评论
     * @return string
     */
    public function batchAddCommentsAction()
    {
        try {
            $userNicknames = trim($this->getRequest()->getPost('user_nicknames', ''));
            $articleIds = trim($this->getRequest()->getPost('article_ids', ''));
            $commentContents = trim($this->getRequest()->getPost('comment_contents', ''));
            $timeFrom = (int)$this->getRequest()->getPost('time_from', 0);
            $timeTo = (int)$this->getRequest()->getPost('time_to', 0);
            $isTop = (int)$this->getRequest()->getPost('is_top', 0);

            if (empty($userNicknames) || empty($articleIds) || empty($commentContents)) {
                return $this->ajaxError('请填写完整信息');
            }

            if ($timeFrom < 0 || $timeTo < 0 || $timeFrom > $timeTo) {
                return $this->ajaxError('时间范围设置不正确');
            }

            // 解析用户昵称（多个用逗号分隔）
            $nicknames = array_filter(array_map('trim', explode(',', $userNicknames)));
            if (empty($nicknames)) {
                return $this->ajaxError('请至少输入一个用户昵称');
            }

            // 解析文章ID（多个用逗号分隔）
            $cids = array_filter(array_map('trim', explode(',', $articleIds)));
            if (empty($cids)) {
                return $this->ajaxError('请至少输入一个文章ID');
            }

            // 解析评论内容（一行一条）
            $comments = array_filter(array_map('trim', explode("\n", $commentContents)));
            if (empty($comments)) {
                return $this->ajaxError('请至少输入一条评论内容');
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
                            'cid'          => $cid,
                            'created'      => $commentCreated,
                            'author'       => $nickname,
                            'reply_author' => '',
                            'reply_aff'    => 0,
                            'thumb'        => '',
                            'app_aff'      => 0,
                            'authorId'     => 0,
                            'ownerId'      => $content->authorId,
                            'mail'         => '',
                            'url'          => '',
                            'ip'           => client_ip(),
                            'agent'        => 'web',
                            'text'         => htmlspecialchars($commentText, ENT_QUOTES, 'UTF-8'),
                            'type'         => CommentsModel::TYPE_COMMENT,
                            'status'       => CommentsModel::STATUS_APPROVED,
                            'is_top'       => $isTop,
                            'parent'       => 0,
                            'sec_parent'   => 0,
                            'admin_id'     => $this->getUser()->uid
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

            $message = "批量添加评论完成：成功 {$successCount} 条";
            if ($failCount > 0) {
                $message .= "，失败 {$failCount} 条";
                if (count($errorMessages) > 0) {
                    $message .= "。错误：" . implode('; ', array_slice($errorMessages, 0, 5));
                    if (count($errorMessages) > 5) {
                        $message .= "...（还有 " . (count($errorMessages) - 5) . " 条错误）";
                    }
                }
            }

            return $this->ajaxSuccessMsg($message);
        } catch (\Throwable $e) {
            return $this->ajaxError('批量添加评论失败：' . $e->getMessage());
        }
    }
}
