<?php


namespace service;

use Carbon\Carbon;
use MemberModel;
use MemberPostScoreModel;
use PostClubMembersModel;
use PostClubsModel;
use PostCommentModel;
use PostCommentsLikeModel;
use PostCreatorModel;
use PostMediaModel;
use PostModel;
use PostTopicCategoryModel;
use PostTopicModel;
use SystemNoticeModel;
use SysTotalModel;
use UserCommunityLikeModel;
use UserFavoritesLogModel;
use UserPostTopicFollowModel;

class CommunityService
{
    // 获取话题分类
    public function getListCates()
    {
        return PostTopicCategoryModel::listCates();
    }

    // 获取话题分页
    public function getCateTopics($cateId, $page, $limit)
    {
        $offset = ($page - 1) * $limit;
        return PostTopicModel::listTopics($cateId, $offset, $limit);
    }

    // 话题详情信息
    public function getTopicDetail($id)
    {
        $topic = PostTopicModel::getTopicById($id);
        test_assert($topic,'无此话题');
        return $topic;
    }

    // 话题帖子分页
    public function listTopicPost($cate, $topicId, MemberModel $member, $page, $limit)
    {
        $offset = ($page - 1) * $limit;
        PostModel::setWatchUser($member);
        MemberModel::setWatchUser($member);
        return PostModel::listTopicPosts($cate, $topicId, $member->aff, $offset, $limit);
    }


    public function followTopic($aff, $topicId)
    {
        $topic = PostTopicModel::getTopicById($aff, $topicId);
        test_assert($topic,'话题不存在');
        $record = UserPostTopicFollowModel::getRecordByParam($aff, $topicId);
        if (!$record) {
            $data = [
                'aff' => $aff,
                'related_id' => $topicId,
            ];
            UserPostTopicFollowModel::create($data);
            $topic->increment('follow_num');
        } else {
            $record->delete();
            $topic->decrement('follow_num');
        }
        UserPostTopicFollowModel::clearFollowCache($aff);
    }


    public function listTopics()
    {
        return PostTopicModel::listAllTopics();
    }

    // 发布帖子
    public function createPost1(MemberModel $member, $topicId, $categoryId, $content, $title, $medias, $cityname , $ipstr)
    {
        $data = [
            'topic_id' => $topicId,
            'category' => $categoryId,
            'content' => $content,
            'aff' => $member->aff,
            'ipstr' => $ipstr,
            'cityname' => $cityname,
            'refresh_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
            'title' => addslashes(emojiEncode($title)),
            'status' => PostModel::STATUS_WAIT
        ];
        $new = PostModel::create($data);
        test_assert($new, '系统异常,异常码:1001');

        $isFinished = PostModel::FINISH_OK;
        foreach ($medias as $val) {
            $arr = explode('.', $val['media_url']);
            $media = [
                'aff' => $member->aff,
                'relate_type' => PostMediaModel::TYPE_RELATE_POST,
                'pid' => $new->id,
                'media_url' => $val['media_url'],
                'thumb_width' => $val['thumb_width'],
                'thumb_height' => $val['thumb_height'],
                'created_at' => date('Y-m-d H:i:s'),
            ];
            if (isset($val['cover']) && !empty($val['cover'])){
                $media['cover'] = $val['cover'];
            }
            if (end($arr) == 'mp4') {
                $media['type'] = PostMediaModel::TYPE_VIDEO;
                $media['status'] = PostMediaModel::STATUS_NO;
                $isFinished = PostModel::FINISH_NO;
            } else {
                $media['type'] = PostMediaModel::TYPE_IMG;
                $media['status'] = PostMediaModel::STATUS_OK;
            }
            $media = PostMediaModel::create($media);
            if ($media->type == PostMediaModel::TYPE_VIDEO) {
                $new->increment('video_num');
            } else {
                $new->increment('photo_num');
            }
        }
        $new->update(['is_finished' => $isFinished]);
        PostTopicModel::where('id', $topicId)->increment('post_num');
        SysTotalModel::incrBy('add-bbs');
        $member->increment('post_count');
        $member->clearCached();
        return $new ;
    }

