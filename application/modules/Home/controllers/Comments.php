<?php

/**
 * CommentsController.php
 * @author  chenmoyuan
 */

/**
 * 评论
 */
class CommentsController extends WebController{

    

    public function commentAction()
    {
        $cid = $this->getRequest()->getParam('cid');

        if (empty($cid)) {
            return $this->x404();
        }
        $page = $this->getRequest()->getParam('page') ?? 1;
        $limit = $this->getRequest()->getParam('limit') ?? $this->limit;
        $list = cached("list-comment:$cid-{$page}")
            ->group("list-comment-list")
            ->chinese("WEB端文章评论列表缓存")
            ->fetchPhp(function () use ($cid, $page, $limit) {
                $collection = CommentsModel::where('cid', $cid)
                    ->selectRaw('coid,thumb,reply_author,author,authorId,ownerId,`text`,type,parent,created')
                    ->where('status', CommentsModel::STATUS_APPROVED)
                    ->where('parent', 0)
                    ->forPage($page, $limit)
                    ->get()
                    ->each(function (CommentsModel $item) {
                        // 每条一级评论取最新几条回复（按 coid 倒序），审核通过的新回复会排前面
                        $items = CommentsModel::where('cid', $item->cid)
                            ->selectRaw('coid,cid,thumb,reply_author,author,authorId,ownerId,`text`,type,parent,created')
                            ->where('status', CommentsModel::STATUS_APPROVED)
                            ->where('parent', $item->coid)
                            ->orderByDesc('coid')
                            ->limit(5)
                            ->get();
                        $item->setRelation('items', $items);
                        $item->setAttribute('is_owner', $item->authorId == $item->ownerId ? 1 : 0);
                    });
                // 转为纯数组并显式包含 items，避免缓存反序列化后 relation 丢失导致前端拿不到二级评论
                return $collection->map(function (CommentsModel $item) {
                    $arr = $item->toArray();
                    $arr['items'] = $item->items->map(function (CommentsModel $c) {
                        return $c->toArray();
                    })->values()->all();
                    return $arr;
                })->values()->all();
            }, 1800);

        // 若来自缓存且 items 为空但 DB 有已审核二级评论，则从 DB 补拉（解决旧缓存导致二级评论不显示）
        $list = $this->ensureCommentItemsFromDb($cid, $list);

        $total = count($list);
        $this->displayJson(array(
            "commentList" => $list, "msg" => true, "code" => 1,
            "total"       => $total,
            "limit"      => $this->limit,
        ));
    }

    /**
     * 若列表来自缓存且某条一级评论的 items 为空但 DB 有已审核二级评论，则从 DB 补拉并填充 items
     * 解决旧缓存（或反序列化丢失 relation）导致二级评论不显示的问题
     * @param int|string $cid 文章ID
     * @param array|\Illuminate\Support\Collection $list 评论列表
     * @return array
     */
    protected function ensureCommentItemsFromDb($cid, $list)
    {
        if (!is_array($list)) {
            $list = $list->map(function ($item) {
                return is_array($item) ? $item : $item->toArray();
            })->values()->all();
        }
        foreach ($list as &$row) {
            $parentCoid = isset($row['coid']) ? (int) $row['coid'] : 0;
            $articleCid = isset($row['cid']) ? (int) $row['cid'] : (int) $cid;
            $existingItems = $row['items'] ?? [];
            $existingCount = is_array($existingItems) ? count($existingItems) : 0;
            if ($parentCoid > 0 && $existingCount === 0) {
                $items = CommentsModel::where('cid', $articleCid)
                    ->where('parent', $parentCoid)
                    ->where('status', CommentsModel::STATUS_APPROVED)
                    ->orderByDesc('coid')
                    ->limit(5)
                    ->get()
                    ->map(function (CommentsModel $c) {
                        return $c->toArray();
                    })
                    ->values()
                    ->all();
                $row['items'] = $items;
            }
        }
        unset($row);
        return $list;
    }

    /**
     * 某条一级评论下的全部二级回复（用于前台「查看全部回复」）
     * GET /commentList/{cid}/replies/{parentId}
     */
    public function commentRepliesAction()
    {
        $cid = (int) $this->getRequest()->getParam('cid');
        $parentId = (int) $this->getRequest()->getParam('parentId');
        if ($cid <= 0 || $parentId <= 0) {
            return $this->x404();
        }
        $list = CommentsModel::where('cid', $cid)
            ->selectRaw('coid,cid,thumb,reply_author,author,authorId,ownerId,`text`,type,parent,created')
            ->where('status', CommentsModel::STATUS_APPROVED)
            ->where('parent', $parentId)
            ->orderByDesc('coid')
            ->get()
            ->map(function (CommentsModel $item) {
                $arr = $item->toArray();
                $ts = $item->getRawOriginal('created');
                $arr['created'] = $ts ? date('Y-m-d H:i:s', $ts) : '';
                return $arr;
            });
        $this->displayJson(array(
            'commentList' => $list,
            'msg' => true,
            'code' => 1,
        ));
    }

