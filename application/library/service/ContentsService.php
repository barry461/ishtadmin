<?php


namespace service;

use AttachmentImagesModel;
use AttachmentModel;
use CategoriesModel;
use CategoryRelationshipsModel;
use CommentsLikeModel;
use CommentsModel;
use ContentsModel;
use FieldsModel;
use MemberModel;
use MetasModel;
use TagRelationshipsModel;
use TagsModel;
use UserUploadModel;
use UsersModel;

class ContentsService
{

    /**
     * 默认自定义字段
     */
    private const DEFAULT_FIELDS = [
        'banner' => '',
        'contentLang' => '',
        'disableBanner' => '0',
        'disableDarkMask' => '0',
        'enableFlowChat' => '0',
        'enableMathJax' => '0',
        'enableMermaid' => '0',
        'headTitle' => '',
        'TOC' => '0',
        'hide_list_author_cate' => '0'
    ];

    public function list_comment(MemberModel $member, $cid, $page, $limit = 30)
    {
        CommentsModel::setWatchUser($member);
        if ($page == 1) {
            $list = CommentsModel::list_first($cid, 1, 30);
        } else {
            $coids = CommentsModel::list_first_ids($cid);
            if (count($coids) < $limit) {
                return [];
            }
            $list = CommentsModel::list_comments($cid, $coids, $page, $limit);
        }

        return collect($list)->map(function ($item) {
            $item->items = [];
            if ($item->reply_ct > 0) {
                $item->items = CommentsModel::fir_sec_comment(
                    $item->cid,
                    $item->coid
                );
            }

            return $item;
        });
    }

    public function list_replys(MemberModel $member, $cid, $coid, $page, $limit)
    {
        CommentsModel::setWatchUser($member);

        return CommentsModel::list_replys($cid, $coid, $page, $limit);
    }

    public function like_comment(MemberModel $member, $coid)
    {
        $comment = CommentsModel::find($coid);
        test_assert($comment, '评论不存在');
        transaction(function () use ($member, $coid) {
            /** @var CommentsLikeModel $like */
            $like = CommentsLikeModel::query()
                ->where('coid', $coid)
                ->where('aff', $member->aff)->first();
            if (empty($like)) {
                $like = CommentsLikeModel::create([
                    'aff' => $member->aff,
                    'coid' => $coid,
                ]);
                test_assert($like, '添加点赞数据失败');
                jobs([CommentsModel::class, 'incrementLikeNum'], [$coid]);
                redis()->sAdd(sprintf(
                    CommentsLikeModel::CONTENTS_COMMENTS_LIKE,
                    $member->aff
                ), $coid);
            } else {
                test_assert($like->delete(), '清理点赞数据失败');
                jobs([CommentsModel::class, 'decrementLikeNum'], [$coid]);
                redis()->sRem(sprintf(
                    CommentsLikeModel::CONTENTS_COMMENTS_LIKE,
                    $member->aff
                ), $coid);
            }
        });
    }


    public function categoryMeats()
    {
        // YAC缓存分类元数据，不经常变动
        return yac()->fetch('category:categories', function () {
            $maxSort = CategoriesModel::max('sort_order');
            return CategoriesModel::query()
                ->orderBy($maxSort == 0 ? 'created_at' : 'sort_order', 'desc')
                ->get(['name as title', 'slug']);
        });
    }


    public function categoryPages()
    {
        // YAC缓存页面数据，不经常变动
        return yac()->fetch('category:pages', function () {
            return ContentsModel::query()
                ->with('fields')
                ->where('status', ContentsModel::STATUS_PUBLISH)
                ->where('type', ContentsModel::TYPE_PAGE)
                ->orderBy('home_top', 'desc')
                ->get(['title', 'slug', 'cid', 'type']);
        });
    }

    public function category()
    {
        $metas = $this->categoryMeats();
        // var_dump($metas);
        $pages = $this->categoryPages();
        if (!empty($pages)) {
            // echo json_encode($pages);
            foreach ($pages as $page) {
                $metas->push($page);
            }
        }

        return $metas;
    }