    // 发布帖子
    public function createPost(MemberModel $member, $topicId, $content, $title, $cityname , $ipstr, $is_subscribe)
    {
        //解析markdown 获取类型
        $txt = $content;
        $content = PostModel::replaceSym($content);
        $content = \tools\LibMarkdown::loadMarkdown($content);
        $videos = \tools\LibMarkdown::getVideoFromHtml($content);
        $imgs = \tools\LibMarkdown::getImgFromHtml($content);
        $word = \tools\LibMarkdown::getWordFromHtml($content);
        $covers = \tools\LibMarkdown::getCoversFromHtml($content);
        $type = PostModel::TYPE_TXT;//默认文字(帖子类型 1图片 2视频 3文字)
        if (count($imgs) > 0){
            $type = PostModel::TYPE_IMG;
        }
        if (count($videos) > 0){
            $type = PostModel::TYPE_VIDEO;
        }

        $result = transaction(function ()use($member,$topicId,$type,$videos,$imgs,$word,$covers,$ipstr,$cityname,$title,$txt,$is_subscribe){
            $word = trim(str_replace(' ','',$word));
            $data = [
                'topic_id'      => $topicId,
                'category'      => $type,
                'content'       => $txt,
                'content_word'  => $word ?: $title,
                'aff'           => $member->aff,
                'ipstr'         => $ipstr,
                'cityname'      => $cityname,
                'refresh_at'    => date('Y-m-d H:i:s'),
                'created_at'    => date('Y-m-d H:i:s'),
                'title'         => addslashes(emojiEncode($title)),
                'status'        => PostModel::STATUS_WAIT,
                'coins'         => 0,
                'photo_num'     => count($imgs),
                'video_num'     => count($videos),
                'is_subscribe'  => $is_subscribe,
            ];
            $new = PostModel::create($data);
            test_assert($new, '系统异常,异常码:1001');

            $isFinished = PostModel::FINISH_OK;
            //视频
            if (count($videos) > 0){
                $isFinished = PostModel::FINISH_NO;
                collect($videos)->map(function ($video,$key) use( $new, $member, $videos,$covers){
                    if (!str_contains($video, PostMediaModel::getR2Mp4PlayUrl())){
                        //相对路径
                        $video = ltrim(parse_url($video,PHP_URL_PATH),"/");
                    }
                    $media = [
                        'aff' => $member->aff,
                        'type' => PostMediaModel::TYPE_VIDEO,
                        'relate_type' => PostMediaModel::TYPE_RELATE_POST,
                        'pid' => $new->id,
                        'media_url' => $video,
                        'mp4' => $video,
                        'thumb_width' => 0,
                        'thumb_height' => 0,
                        'cover' => parse_url($covers[$key],PHP_URL_PATH),
                        'status' => PostMediaModel::STATUS_NO,
                        'created_at' => date('Y-m-d H:i:s'),
                    ];
                    $is_ok = PostMediaModel::create($media);
                    test_assert($is_ok, '系统异常,异常码:1001');
                });
            }
            //图片
            if (count($imgs) > 0){
                collect($imgs)->map(function ($img) use( $new, $member){
                    $img = parse_url($img,PHP_URL_PATH);
                    $media = [
                        'aff' => $member->aff,
                        'type' => PostMediaModel::TYPE_IMG,
                        'relate_type' => PostMediaModel::TYPE_RELATE_POST,
                        'pid' => $new->id,
                        'media_url' => $img,
                        'thumb_width' => 0,
                        'thumb_height' => 0,
                        'cover' => '',
                        'status' => PostMediaModel::STATUS_OK,
                        'created_at' => date('Y-m-d H:i:s'),
                    ];

                    $is_ok = PostMediaModel::create($media);
                    test_assert($is_ok, '系统异常,异常码:1001');
                });
            }

            $new->update(['is_finished' => $isFinished]);
        });

        SysTotalModel::incrBy('add-bbs');
        $member->clearCached();
        return $result;
    }

