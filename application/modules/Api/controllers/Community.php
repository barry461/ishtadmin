<?php

use helper\QueryHelper;
use helper\Validator;
use service\CommunityService;
use tools\RedisService;

class CommunityController extends BaseController
{
    // 话题分类列表
    public function list_cateAction()
    {
        try {
            $service = new CommunityService();
            $cates = $service->getListCates();
            return $this->listJson($cates);
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    // 获取话题分页
    public function list_topicAction()
    {
        try {
            $validator = Validator::make($this->data, [
                'cate_id' => 'required|numeric|min:1',
            ]);
            if ($validator->fail($msg)) {
                throw new Exception($msg);
            }
            $cateId = (int)$this->data['cate_id'];
            list($page, $limit) = QueryHelper::pageLimit();
            $service = new CommunityService();
            $topics = $service->getCateTopics($cateId, $page, $limit);
            return $this->listJson($topics);
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    // 获取话题详情
    public function topic_detailAction()
    {
        try {
            $validator = Validator::make($this->data, [
                'topic_id' => 'required|numeric|min:1', //话题ID
            ]);
            if ($validator->fail($msg)) {
                throw new Exception($msg);
            }
            $id = (int)$this->data['topic_id'];
            $service = new CommunityService();
            $res = $service->getTopicDetail($id);
            return $this->showJson($res);
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    // 获取话题帖子列表
    public function list_topic_postAction()
    {
        try {
            $validator = Validator::make($this->data, [
                'cate' => 'required|enum:new,pic,video,txt,hot', //类型 new最新 pic图片 video视频 txt图文 hot 热门
                'topic_id' => 'required|numeric|min:1', //话题ID
            ]);
            if ($validator->fail($msg)) {
                throw new Exception($msg);
            }
            $cate = $this->data['cate'];
            if (!in_array($cate, ['new', 'hot'])){
                $cate = PostModel::TYPE_TIPS_PAR[$cate];
            }
            $topicId = (int)$this->data['topic_id'];
            list($page, $limit) = QueryHelper::pageLimit();
            $service = new CommunityService();
            $res = $service->listTopicPost($cate, $topicId, $this->member, $page, $limit);
            return $this->listJson($res);
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    // 社区发帖
    public function create_postAction()
    {
        try {
            $validator = Validator::make($this->data, [
                'topic_id' => 'required|numeric|min:1', //话题ID
                'title' => 'required|min:8', // 标题
                'content' => 'required' //内容(包含图片、视频、文字)
            ]);
            if ($validator->fail($msg)){
                throw new Exception($msg);
            }

            $topicId = (int)$this->data['topic_id'];
            $title = $this->data['title'];
            $content = $this->data['content'] ?? '';
            $is_subscribe = $this->data['is_subscribe'] ?? 0;
            //兼容低版本
            if (version_compare($this->version, '<=', '2.5.0')){
                $creator = PostCreatorModel::findByAff($this->member->aff);
                if ($creator->status == 1 and $creator->ban_post == 0){
                    $is_subscribe = 1;
                }
            }
            $title = html_entity_decode($title);
            $title = strip_tags($title);
            $content = html_entity_decode($content);
            $content = strip_tags($content);

            test_assert($content,'内容不能为空');
            $this->verifyMemberSayRole();
            $ipstr = USER_IP;

            $cityname = ($this->position['province'].$this->position['city']) ?: '火星';
            $service = new CommunityService();
            $service->createPost($this->member->refresh(), $topicId, $content, $title, $cityname, $ipstr, $is_subscribe);
            return $this->successMsg('成功');
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    // 获取帖子列表
    public function list_postAction()
    {
        try {
            $validator = Validator::make($this->data, [
                'cate' => 'required|enum:recommend,subscription', //类型 recommend-推荐 subscription-订阅
                'cate_sec' => 'required|enum:new,pic,video,txt', //类型 new最新 pic图片 video视频 txt文字',
            ]);
            if ($validator->fail($msg)) {
                throw new Exception($msg);
            }
            $cate = $this->data['cate'];
            $cateSec = $this->data['cate_sec'];
            if ($cateSec != 'new'){
                $cateSec = PostModel::TYPE_TIPS_PAR[$cateSec];
            }
            list($page, $limit) = QueryHelper::pageLimit();
            $service = new CommunityService();
            $list = $service->listPosts($this->member, $cate, $cateSec, $page, $limit);
            $ads = null;
            if ($page == 1){
                //$ads = \AdsModel::listPos(\AdsModel::POS_POST_RECOMMEND);
                $ads = \service\CommonService::getAds($this->member,\AdsModel::POS_POST_RECOMMEND);
            }
            return $this->listJson($list,
                [
                'ads' => $ads
            ]);
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    // 获取帖子详情
    public function post_detailAction()
    {
        try {
            $validator = Validator::make($this->data, [
                'id' => 'required|numeric|min:1', //帖子ID
            ]);
            if ($validator->fail($msg)) {
                throw new Exception($msg);
            }
            $postId = (int)$this->data['id'];
            $service = new CommunityService();
            $res = $service->getPostDetail($this->member, $postId);
            return $this->showJson([
                'detail' => $res,
                //'banner'  => AdsModel::listPos(AdsModel::POS_POST_DETAIL),
                'banner'  => \service\CommonService::getAds($this->member,AdsModel::POS_POST_DETAIL),
            ]);
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    // 搜索
    public function searchAction()
    {
        try {
            $validator = Validator::make($this->data, [
                'word' => 'required',
            ]);
            if ($validator->fail($msg)) {
                throw new Exception($msg);
            }
            $word = trim($this->data['word']);
            $service = new CommunityService();
            list($page, $limit) = QueryHelper::pageLimit();
            $res = $service->listSearchPost($word, $this->member, $page, $limit);
            $count = 0;
            if ($page == 1){
                $count = $service->getSearchCount($word);
            }
            return $this->listJson($res,[
                'count' => $count
            ]);
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    //点赞/取消点赞
    public function likeAction()
    {
        try {
            $validator = Validator::make($this->data, [
                'type' => 'required|enum:post,comment', //点赞类型 post帖子 comment评论
                'id' => 'required|numeric|min:1', //帖子ID
            ]);
            if ($validator->fail($msg)) {
                throw new Exception($msg);
            }
            $id = (int)$this->data['id'];
            $type = $this->data['type'];
            $aff = (int)$this->member['aff'];
            $this->verifyMemberSayRole();
            $service = new CommunityService();
            $service->like($type, $aff, $id);
            return $this->successMsg("成功");
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    // 获取评论列表
    public function post_commentsAction()
    {
        try {
            $validator = Validator::make($this->data, [
                'id' => 'required|numeric|min:1',//帖子ID
            ]);
            if ($validator->fail($msg)) {
                return $this->errorJson($msg);
            }
            $postId = (int)$this->data['id'];
            list($page, $limit) = QueryHelper::pageLimit();
            $service = new CommunityService();
            $data = $service->listCommentsByPostId($this->member, $postId, $page, $limit,$this->version);
            return $this->showJson($data);
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    // 评论详情分页
    public function commentsAction()
    {
        try {
            $validator = Validator::make($this->data, [
                'comment_id' => 'required|numeric|min:1',//评论ID
            ]);
            if ($validator->fail($msg)) {
                return $this->errorJson($msg);
            }
            $commentId = (int)$this->data['comment_id'];
            list($page, $limit) = QueryHelper::pageLimit();
            $service = new CommunityService();
            $data = $service->listCommentsByCommentId($this->member, $commentId, $page, $limit);
            return $this->listJson($data);
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    // 发布评论
    public function create_commentAction()
    {
        try {
            $postId = $this->data['post_id'] ?? 0;
            $content = $this->data['content'] ?? '';
            $medias = $this->data['medias'] ?? '';
            $commentId = $this->data['comment_id'] ?? 0;
            $aff = $this->member['aff'];
            $nickname = $this->member['nickname'];
            $cityname = ($this->position['province'].$this->position['city']) ?: '火星';
            $postId = (int)$postId;
            $commentId = (int)$commentId;

            //封禁IP
            $exist = redis()->sIsMember(BAN_IPS_KEY, USER_IP);
            if ($exist){
                return $this->errorJson('评论失败，你已被封禁！');
            }

            if (!$postId && !$commentId) {
                throw new Exception('帖子或者评论ID至少得存在一个');
            }
            if ($postId && $commentId) {
                //throw new Exception('帖子或者评论ID只能存在一个');
            }
            if ($medias) {
                $medias = htmlspecialchars_decode($medias);
                $medias = json_decode($medias, true);
            } else {
                $medias = [];
            }
            if (!$content && count($medias) == 0){
                throw new Exception('评论内容不能为空');
            }

            $this->verifyMemberSayRole();
            \PostBanModel::verifyCommentBan($this->member->aff);

            $cacheKey1 = sprintf("post:comment:rate:%s", $aff);
            if (RedisService::get($cacheKey1)) {
                // throw new Exception('一分钟内只能发一条评论');
            }

            $cacheKey2 = sprintf(PostCommentModel::POST_COMMENT_LIMIT_KEY, $aff);
            $mem = RedisService::get($cacheKey2);
            if (intval($mem) >= PostCommentModel::POST_COMMENT_LIMIT_NUM) {
                PostBanModel::setBan($aff);
                throw new RuntimeException('评论失败，你已被封禁！等待自动解禁，恶意刷评论打广告会被永久封禁。');
            }

            $service = new CommunityService();
            //评论待审核
            $status = PostCommentModel::STATUS_WAIT;

            $refuseReason = '';
            
            // 敏感词检测 - 自动过滤
            if ($content) {
                $sensitiveHandle = SensitiveWordsModel::sensitiveHandle();
                $hasSensitiveWord = $sensitiveHandle->islegal($content);
                if ($hasSensitiveWord) {
                    // 包含敏感词，自动标记为未通过状态
                    $status = PostCommentModel::STATUS_UNPASS;
                    $refuseReason = '自动过滤：包含敏感词';
                }
            }
            
            //判断帖子是否存在
            $post = null;
            if ($postId > 0){
                $post = \PostModel::getPostById($aff, $postId);
                test_assert($post , '此帖子不存在');
            }

            $parentComment = null;
            if ($commentId > 0){
                $parentComment = \PostCommentModel::getCommentById($aff, $commentId);
                test_assert($parentComment,'此评论不存在');
            }

            //图片和视频评论不过滤
            if ($content && count($medias) == 0) {
                $data = [
                    'post_id' => $parentComment ? $parentComment->post_id : $postId,
                    'pid' => $parentComment ? $parentComment->id : 0,
                    'aff' => $aff,
                    'reply_aff' => $parentComment ? $parentComment->aff : 0,
                    'comment' => $content,
                    'status' => $status,
                    'refuse_reason' => '',
                    'ipstr' => USER_IP,
                    'cityname' => $cityname,
                    'author' => $this->member->nickname,
                    'created_at' => date('Y-m-d H:i:s'),
                ];

                $str = preg_replace('/[^a-zA-Z0-9]/', '', $content);
                $affStr = generate_code($aff);
                $rubId = $postId ?: $commentId;
                if (stristr($str, $affStr) !== false) {
                    RubCommentModel::addData($rubId, $aff, $content, RubCommentModel::TYPE_POST, USER_IP, $cityname, $this->member->nickname, $data);
                    throw new Exception('禁止发邀请码');
                }

//                $isChecked = $service->filterStr($content);
//                if ($isChecked) {
//                    $isChecked = $service->filterBio($content);
//                }
//
//                if (!$isChecked) {
//                    RubCommentModel::addData($postId, $aff, $content, RubCommentModel::TYPE_POST, USER_IP, $cityname);
//                    test_assert(false, "存在敏感词");
//                    //$status = PostCommentModel::STATUS_UNPASS;
//                    //$refuseReason = '存在敏感词';// 评论内容
//                }
                $check2 = \tools\FilterService::validate($content);
                if (!$check2){
                    RubCommentModel::addData($rubId, $aff, $content, RubCommentModel::TYPE_POST, USER_IP, $cityname, $this->member->nickname, $data);
                    test_assert(false, '存在广告嫌疑');
                }
            }

            if ($commentId > 0) {
                $service->createComComment($commentId, $this->member, $nickname, $content, $medias, $cityname, $status, $refuseReason, $parentComment);
            } else {
                $service->createPostComment($postId, $this->member, $nickname, $content, $medias, $cityname, $status, $refuseReason, $post);
            }
            RedisService::set($cacheKey1, 1, 60);
            $mem = !$mem ? 1 : $mem + 1;
            RedisService::set($cacheKey2, $mem, PostCommentModel::POST_COMMENT_LIMIT_SECOND);
            return $this->successMsg('评论成功，请耐心等待审核');
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    /**
     * 个人中心帖子列表
     */
    public function list_myAction(){
        try {
            $validator = Validator::make($this->data, [
                'cate' => 'required|enum:wait,wait_release,pass,unpass,', //类型
            ]);
            if ($validator->fail($msg)) {
                throw new Exception($msg);
            }
            $cate = $this->data['cate'];
            if ($cate != 'wait_release'){
                $cate = PostModel::STATUS_TIPS_PARA[$cate];
            }
            list($page, $limit) = QueryHelper::pageLimit();
            $service = new CommunityService();
            $res = $service->listMyPosts($this->member, $cate, $page, $limit);
            return $this->listJson($res);
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    /**
     * 个人中心帖子列表
     */
    public function list_my_newAction(){
        try {
            $validator = Validator::make($this->data, [
                'cate' => 'required|enum:wait,wait_release,pass,unpass,', //类型
            ]);
            if ($validator->fail($msg)) {
                throw new Exception($msg);
            }
            $cate = $this->data['cate'];
            $is_subscribe = $this->data['is_subscribe'] ?? 0;
            if ($cate != 'wait_release'){
                $cate = PostModel::STATUS_TIPS_PARA[$cate];
            }
            list($page, $limit) = QueryHelper::pageLimit();
            $service = new CommunityService();
            $res = $service->listMyPostsNew($this->member, $cate, $is_subscribe, $page, $limit);
            return $this->showJson($res);
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    /**
     * 个人中心收益及设置
     */
    public function my_postcenterAction(){
        try {
            $service = new CommunityService();
            $res['club'] = $service->getPostCludInfo($this->member);
            return $this->showJson($res);
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    /**
     * 热搜接口
     */
    public function search_optionsAction(): bool
    {
        try {
            $this->verifyFrequency(10, 10);
            $toplist = redis()->zRevRange(\SearchWordModel::SEARCH_TOPLIST_POST_KEY, 0, 9, true);
            $toplist = collect($toplist)->map(function ($value, $key) {
                return ['name' => $key, 'value' => $value];
            })->values();

            return $this->showJson([
                'toplist' => $toplist,
                //'banner'  => \AdsModel::listPos(\AdsModel::POS_SEARCH_BANNER),
                'banner'  => \service\CommonService::getAds($this->member,\AdsModel::POS_SEARCH_BANNER),
            ]);
        } catch (\Throwable $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    // 他人中心-帖子
    public function peer_center_postAction()
    {
        try {
            $validator = Validator::make($this->data, [
                'aff' => 'required|numeric|min:1',//对方的aff
            ]);
            if ($validator->fail($msg)) {
                return $this->errorJson($msg);
            }
            $peerAff = (int)$this->data['aff'];
            list($page, $limit) = QueryHelper::pageLimit();
            $service = new CommunityService();
            $info = new \StdClass;
            if ($page == 1){
                $info = $service->getPeerInfo($this->member, $peerAff);
            }
            $list = $service->listPeerPosts($this->member, $peerAff, $page, $limit);
            return $this->listJson($list,[
                'info' => $info
            ]);
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    //收藏/取消收藏
    public function favoriteAction()
    {
        try {
            $validator = Validator::make($this->data, [
                'id' => 'required|numeric|min:1', //帖子ID
            ]);
            if ($validator->fail($msg)) {
                throw new Exception($msg);
            }
            $id = (int)$this->data['id'];
            $this->verifyMemberSayRole();
            $service = new CommunityService();
            $service->favorite($this->member, $id);
            return $this->successMsg("收藏成功");
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    public function list_favoriteAction()
    {
        try {
            list($page, $limit) = QueryHelper::pageLimit();
            $service = new CommunityService();
            $list = $service->listFavoritePosts($this->member, $page, $limit);
            return $this->listJson($list);
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    public function list_my_commentsAction()
    {
        try {
            list($page, $limit) = QueryHelper::pageLimit();
            $service = new CommunityService();
            $list = $service->listMyComments($this->member, $page, $limit);
            return $this->listJson($list);
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    // 获取帖子列表-新
    public function list_constructAction()
    {
        try {
            $validator = Validator::make($this->data, [
                'type' => 'required|enum:recommend,subscription,category,follow'
            ]);
            if ($validator->fail($msg)) {
                throw new Exception($msg);
            }
            $type = $this->data['type'];
            $sort = $this->data['sort'] ?? 'new';
            $category_id = $this->data['category_id'] ?? 0;
            list($page, $limit) = QueryHelper::pageLimit();
            $service = new CommunityService();
            $result = $service->list_construct($this->member, $category_id, $type, $sort, $page, $limit);
            return $this->showJson($result);
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    //作品评分
    public function scoreAction(){
        try {
            $validator = Validator::make($this->data, [
                'aff' => 'required|numeric|min:1', //评分用户
                'score' => 'required|min:1|max:5', //评分
            ]);
            if ($validator->fail($msg)) {
                throw new Exception($msg);
            }
            $aff = (int)$this->data['aff'];
            $score = $this->data['score'];
            $this->verifyMemberSayRole();
            $service = new CommunityService();
            $service->score($this->member, $aff, $score);
            return $this->successMsg("评分成功");
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    // 他人中心
    public function peer_centerAction()
    {
        try {
            $validator = Validator::make($this->data, [
                'aff' => 'required|numeric|min:1',//对方的aff
            ]);
            if ($validator->fail($msg)) {
                return $this->errorJson($msg);
            }
            $peerAff = (int)$this->data['aff'];
            $service = new CommunityService();
            $data = $service->getPeerInfo($this->member, $peerAff);
            return $this->showJson($data);
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    // 他人中心-帖子-新
    public function list_peer_postsAction()
    {
        try {
            $validator = Validator::make($this->data, [
                'aff' => 'required|numeric|min:1',//对方的aff
            ]);
            if ($validator->fail($msg)) {
                return $this->errorJson($msg);
            }
            $peerAff = (int)$this->data['aff'];
            $sort = $this->data['sort'] ?? 'new';
            list($page, $limit) = QueryHelper::pageLimit();
            $service = new CommunityService();
            $info = new \StdClass;
            \PostModel::setWatchUser($this->member);
            \MemberModel::setWatchUser($this->member);
            if ($page == 1){
                $info = $service->getPeerInfo($this->member, $peerAff);
            }
            $list = $service->listPeerPostsNew($this->member, $peerAff, $sort, $page, $limit);
            return $this->listJson($list,[
                'info' => $info
            ]);
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    // 获取评论列表v1
    public function post_comments_v1Action()
    {
        try {
            $validator = Validator::make($this->data, [
                'id' => 'required|numeric|min:1',//帖子ID
            ]);
            if ($validator->fail($msg)) {
                return $this->errorJson($msg);
            }
            $postId = (int)$this->data['id'];
            list($page, $limit) = QueryHelper::pageLimit();
            $service = new CommunityService();
            $data = $service->listCommentsByPostIdV1($this->member, $postId, $page, 30);
            return $this->showJson($data);
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    // 评论详情分页
    public function comments_v1Action()
    {
        try {
            $validator = Validator::make($this->data, [
                'comment_id' => 'required|numeric|min:1',//评论ID
            ]);
            if ($validator->fail($msg)) {
                return $this->errorJson($msg);
            }
            $commentId = (int)$this->data['comment_id'];
            list($page, $limit) = QueryHelper::pageLimit();
            $service = new CommunityService();
            $data = $service->listCommentsByCommentIdV1($this->member, $commentId, $page, $limit);
            return $this->listJson($data);
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    //点赞/取消点赞
    public function like_commentAction()
    {
        try {
            $validator = Validator::make($this->data, [
                'comment_id' => 'required|numeric|min:1', //评论ID
            ]);
            if ($validator->fail($msg)) {
                throw new Exception($msg);
            }
            $comment_id = (int)$this->data['comment_id'];
            $this->verifyMemberSayRole();
            $service = new \service\CommunityService();
            $service->like_comment($this->member, $comment_id);
            return $this->successMsg("成功");
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    // 打赏
    public function rewardAction()
    {
        try {
            $validator = Validator::make($this->data, [
                'id' => 'required|numeric|min:1',//帖子ID
                'amount' => 'required|numeric|min:1' // 金额
            ]);
            if ($validator->fail($msg)) {
                return $this->errorJson($msg);
            }

            $postId = (int)$this->data['id'];
            $member = $this->member->refresh();
            $amount = $this->data['amount'];

            $service = new CommunityService();
            $service->reward($member, $postId, $amount);
            return $this->successMsg('成功');
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }
}