    public function mvList($slice_status, $page, $limit)
    {
        $query = UserUploadModel::query()
            ->when($slice_status != -1, function ($q) use ($slice_status) {
                $q->where('slice_status', $slice_status);
            });

        $list
            = $query->selectRaw('id,name,created_at,progress_rate,cover,mp4_url,m3u8_url,slice_status')
                ->orderByDesc('id')
                ->forPage($page, $limit)
                ->get();
        $total = 0;
        if ($page == 1) {
            $total = $query->count('id');
        }

        return [
            'list' => $list,
            'total' => ceil($total / $limit),
        ];
    }

    public function atachmentList($cid, $page, $limit)
    {
        $query = AttachmentModel::query();

        $list = $query->selectRaw('id,name,created_at,progress_rate,cover,mp4_url,m3u8_url,slice_status')
            ->orderByDesc('id')
            ->whereIn("cid", [$cid, 0])
            ->forPage($page, $limit)
            ->get();
        $total = 0;
        if ($page == 1) {
            $total = $query->count('id');
        }

        return [
            'list' => $list,
            'total' => ceil($total / $limit),
        ];
    }

    public function atachmentImagesList($cid, $page, $limit)
    {
        $query = AttachmentImagesModel::query();

        $list = $query->selectRaw('id,name,created_at,image_url,image_src')
            ->orderByDesc('id')
            ->whereIn("cid", [$cid, 0])
            ->forPage($page, $limit)
            ->get()->map(function ($item) {
                $item->image_url = url_image($item->image_url);
                return $item;
            });
        $total = 0;
        if ($page == 1) {
            $total = $query->count('id');
        }

        return [
            'list' => $list,
            'total' => ceil($total / $limit),
        ];
    }

    /**
     * 保存文章基本信息
     */
    public function saveBasicInfo(array $data): ContentsModel
    {
        $post = $this->getOrCreatePost($data['cid'] ?? 0);

        // 支持 text 或 content 字段名
        $content = $data['text'] ?? $data['content'] ?? '';
        $txt = $this->processContent($content);

        $shouldSlice = $this->shouldBeSlice($post, $data);

        // 处理作者ID：支持 author_id, authorId, author 三种字段名
        $authorId = $data['author_id'] ?? $data['authorId'] ?? $data['author'] ?? null;
        if ($authorId !== null) {
            $authorId = (int)$authorId;
            // 验证作者是否存在
            if ($authorId > 0 && !UsersModel::find($authorId)) {
                $authorId = 1; // 如果作者不存在，使用默认作者ID 1
            }
        } else {
            $authorId = 1; // 默认作者ID
        }

        $post->fill([
            'title' => $data['title'],
            'text' => '<!--markdown-->' . $txt,
            'type' => $data['type'] ?? $data['post_type'] ?? ContentsModel::TYPE_POST,
            'is_slice' => $shouldSlice ? "1" : "0",
            'status' => $data['status'] ?? ContentsModel::STATUS_DRAFT,
            'authorId' => $authorId,
            'allowPing' => $data['allowPing'] ?? '0',
            'allowFeed' => $data['allowFeed'] ?? '0',
            'allowComment' => $data['allowComment'] ?? '0',
            'created' => isset($data['publish_date']) ? strtotime($data['publish_date']) : time(),
        ]);

        // 处理 home_top：如果提交了值（包括0），使用提交的值；否则保留原值
        if (isset($data['home_top']) && $data['home_top'] !== '' && $data['home_top'] !== null) {
            $post->home_top = (int) $data['home_top'];
        } elseif ($post->exists) {
            // 更新操作时，如果未提交 home_top，保留原值（不修改）
            // $post->home_top 保持原值，不需要设置
        } else {
            // 新建时默认值为 0
            $post->home_top = 0;
            // 处理 is_home：如果是 page 类型，默认不在首页显示
            $postType = $data['type'] ?? $data['post_type'] ?? ContentsModel::TYPE_POST;
            if ($postType === 'page') {
                $post->is_home = 0;
            } elseif (isset($data['is_home'])) {
                $post->is_home = (int) $data['is_home'];
            }
        }

        $postType = $data['type'] ?? $data['post_type'] ?? ContentsModel::TYPE_POST;
        if ($postType === 'page') {
            $post->slug = !empty($data['page_slug']) ? $data['page_slug'] : ContentsModel::getSlug();
        }

        $isOk = $post->save();
        test_assert($isOk, '文章保存失败');

        return $post;
    }