    // 帖子列表
    public function listPosts(MemberModel $member, $cate, $cateSec, $page, $limit)
    {
        $offset = ($page - 1) * $limit;
        PostModel::setWatchUser($member);
        switch ($cate) {
            case 'recommend':
                $posts = PostModel::listRecommendTopicPosts($cateSec, $member->aff, $offset, $limit);
                break;
            default:
                $posts = PostModel::listClubPosts($member->aff, $offset, $limit);
                break;
        }

        return $posts;
    }

    // 获取帖子详情
    public function getPostDetail(MemberModel $member, $postId)
    {
        PostModel::setWatchUser($member);
        $post = PostModel::getPostById($member->aff, $postId);
        test_assert($post , '此贴已被删除');

        $content = PostModel::replaceSym($post->content);
        $content = \tools\LibMarkdown::loadMarkdown($content);
        $content = PostModel::symReplace($content);
        if ($post->user) {
            $hasClub = $post->user->post_club_month
                + $post->user->post_club_quarter
                + $post->user->post_club_year;
            if ($post->user->aff != $member->aff && $hasClub > 0 && $post->is_fans == 0 && $post->category == PostModel::TYPE_VIDEO){
                if (version_compare($_POST['version'], '2.5.0', '<=')){
                    //视频地址替换为空
                    preg_replace_callback('/<video[^<>]*src=[\"]([^\"]+)[\"][^<>]*>/im',
                        function ($match) use(&$content){
                            $content = str_replace($match[1],'',$content);
                        }, $content);
                }else{
                    if ($post->is_subscribe == PostModel::SUBSCRIBE_YES){
                        //视频地址替换为空
                        preg_replace_callback('/<video[^<>]*src=[\"]([^\"]+)[\"][^<>]*>/im',
                            function ($match) use(&$content){
                                $content = str_replace($match[1],'',$content);
                            }, $content);
                    }
                }

            }
            unset($post->user->share);
        }

        $post->content = $content;
        //分享连接
        $shareText = setting('post_detail_share_text', '');
        $shareText = str_replace("{title}", $post->title, $shareText);
        $post->shareText = $shareText;

        $key = "post:rating:key:%d";
        $key = sprintf($key, $postId);
        $val = redis()->incrBy($key, 1);
        $val = intval($val);
        if ($val >= rand(50, 60)){
            //浏览数
            jobs([PostModel::class, 'incrByViewNum'], [$post->id, $val]);
            jobs([PostTopicModel::class, 'incrByViewNum'], [$post->topic_id, $val]);
            redis()->del($key);
        }
        return $post;
    }

    // 搜索
    public function listSearchPost($word, MemberModel $member, $page, $limit)
    {
        PostModel::setWatchUser($member);
        $offset = ($page - 1) * $limit;
        return PostModel::listPostBySearch($word, $member->aff, $offset, $limit);
    }

    public function getSearchCount($word)
    {
        return PostModel::getSearchCount($word);
    }


    // 帖子点赞/取消点赞
    protected function likePost($aff, $postId)
    {
        $post = PostModel::getPostById($aff, $postId);
        test_assert($post,'此帖子不存在');

        $record = UserCommunityLikeModel::getIdsById(UserCommunityLikeModel::TYPE_POST, $aff, $postId);
        if (!$record) {
            $data = [
                'aff' => $aff,
                'type' => UserCommunityLikeModel::TYPE_POST,
                'related_id' => $postId,
            ];
            UserCommunityLikeModel::create($data);
            $post->increment('like_num', 1, ['hot_sort' => \DB::raw('hot_sort + 1')]);
        } else {
            $record->delete();
            $post->decrement('like_num', 1, ['hot_sort' => \DB::raw('hot_sort - 1')]);
        }
        UserCommunityLikeModel::clearCacheByAff(UserCommunityLikeModel::TYPE_POST, $aff);
    }

    // 评论点赞/取消点赞
    protected function likeComment($aff, $commentId)
    {
        $comment = PostCommentModel::getCommentById($aff, $commentId);
        test_assert($comment,'此评论不存在');
        $record = UserCommunityLikeModel::getIdsById(UserCommunityLikeModel::TYPE_COMMENT, $aff, $commentId);
        if (!$record) {
            $data = [
                'aff' => $aff,
                'type' => UserCommunityLikeModel::TYPE_COMMENT,
                'related_id' => $commentId,
            ];
            UserCommunityLikeModel::create($data);
            $comment->increment('like_num');
        } else {
            $record->delete();
            $comment->decrement('like_num');
        }
        UserCommunityLikeModel::clearCacheByAff(UserCommunityLikeModel::TYPE_COMMENT, $aff);
    }