    /**
     * 处理旧评论分页路径 /cmt/{cid}/respond-post-{cid}/page/{page}/{limit}/
     * 无内容返回410，有内容返回200，都设置X-Robots-Tag: noindex, nofollow
     */
    public function oldCommentPageAction()
    {
        $cid = $this->getRequest()->getParam('cid');
        $respondCid = $this->getRequest()->getParam('respond_cid');
        $page = $this->getRequest()->getParam('page') ?? 1;
        $limit = $this->getRequest()->getParam('limit') ?? $this->limit;

        // 验证参数
        if (empty($cid) || !is_numeric($cid) || $cid <= 0) {
            return $this->x410();
        }

        // 验证两个cid是否一致（旧URL格式要求）
        if ($cid != $respondCid) {
            return $this->x410();
        }

        // 验证分页参数
        $page = max(1, (int)$page);
        $limit = max(1, (int)$limit);

        // 检查该分页是否有已审核的评论（复用commentAction的查询逻辑）
        $hasComments = CommentsModel::where('cid', $cid)
            ->where('status', CommentsModel::STATUS_APPROVED)
            ->where('parent', 0)
            ->forPage($page, $limit)
            ->exists();

        if (!$hasComments) {
            // 无内容，返回410状态码（x410方法已设置X-Robots-Tag头）
            return $this->x410();
        }

        // 有内容，返回200状态码并设置X-Robots-Tag头
        http_response_code(200);
        $this->getResponse()->setHeader('X-Robots-Tag', 'noindex, nofollow');
        
        // 返回空响应
        \Yaf\Registry::set('html:skip', true);
        $this->getResponse()->setBody('');
        return true;
    }

    public function create_commentAction(){

        $data  = $this->getRequest()->getPost();
        $cid = $this->getRequest()->getParam('cid');

        $coid = $data['parent'] ?? null;
        $text = trim($data['text'] ?? '');

        $author = $data['author'] ?? null;

        if (empty($cid) || empty($text) || empty($author)) {
            return $this->x404();
        }
        //封禁IP
        $exist = redis()->sIsMember(BAN_IPS_KEY, USER_IP);
        if ($exist){
            return $this->x404();
        }
        try {
            $parent = null;
            $model = ContentsModel::find($cid);
            test_assert($model, '您评论的文章不存在');
            $parent_coid = 0;
            $sec_parent_coid = 0;
            if (is_numeric($coid) && $coid > 0) {
                $parent = CommentsModel::find($coid);
                test_assert($parent, '您回复的评论不存在');
                if ($parent->parent){
                    //二级评论
                    $parent_coid = $parent->parent;
                    $sec_parent_coid = $parent->coid;
                }else{
                    //一级评论ID作为父ID
                    $parent_coid = $parent->coid;
                }
            }
            if (empty($model->allowComment)) {
                throw new RuntimeException('该文章不允许评论');
            }

            // 敏感词检测 - 自动过滤
            $sensitiveHandle = SensitiveWordsModel::sensitiveHandle();
            $hasSensitiveWord = $sensitiveHandle->islegal($text);
            $commentStatus = CommentsModel::STATUS_WAITING;
            
            if ($hasSensitiveWord) {
                // 包含敏感词，自动标记为过滤状态
                $commentStatus = CommentsModel::STATUS_FILTER;
            }

            $data = [
                'cid'          => $model->cid,
                'created'      => time(),
                'author'       => $author,
                'reply_author' => $parent ? $parent->author : '',
                'reply_aff'    => $parent ? $parent->app_aff : 0,
                'thumb'        => "",
                'app_aff'      => "",
                'authorId'     => 0,
                'ownerId'      => $model->authorId,
                'mail'         => '',
                'url'          => '',
                'ip'           => client_ip(),
                'agent'        => 'web',
                'text'         =>  htmlspecialchars($text, ENT_QUOTES, 'UTF-8'),
                'type'         => CommentsModel::TYPE_COMMENT,
                'status'       => $commentStatus,
                'parent'       => $parent_coid,
                'sec_parent'   => $sec_parent_coid, //二级评论ID
            ];


            $comment = CommentsModel::create($data);

             if ($comment) {
                // 埋点数据 (映射 Video Comment 规范)
                // 规范: event, video_id, video_title, video_type_id, video_type_name, comment_content
                // 映射为: article_comment, article_id, ...
                $categoryName = '';
                try {
                    $category = AppCategoryModel::find($model->category_id);
                    if ($category) $categoryName = $category->name;
                } catch (\Exception $e) {}

                $trackingData = [
                    'event' => 'article_comment',
                    'article_id' => (string)$model->cid,
                    'article_title' => $model->title,
                    'category_id' => (string)$model->category_id,
                    'category_name' => $categoryName,
                    'comment_content' => $text
                ];

                exit(json_encode(array(
                    "msg"=>'评论成功，请耐心等待审核',
                    'status'=>1,
                    'tracking_data' => $trackingData
                )));
             }
             exit(json_encode(array("msg"=>'评论失败')));
        } catch (\Throwable $e) {
            exit(json_encode(array("msg"=>$e->getMessage())));
        }
    }

    

}