    private function shouldBeSlice(ContentsModel $post, array $data): bool
    {
        $content = $data['content'] ?? '';

        preg_match_all('/\[dplayer\s+url="([^"]+)"/i', $content, $matches);
        $videoUrls = array_filter(array_unique($matches[1] ?? []));

        if (empty($videoUrls)) {
            return true;
        }

        $mp4Urls = array_filter($videoUrls, function ($url) {
            return preg_match('/\.mp4$/i', $url);
        });

        if (empty($mp4Urls)) {
            return true;
        }

        if (empty($post->cid)) {
            return false;
        }

        $unfinishedCount = AttachmentModel::where('cid', $post->cid)
            ->whereIn('slice_status', [
                AttachmentModel::SLICE_WAIT,
                AttachmentModel::SLICE_PROCESS,
            ])
            ->where(function ($query) use ($mp4Urls) {
                foreach ($mp4Urls as $url) {
                    $query->orWhere('mp4_url', $url);
                }
            })
            ->count();

        return $unfinishedCount === 0;
    }


    /**
     * 获取或创建文章
     */
    public function getOrCreatePost(int $postId): ContentsModel
    {
        if ($postId > 0) {
            $post = ContentsModel::findOrFail($postId);
        } else {
            $post = new ContentsModel();
            $post->created = time();
        }

        $post->modified = time();
        return $post;
    }

    /**
     * 处理文章内容
     */
    public function processContent(string $content): string
    {
        return str_replace(
            ["{{mp4-cdn}}/", "{{m3u8-cdn}}/", "{{img-cdn}}"],
            ["", "", TB_IMG_PWA_CN],
            $content
        );
    }


    /**
     * 处理文章自定义字段
     */
    public function handleCustomFields(ContentsModel $post, array $customFields = []): void
    {

        $deleteResult = FieldsModel::where('cid', $post->cid)->delete();
        test_assert($deleteResult !== false, '删除旧自定义字段失败');


        $processedFields = [];
        foreach ($customFields as $field) {
            if (isset($field['name']) && isset($field['value'])) {
                $processedFields[$field['name']] = $field['value'];
            }
        }


        $fields = array_merge(self::DEFAULT_FIELDS, $processedFields);


        foreach ($fields as $name => $value) {

            $strValue = is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : (string) $value;

            $field = new FieldsModel([
                'cid' => $post->cid,
                'name' => $name,
                'type' => 'str',
                'str_value' => $strValue,
                'int_value' => is_numeric($value) ? (int) $value : 0
            ]);

            $isOk = $field->save();
            test_assert($isOk, "创建自定义字段失败: {$name}");
        }
    }

    /**
     * 处理文章分类关系
     */
    public function handleCategories(ContentsModel $post, array $categories): void
    {

        $deleteResult = CategoryRelationshipsModel::where('cid', $post->cid)
            ->delete();
        test_assert($deleteResult !== false, '删除旧分类关系失败');

        if (empty($categories)) {
            return;
        }


        foreach ($categories as $categoryId) {
            $meta = new CategoryRelationshipsModel([
                'cid' => $post->cid,
                'category_id' => $categoryId,
            ]);
            $isOk = $meta->save();
            test_assert($isOk, "创建分类关联关系失败，分类ID: {$categoryId}");
        }


        $existingCategories = CategoriesModel::whereIn('id', $categories)->count();
        test_assert($existingCategories === count($categories), '存在无效的分类ID');

    }