    // 帖子或者评论点赞
    public function like($type, $aff, $id)
    {
        switch ($type) {
            case 'post':
                $this->likePost($aff, $id);
                break;
            case 'comment':
                $this->likeComment($aff, $id);
                break;
        }
    }

    // 帖子收藏/取消收藏
    public function favorite(MemberModel $member, $postId)
    {
        $post = PostModel::getPostById($member->aff, $postId);
        test_assert($post , '此帖子不存在');

        $record = UserFavoritesLogModel::where('aff', $member->aff)
            ->where('type', UserFavoritesLogModel::TYPE_POST)
            ->where('related_id', $postId)
            ->first();
        if (!$record) {
            $data = [
                'type' => UserFavoritesLogModel::TYPE_POST,
                'aff' => $member->aff,
                'related_id' => $postId
            ];
            UserFavoritesLogModel::create($data);
            $post->increment('favorite_num', 1, ['hot_sort' => \DB::raw('hot_sort + 1')]);
        } else {
            $record->delete();
            $post->decrement('favorite_num', 1, ['hot_sort' => \DB::raw('hot_sort - 1')]);
        }
        //清除一下缓存
        cached(sprintf(UserFavoritesLogModel::USER_POST_FAVORITE_LIST,$member->aff))->clearCached();
    }

    public function listFavoritePosts(MemberModel $member, $page, $limit)
    {
        PostModel::setWatchUser($member);
        $ids = UserFavoritesLogModel::where(['aff' => $member->aff])
            ->where('type', UserFavoritesLogModel::TYPE_POST)
            ->orderByDesc('id')
            ->forPage($page , $limit)
            ->get()
            ->pluck('related_id')
            ->toArray();

        return PostModel::listFavoritedPosts($member->aff, $ids);
    }

    public function listCommentsByPostId(MemberModel $member, $postId, $page, $limit, $version)
    {
        $post = PostModel::getPostById($member->aff, $postId);
        test_assert($post,'此帖子不存在');

        $offset = ($page - 1) * $limit;
        return PostCommentModel::listCommentsByPostId($member, $postId, $post->aff, $offset, $limit,$version);
    }

    public function listCommentsByCommentId(MemberModel $member, $commentId, $page, $limit)
    {
        $comment = PostCommentModel::getCommentById($member->aff, $commentId);
        test_assert($comment,'此评论不存在');

        $post = PostModel::getPostById($member->aff, $comment->post_id);
        test_assert($comment,'此帖子不存在');

        $offset = ($page - 1) * $limit;
        return PostCommentModel::listCommentsByCommentId($member->aff, $comment->id, $comment->post_id, $post->aff, $offset, $limit);
    }

    public function createComComment($commentId, MemberModel $member, $nickname, $content, $medias, $cityname, $status, $refuseReason, $parentComment)
    {
        $aff = $member->aff;
        if ($parentComment->ads_url){
            throw new \Exception('此评论不能回复');
        }

        $sec_parent = 0;
        $pid = $parentComment->id;
        if ($parentComment->pid){
            $pid = $parentComment->pid;
            $sec_parent = $commentId;
        }

        $data = [
            'post_id' => $parentComment->post_id,
            'pid' => $pid,
            'aff' => $aff,
            'reply_aff' => $parentComment->aff,
            'comment' => $content,
            'status' => $status,
            'refuse_reason' => $refuseReason,
            'ipstr' => USER_IP,
            'cityname' => $cityname,
            'author' => $member->nickname,
            'created_at' => date('Y-m-d H:i:s'),
            'sec_parent' => $sec_parent,
        ];
        $comment = PostCommentModel::create($data);
        test_assert($comment,'系统异常,异常码:1001');

        $isFinished = PostCommentModel::FINISH_OK;
        foreach ($medias as $val) {
            $arr = explode('.', $val['media_url']);
            $media = [
                'aff' => $aff,
                'relate_type' => PostMediaModel::TYPE_RELATE_COMMENT,
                'pid' => $comment->id,
                'media_url' => $val['media_url'],
                'thumb_width' => $val['thumb_width'] ?? 0,
                'thumb_height' => $val['thumb_height'] ?? 0,
                'created_at' => date('Y-m-d H:i:s'),
            ];
            if (end($arr) == 'mp4') {
                $media['type'] = PostMediaModel::TYPE_VIDEO;
                $media['status'] = PostMediaModel::STATUS_NO;
                $isFinished = PostCommentModel::FINISH_NO;
            } else {
                $media['type'] = PostMediaModel::TYPE_IMG;
                $media['status'] = PostMediaModel::STATUS_OK;
            }
            $media = PostMediaModel::create($media);
            if ($media->type == PostMediaModel::TYPE_VIDEO) {
                $comment->increment('video_num');
            } else {
                $comment->increment('photo_num');
            }
        }
        $comment->update(['is_finished' => $isFinished]);

        // 通知
        if ($status == PostCommentModel::STATUS_PASS) {
            // 通过通知
            $msg = sprintf(SystemNoticeModel::AUDIT_COMMENT_PASS_MSG, $comment->comment);
            $model = SystemNoticeModel::addNotice($comment->aff, $msg, '审核消息');
            if (!$model)
                throw new \Exception('系统异常');

            // 通知被评论人
            $msg = sprintf(SystemNoticeModel::COMMENT_COMMENT_MSG, $nickname, $parentComment->comment, $comment->comment);
            $model = SystemNoticeModel::addNotice($parentComment->aff, $msg, '评论消息');
            if (!$model)
                throw new \Exception('系统异常');

            PostCommentModel::clearCacheWhenCreateComment($commentId);
        } else {
            // 失败通知
//            $msg = sprintf(\SystemNoticeModel::AUDIT_COMMENT_UNPASS_MSG, $comment->comment, $refuseReason);
//            $model = \SystemNoticeModel::addNotice($comment->aff, $msg, '审核消息');
//            if (!$model)
//                throw new \Exception('系统异常');
        }
    }

    public function createPostComment($id, MemberModel $member, $nickname, $content, $medias, $cityname, $status, $refuseReason, $post)
    {
        $aff = $member->aff;

        $data = [
            'post_id' => $post->id,
            'pid' => 0,
            'aff' => $aff,
            'reply_aff' => 0,
            'comment' => $content,
            'status' => $status,
            'refuse_reason' => $refuseReason,
            'ipstr' => USER_IP,
            'cityname' => $cityname,
            'author' => $member->nickname,
            'created_at' => date('Y-m-d H:i:s'),
        ];
        $comment = PostCommentModel::create($data);
        test_assert($comment , '系统异常,异常码:1001');
//        $post->increment('comment_num');

        $isFinished = PostCommentModel::FINISH_OK;
        foreach ($medias as $val) {
            $arr = explode('.', $val['media_url']);
            $media = [
                'aff' => $aff,
                'relate_type' => PostMediaModel::TYPE_RELATE_COMMENT,
                'pid' => $comment->id,
                'media_url' => $val['media_url'] ? parse_url($val['media_url'],PHP_URL_PATH) : '',
                'thumb_width' => $val['thumb_width'] ?? 0,
                'thumb_height' => $val['thumb_height'] ?? 0,
                'cover' => $val['cover'] ? parse_url($val['cover'],PHP_URL_PATH) : '',
                'created_at' => date('Y-m-d H:i:s'),
            ];
            if (end($arr) == 'mp4') {
                $media['type'] = PostMediaModel::TYPE_VIDEO;
                $media['status'] = PostMediaModel::STATUS_NO;
                $isFinished = PostCommentModel::FINISH_NO;
            } else {
                $media['type'] = PostMediaModel::TYPE_IMG;
                $media['status'] = PostMediaModel::STATUS_OK;
            }
            $media = PostMediaModel::create($media);
            if ($media->type == PostMediaModel::TYPE_VIDEO) {
                $comment->increment('video_num');
            } else {
                $comment->increment('photo_num');
            }
        }
        $comment->update(['is_finished' => $isFinished]);

        // 通知
        if ($status == PostCommentModel::STATUS_PASS) {
            // 通过通知
            $msg = sprintf(SystemNoticeModel::AUDIT_COMMENT_PASS_MSG, $comment->comment);
            $model = SystemNoticeModel::addNotice($comment->aff, $msg, '审核消息');
            test_assert($model , '系统异常');

            // 通知帖子作者
            $msg = sprintf(SystemNoticeModel::COMMENT_POST_MSG, $nickname, $post->title, $comment->comment);
            $model = SystemNoticeModel::addNotice($post->aff, $msg, '评论消息');
            test_assert($model , '系统异常');
            // 清理帖子缓存
            PostCommentModel::clearCacheWhenCreatePostComment($post->id);
        } else {
            // 失败通知
//            $msg = sprintf(\SystemNoticeModel::AUDIT_COMMENT_UNPASS_MSG, $comment->comment, $refuseReason);
//            $model = \SystemNoticeModel::addNotice($comment->aff, $msg, '审核消息');
//            test_assert($model , '系统异常');
        }
    }