    public function handleTags(ContentsModel $post, string $tagsString): void
    {
        // 删除旧标签关系
        $deleteResult = TagRelationshipsModel::where('cid', $post->cid)->delete();
        test_assert($deleteResult !== false, '删除旧标签关系失败');

        // 支持用 # 和 , 作为分隔符，例如：
        // "#美女#吃瓜" => ["美女", "吃瓜"]
        // "美女,吃瓜"   => ["美女", "吃瓜"]
        // 先将中文逗号统一成英文逗号，方便统一处理
        $normalized = str_replace('，', ',', $tagsString);
        // 使用正则按 # 或 , 拆分，过滤空值
        $rawTags = preg_split('/[#,\s]+/u', $normalized);
        $tags = array_values(array_filter(array_map('trim', $rawTags), function ($tag) {
            return $tag !== '';
        }));

        if (empty($tags)) {
            return;
        }

        // 验证标签：只允许中文、字母、数字和横杠
        $tagPattern = '/^[\x{4e00}-\x{9fa5}a-zA-Z0-9\-]+$/u';
        foreach ($tags as $tag) {
            if (!preg_match($tagPattern, $tag)) {
                throw new \RuntimeException("标签 '{$tag}' 格式不正确，只能包含中文、字母、数字和横杠，禁止使用表情、符号和空格");
            }
        }

        // 获取现有标签
        $existingTags = TagsModel::whereIn('name', $tags)->get();
        test_assert($existingTags !== null, '获取已存在标签失败');

        $existingTagNames = $existingTags->pluck('name')->toArray();

        // 创建新标签
        $newTags = array_diff($tags, $existingTagNames);
        if (!empty($newTags)) {
            $tagModels = array_map(function ($name) {
                return new TagsModel(['name' => $name]);
            }, $newTags);

            foreach ($tagModels as $tagModel) {
                $isOk = $tagModel->save();
                test_assert($isOk, "创建标签 '{$tagModel->name}' 失败");
            }

            // 重新获取所有标签
            $existingTags = TagsModel::whereIn('name', $tags)->get();
            test_assert($existingTags !== null, '获取更新后的标签失败');
        }

        // 创建标签关联
        foreach ($existingTags as $tag) {
            $relation = new TagRelationshipsModel([
                'tag_id' => $tag->id,
                'cid' => $post->cid
            ]);
            $isOk = $relation->save();
            test_assert($isOk, "创建标签 '{$tag->name}' 关联关系失败");
        }
    }

    /**
     * 处理视频附件关联
     */
    public function handleVideoAttachments(string $content, int $postId): void
    {

        preg_match_all('/\[dplayer\s+url="([^"]+)"/i', $content, $matches);
        $urls = array_filter(array_unique($matches[1] ?? []));


        // test_assert(!empty($urls), '未找到视频参数');
        if (empty($urls))
            return;

        $attachments = AttachmentModel::whereIn('mp4_url', $urls)
            ->get(['id', 'mp4_url'])
            ->keyBy('mp4_url');

        // test_assert(!$attachments->isEmpty(), '未找到相关视频记录');

        if ($attachments->isEmpty())
            return;


        $updateResult = AttachmentModel::whereIn('id', $attachments->pluck('id'))
            ->update(['cid' => $postId]);
        test_assert($updateResult !== false, '更新视频关联失败');


        $notFound = array_diff($urls, $attachments->keys()->all());
        if (!empty($notFound)) {
            error_log('未找到的视频 URL: ' . print_r($notFound, true) . PHP_EOL, 3, APP_PATH . '/storage/logs/log.log');
            test_assert(false, '以下视频URL未找到对应记录：' . implode(', ', $notFound));
        }


        error_log('成功更新视频关联，文章ID: ' . $postId . ', 视频数量: ' . count($attachments) . PHP_EOL, 3, APP_PATH . '/storage/logs/log.log');
    }