    public function listMyPosts(MemberModel $member, $cate, $page, $limit)
    {
        return PostModel::listMyPosts($member->aff, $cate, $page, $limit);
    }

    public function listMyPostsNew(MemberModel $member, $cate, $is_subscribe, $page, $limit)
    {
        $pass = 0;
        $wait = 0;
        $unpass = 0;
        $wait_release = 0;
        if ($page == 1){
            //帖子数量
            $pass = PostModel::listMyPostsCount($member->aff, PostModel::STATUS_PASS, $is_subscribe);
            $wait = PostModel::listMyPostsCount($member->aff, PostModel::STATUS_WAIT, $is_subscribe);
            $unpass = PostModel::listMyPostsCount($member->aff, PostModel::STATUS_UNPASS, $is_subscribe);
            $wait_release = PostModel::listMyPostsCount($member->aff, 'wait_release', $is_subscribe);
        }
        $list = PostModel::listMyPostsNew($member->aff, $cate, $is_subscribe, $page, $limit);
        return [
            'list' => $list,
            'pass_ct' => $pass,
            'wait_ct' => $wait,
            'unpass_ct' => $unpass,
            'wait_release_ct' => $wait_release,
        ];
    }

    public function getPostCludInfo(MemberModel $member){
        $club = PostClubsModel::findByAff($member->aff);
        if (empty($club)) {
            $club = PostClubsModel::make();
            $club->aff = $member->aff;
        }
        $club->total_income = $club->year_income + $club->month_income + $club->quarter_income;
        $club->money = $member->money;

        return $club;
    }

    public function getPeerInfo(MemberModel $member, $peerAff)
    {
        $peerMember = MemberModel::findByAff($peerAff);
        test_assert($peerMember,'此用户不存在');
        $peerMember->is_fans = 0;
        if ($member->aff != $peerAff){
            //是否订阅
            $postClubMembers = PostClubMembersModel::findByAffClubId($member->aff , $peerMember->post_club_id);
            if ($postClubMembers && $postClubMembers->expired_at >= TIMESTAMP){
                $peerMember->is_fans = 1;
            }
        }
        //评分
        $score_record = MemberPostScoreModel::findByAff($member->aff, $peerAff);
        $creator = PostCreatorModel::findByAff($peerAff);
        $work_score = '4.0';
        if ($creator){
            $work_score = $creator->work_score > 0 ? number_format($creator->work_score, 1) : '4.0';
        }

        //总订阅量 总帖子数
        $postClub = PostClubsModel::findByAff($peerAff);
        if ($postClub){
            $peerMember->post_num = $postClub->post_num;
        }else{
            $peerMember->post_num = $peerMember->post_count;
        }
        $result = $peerMember->toArray();
        unset($result['share']);
        unset($result['invited_by']);
        $tmp = [
            'post_club_month'      => $postClub ? $postClub->month : 0,
            'post_club_quarter'    => $postClub ? $postClub->quarter : 0,
            'post_club_year'       => $postClub ? $postClub->year : 0,
            'post_club_number_num' => $postClub ? $postClub->member_num : 0,
            'is_score'             => $score_record ? 1 : 0,
            'work_score'           => $work_score,
        ];


        return array_merge($result, $tmp);
    }

    public function listPeerPosts(MemberModel $member, $peerAff, $page, $limit)
    {
        $offset = ($page - 1) * $limit;
        return PostModel::listPeerPosts($member->aff, $peerAff, $offset, $limit);
    }

    public function listMyComments(MemberModel $member, $page, $limit){
        return PostCommentModel::listMyComments($member->aff,$page, $limit);
    }

    // 结构详情
    public function list_construct(MemberModel $member, $category_id, $type, $sort, $page, $limit)
    {
        PostModel::setWatchUser($member);
        MemberModel::setWatchUser($member);
        $banner = CommonService::getAds($member, \AdsModel::POS_POST_RECOMMEND);
        $topics = [];
        $list = [];
        switch ($type) {
            case 'recommend':
                //获取推荐的分类
                $topics = PostTopicModel::listByRecommend();
                if (collect($topics)->isNotEmpty()){
                    $topic_ids = collect($topics)->map(function ($item){
                        return $item->id;
                    })->values()->toArray();
                    $list = PostModel::listRecommend($sort, $topic_ids, $page, $limit);
                }
                break;
            case 'subscription':
                $list = PostModel::listClubPosts($member->aff, ($page - 1) * $limit, $limit);
                break;
            case 'category':
                $topics = PostTopicModel::listTopicsByCategory($category_id);
                if (collect($topics)->isNotEmpty()){
                    $topic_ids = collect($topics)->map(function ($item){
                        return $item->id;
                    })->values()->toArray();
                    $list = PostModel::listCategory($category_id, $sort, $topic_ids, $page, $limit);
                }
                break;
            case 'follow':
                $key = \MemberFollowModel::generateId($member->aff);
                $affs = redis()->sMembers($key);
                if ($affs){
                    $list = PostModel::listFollow($affs, $page, $limit);
                }
                break;
            default:
                test_assert(false, '类型错误');
                break;
        }

        return [
            'banner' => $banner,
            'topics' => $topics,
            'list'   => $list,
        ];
    }

    // 结构详情
    public function score(MemberModel $member, $aff, $score)
    {
        test_assert($member->aff != $aff, '自己不能对自己评分');
        $creator = PostCreatorModel::findByAff($aff);
        test_assert($creator && $creator->status == PostCreatorModel::STATUS_OK, '此用户不是博主');
        $club = PostClubMembersModel::findByAffClubAff($member->aff, $aff);
        test_assert($club->expired_at > TIMESTAMP, '仅订阅用户或购买作品用户可评分');
        $post_score = MemberPostScoreModel::findByAff($member->aff, $aff);
        test_assert(empty($post_score), '此博主不能重复评分');
        transaction(function () use ($creator, $club, $member, $score){
            $init_count = 100 + 1;
            $init_score = 400 + $score;
            //获取评分
            $total_score = MemberPostScoreModel::sumScore($club->aff) + $init_score;
            $total_count = MemberPostScoreModel::countAff($club->aff) + $init_count;
            $final_score = round($total_score / $total_count, 2);
            $creator->work_score = $final_score;
            $isOk = $creator->save();
            test_assert($isOk, '评分失败，请重试');
            $post_score = MemberPostScoreModel::make();
            $post_score->aff = $member->aff;
            $post_score->to_aff = $creator->aff;
            $post_score->score = $score;
            $post_score->created_at = \Carbon\Carbon::now();
            $post_score->updated_at = \Carbon\Carbon::now();
            $isOk = $post_score->save();
            test_assert($isOk, '评分失败，请重试');
        });
    }

    public function listPeerPostsNew(MemberModel $member, $peerAff, $sort, $page, $limit)
    {
        return PostModel::listPeerPostsNew($peerAff, $sort, $page, $limit);
    }