    /**
     * 获取当前时间，用于更新数据库
     * 优先用 Carbon，如果报错就用 date() 兜底
     * 线上一般不会报错，这个主要是防止某些环境出问题
     */
    private function nowForUpdate()
    {
        $fallback = false;
        set_error_handler(function ($errno, $errstr) use (&$fallback) {
            if ($errno === E_WARNING
                && (strpos($errstr, 'TranslatorInterface') !== false
                    || strpos($errstr, 'Yaf\\Loader::autoload') !== false
                    || strpos($errstr, 'Failed opening script') !== false)) {
                $fallback = true;
                return true; // 忽略这个错误，不让程序退出
            }
            return false;
        });
        try {
            $now = \Carbon\Carbon::now();
            return $fallback ? date('Y-m-d H:i:s') : $now;
        } finally {
            restore_error_handler();
        }
    }

    /**
     * 处理视频提交远程切片
     * @param int $postId 文章ID
     * @throws \Exception
     */
    public function handelVideoMakeSlice(int $postId): void
    {
        try {

            $pendingVideos = AttachmentModel::where([
                'cid' => $postId,
                'slice_status' => AttachmentModel::SLICE_WAIT
            ])
                ->whereNotNull('mp4_url')
                ->get();

            if ($pendingVideos->isEmpty()) {
                trigger_log("文章ID:{$postId} 没有需要切片的视频");
                return;
            }


            $updateCount = AttachmentModel::whereIn('id', $pendingVideos->pluck('id'))
                ->update([
                    'slice_status' => AttachmentModel::SLICE_PROCESS,
                    'updated_at' => $this->nowForUpdate()
                ]);

            if ($updateCount <= 0) {
                throw new \Exception('批量更新视频状态失败');
            }


            $jobData = $pendingVideos->map(function ($video) {

                return [[AttachmentModel::class, 'makeSlice'], [$video]];

            })->toArray();

            $this->jobsBatch($jobData);


            trigger_log(sprintf(
                "文章ID:%d 的视频切片任务已提交，共 %d 个视频",
                $postId,
                $pendingVideos->count()
            ));

        } catch (\Exception $e) {

            AttachmentModel::whereIn('id', $pendingVideos->pluck('id'))
                ->update([
                    'slice_status' => AttachmentModel::SLICE_WAIT,
                    'updated_at' => $this->nowForUpdate()
                ]);

            trigger_error("处理文章ID:{$postId} 视频切片失败: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 批量投递任务
     * @param array $jobs 任务数组
     */
    function jobsBatch(array $jobs): void
    {
        foreach ($jobs as $job) {
            jobs(...$job);
        }
    }

    /**
     * 批量更新内容状态
     */
    public function handleUpdateStatus(array $cids, string $status): void
    {
        try {
            test_assert(array_key_exists($status, ContentsModel::STATUS), '无效的状态参数');
            test_assert(!empty($cids), '请选择要操作的内容');

            $contents = ContentsModel::whereIn('cid', $cids)->get();
            test_assert(!$contents->isEmpty(), '未找到指定内容');

            foreach ($contents as $content) {
                $content->status = $status;
                $content->modified = time();

                test_assert($content->save(), "内容 ID:{$content->cid} 状态更新失败");
            }

        } catch (\Exception $e) {
            trigger_error("批量更新内容状态失败: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 批量更新文章分类
     * @param array $cids 文章ID数组
     * @param array $categoryIds 分类ID数组
     * @return void
     * @throws \Exception
     */
    public function handleBatchUpdateCategory(array $cids, array $categoryIds): void
    {
        try {
            test_assert(!empty($cids), '请选择要操作的内容');

            $contents = ContentsModel::whereIn('cid', $cids)->get();
            test_assert(!$contents->isEmpty(), '未找到指定内容');

            foreach ($contents as $content) {
                // Reuse the existing handleCategories method which handles deletion and insertion
                $this->handleCategories($content, $categoryIds);

                // Update modification time
                $content->modified = time();
                $content->save();
            }

        } catch (\Exception $e) {
            trigger_error("批量更新文章分类失败: " . $e->getMessage());
            throw $e;
        }
    }


}