    public function listCommentsByPostIdV1(MemberModel $member, $postId, $page, $limit)
    {
        $post = PostModel::getPostById($member->aff, $postId);
        test_assert($post,'此帖子不存在');

        PostCommentModel::setWatchUser($member);
        if ($page == 1){
            $list = PostCommentModel::list_first($postId, $post->aff, 1, $limit);
        }else{
            $coids = PostCommentModel::list_first_ids($postId);
            if (count($coids) < $limit){
                return [];
            }
            $list = PostCommentModel::list_comments($postId, $coids, $post->aff, $page, $limit);
        }
        return collect($list)->map(function ($item) use ($post){
            if ($item->reply_ct > 0){
                $item->comments = PostCommentModel::fir_sec_list($post->id, $item->id, $post->aff);
            }
            return $item;
        });
    }

    public function listCommentsByCommentIdV1(MemberModel $member, $commentId, $page, $limit)
    {
        $comment = PostCommentModel::getCommentById($member->aff, $commentId);
        test_assert($comment,'此评论不存在');

        $post = PostModel::getPostById($member->aff, $comment->post_id);
        test_assert($post,'此帖子不存在');

        PostCommentModel::setWatchUser($member);

        return PostCommentModel::list_replys($comment->post_id, $commentId, $post->aff, $page, $limit);
    }


    public function like_comment(MemberModel $member, $comment_id){
        $comment = PostCommentModel::find($comment_id);
        test_assert($comment, '评论不存在');
        transaction(function () use ($member, $comment_id){
            /** @var PostCommentsLikeModel $like */
            $like = PostCommentsLikeModel::query()
                ->where('cid', $comment_id)
                ->where('aff', $member->aff)->first();
            if (empty($like)) {
                $like = PostCommentsLikeModel::create([
                    'aff'        => $member->aff,
                    'cid'        => $comment_id,
                ]);
                test_assert($like , '添加点赞数据失败');
                jobs([PostCommentModel::class, 'incrementLikeNum'], [$comment_id]);
                redis()->sAdd(sprintf(PostCommentsLikeModel::POST_COMMENTS_LIKE, $member->aff), $comment_id);
            } else {
                test_assert($like->delete() , '清理点赞数据失败');
                jobs([PostCommentModel::class, 'decrementLikeNum'], [$comment_id]);
                redis()->sRem(sprintf(PostCommentsLikeModel::POST_COMMENTS_LIKE, $member->aff), $comment_id);
            }
        });
    }

    // 打赏
    public function reward(MemberModel $member, $postId, $amount)
    {
        $post = PostModel::getPostById($member->aff, $postId);
        test_assert($post, '此帖子不存在');
        test_assert((int)$post->aff != (int)$member->aff, '不能打赏自己');
        $peer = \MemberModel::firstAff($post->aff);
        test_assert($peer, '打赏的用户不存在');
        test_assert($member->money >= $amount, '金币不足');

        $data = [
            'aff' => $member->aff,
            'post_id' => $post->id,
            'post_aff' => $post->aff,
            'amount' => $amount,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
        return transaction(function () use ($member, $peer, $post, $amount, $data) {
            # 减少本人钻石
            $description = '打赏帖子:' . $post->id;
            $isOk = $member->subMoney($amount, \MoneyLogModel::SOURCE_REWARD_POST, $description, $post);
            test_assert($isOk , '扣款失败');
            # 增加对方可提现的收益
            $isOk = $peer->addIncome($amount, $member, $post, \MoneyIncomeLogModel::SOURCE_POST_REWARD, $description);
            test_assert($isOk, '添加用户收益失败');
            # 日志记录
            $itOK = \PostRewardLogModel::create($data);
            test_assert($itOK, '打赏日志记录失败');
            # 打赏记录维护
            $itOK = $post->increment('reward_num', 1, ['reward_amount' => \DB::raw('reward_amount + ' . $amount)]);
            test_assert($itOK, '打赏统计失败');
            # 发布通知
            //PostModel::clearDetailCache($post->id);
            //$msg = sprintf(\SystemNoticeModel::POST_REWARD_MSG, $member->nickname, $post->title, $amount);
            //\SystemNoticeModel::addNotice($peer->aff, $msg, '打赏消息');
            return true;
        });
    }
}