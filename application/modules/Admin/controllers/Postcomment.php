<?php

class PostcommentController extends BackendBaseController
{

    use \repositories\HoutaiRepository;
    use \repositories\HoutaiRepository {
        doSave as fatherSave;
    }

    /**
     * 列表数据过滤
     * @return Closure
     */
    protected function listAjaxIteration()
    {
        $handle = SensitiveWordsModel::sensitiveHandle();
        return function (PostCommentModel $item) use($handle) {
            if ($item->comment && $handle->islegal($item->comment)){
                $item->comment = $handle->mark($item->comment, '<mark>', '</mark>');
            }
            $item->admin_str = '';
            if ($item->manager){
                $item->admin_str = $item->manager->username;
            }
            $item->status_str = PostCommentModel::STATUS_TIPS[$item->status];
            // 添加状态CSS类
            $statusClassMap = [
                PostCommentModel::STATUS_WAIT => 'status-wait',
                PostCommentModel::STATUS_PASS => 'status-pass',
                PostCommentModel::STATUS_UNPASS => 'status-unpass',
            ];
            $item->status_class = $statusClassMap[$item->status] ?? '';
            $item->finish_str = PostCommentModel::FINISH_TIPS[$item->is_finished];
            $imgs = [];
            $videos = [];
            foreach ($item->medias as $v) {
                if ($item->ads_url){
                    $item->ads_img = $v->media_url;
                    $item->ads_img_w = $v->thumb_width;
                    $item->ads_img_h = $v->thumb_height;
                }
                if ($v->type == PostMediaModel::TYPE_IMG) {
                    $imgs[] = $v;
                }
                if ($v->type == PostMediaModel::TYPE_VIDEO) {
                    $videos[] = $v;
                }
            }
            $item->imgs = $imgs;
            $item->videos = $videos;
            $item->top_str = PostCommentModel::TOP_TIPS[$item->is_top];
            return $item;
        };
    }

    protected function doSave($data)
    {
        // 编辑框的
        if (!isset($data['aff']) || !isset($data['post_id'])) {
            return $this->fatherSave($data);
        }

        $aff = $data['aff'] = (int)$data['aff'];
        $member = MemberModel::findByAff($aff);
        test_assert($member, '未找到用户');

        $post_id = $data['post_id'] = (int)$data['post_id'];
        $post = PostModel::where('id',$post_id)->where('status',PostModel::STATUS_PASS)->where('is_finished',PostModel::FINISH_OK)->where('is_deleted',PostModel::DELETED_NO);
        test_assert($post, '未找到帖子');

        return transaction(function () use ($data,$member) {
            /** @var PostCommentModel $model */
            $model = $this->fatherSave($data);

            //只有广告评论才处理 媒体文件
            if ($model->ads_url){
                PostMediaModel::where('pid', $model->id)
                    ->where('relate_type', PostMediaModel::TYPE_RELATE_COMMENT)
                    ->get()
                    ->map(function ($item) {
                        $isOk = $item->delete();
                        test_assert($isOk, '删除数据异常');
                    });

                //图片
                $tmp = [
                    'aff'          => $data['aff'],
                    'cover'        => '',
                    'thumb_width'  => $data['ads_img_w'],
                    'thumb_height' => $data['ads_img_h'],
                    'duration'     => 0,
                    'pid'          => $model->id,
                    'media_url'    => trim(parse_url($data['ads_img'], PHP_URL_PATH), '/'),
                    'relate_type'  => PostMediaModel::TYPE_RELATE_COMMENT,
                    'status'       => PostMediaModel::STATUS_OK,
                    'type'         => PostMediaModel::TYPE_IMG,
                ];
                $isOk = PostMediaModel::create($tmp);
                test_assert($isOk, '保存图片资源异常');
            }

            $model->is_finished = PostModel::FINISH_OK;
            $model->author = $member->nickname;
            $model->photo_num = 1;
            $isOk = $model->save();
            test_assert($isOk, '更新状态错误');

            // 更新帖子数据
            if ($model->status == PostCommentModel::STATUS_PASS){
                PostCommentModel::clearCache($model);
                $isOk = PostModel::where('id', $model->post_id)->where('status', PostModel::STATUS_PASS)->increment('comment_num');
                test_assert($isOk, '更新主题帖子计数异常');
            }

            return $model;
        });
    }

    /**
     * 试图渲染
     * @return void
     */
    public function indexAction()
    {
        $hour = date('H');
        $showLike = true;
//        if (in_array($hour,[20,21,22,23])){
//            $showLike = false;
//        }

        //拒绝原因
        $refuseReason = setting('comment_refuse_reason','');
        $refuseReason = explode("\r\n", $refuseReason);
        $refuseList = [];
        foreach ($refuseReason as $v){
            $refuseList[$v] = $v;
        }

        $this->assign('refuseReason', $refuseList);
        $this->assign('default_status',PostCommentModel::STATUS_WAIT);
        $this->assign('showLike',$showLike);
        $this->assign('postId', $_GET['post_id'] ?? '');
        $this->assign('pid', $_GET['pid'] ?? '');
        $this->display();
    }

    /**
     * @description 批量拒绝
     */
    public function batch_refuseAction(): bool
    {
        try {
            $content = $_POST['content'] ?? null;
            $ids = $_POST['ids'] ?? '';
            $commentIds = explode(',', $ids);

            if (empty($content)) {
                return $this->ajaxError('请选择拒绝原因');
            }
            if (!$commentIds) {
                return $this->ajaxError('评论ID不能为空');
            }

            //过滤已经审核通过的
            $comments = PostCommentModel::query()->whereIn('id',$commentIds)->where('status',PostCommentModel::STATUS_WAIT)->get();
            if (!$comments){
                return $this->ajaxError('没有待审核的评论');
            }

            foreach ($comments as $comment) {
                $data = [
                    'status'       => PostCommentModel::STATUS_UNPASS,
                    'refuse_reason'     => $content,
                    'updated_at' => \Carbon\Carbon::now(),
                    'admin_id' => $this->getUser()->uid
                ];
                $comment->update($data);
            }
            return $this->ajaxSuccess('拒绝成功');
        }catch (Exception $e){
            return $this->ajaxError($e->getMessage());
        }
    }


    /**
     * 获取本控制器和哪个model绑定
     * @return string
     */
    protected function getModelClass(): string
    {
        return PostCommentModel::class;
    }

    protected function getModelObject()
    {
        return PostCommentModel::with(['medias' => function($q){
            $q->where('relate_type',PostMediaModel::TYPE_RELATE_COMMENT);
        },'manager']);
    }

    protected function getSearchWhereParam()
    {
        $get = $this->getRequest()->getQuery();
        $get['where'] = $get['where'] ?? [];
        $where = [];
        foreach ($get['where'] as $key => $value) {
            if ($value === '__undefined__') {
                continue;
            }
            $value = $this->formatSearchVal($key, $value);

            list($key , $value) = $this->formatKey($key,$value);
            if (empty($key)) {
                continue;
            }
            if ($value !== '' && !in_array($key, ['post_title', 'type'])) {
                $where[] = [$key, '=', $value];
            }

            if ($key == 'post_title') {
                $ids = PostModel::query()->where('title', $value)->get()->pluck('id')->toArray();
                $ids = $ids ? implode(",", $ids) : '0';
                $where[] = [\DB::raw("post_id in ($ids)"),'1'];
            }
            if ($key == 'type'){
                if ($value == 3){
                    $where[] = ['video_num', '=', 0];
                    $where[] = ['photo_num', '=', 0];
                }elseif ($value == 1){
                    $where[] = ['photo_num', '>', 0];
                }elseif ($value == 2){
                    $where[] = ['video_num', '>', 0];
                }
            }
        }

        return $where;
    }

    /**
     * 定义数据操作的表主键名称
     * @return string
     */
    protected function getPkName(): string
    {
        return 'id';
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

    protected function saveAfterCallback($model, $oldModel = null)
    {
        PostCommentModel::clearCache($model);
    }

//    protected function deleteAfterCallback($model, $isDelete)
//    {
//        if ($model->status == PostCommentModel::STATUS_PASS){
//            PostCommentModel::clearCache($model);
//            $post = PostModel::find($model->post_id);
//            if ($post && $post->comment_num > 0){
//                $post->decrement('comment_num');
//            }
//        }
//    }

    public function delAction()
    {
        $_POST['value'] = $_POST['_pk'];
        return $this->delAllAction();
    }

    public function delAllAction()
    {
        $id = $_POST['value'] ?? '';
        $idAry = explode(',', $id);

        PostCommentModel::useWritePdo()
            ->whereIn('id', $idAry)
            ->get()
            ->each(function (PostCommentModel $item) {
                if ($item->status == PostCommentModel::STATUS_PASS) {
                    $post = PostModel::find($item->post_id);
                    if ($post && $post->comment_num > 0){
                        PostCommentModel::clearCache($item);
                        $post->decrement('comment_num');
                    }
                }
                $item->delete();
            });

        return $this->ajaxSuccessMsg('操作成功');
    }

    public function pass_allAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->ajaxError('请求错误');
        }
        $post = $this->postArray();
        $ary = explode(',', $post['value'] ?? '');
        $ary = array_filter($ary);
        // 允许待审核和未通过（自动过滤）状态的评论被通过
        $comments = PostCommentModel::whereIn('id', $ary)
            ->whereIn('status', [PostCommentModel::STATUS_WAIT, PostCommentModel::STATUS_UNPASS])
            ->get();

        try {
            transaction(function () use ($comments) {
                /** @var PostCommentModel $comment */
                foreach ($comments as $comment) {
                    $ret = PostCommentModel::where('id', $comment->id)
                        ->update(
                            [
                                'status' => PostCommentModel::STATUS_PASS,
                                'admin_id' => $this->getUser()->uid,
                            ]);
                    if ($ret <= 0)
                        throw new Exception('系统异常');

                    $postId = $comment->post_id;
                    $post = PostModel::find($postId);
                    if (!$post){
                        continue;
                        //throw new Exception('系统异常');
                    }
                    $post->increment('comment_num');

                    $medias = PostMediaModel::getMakeSliceList($comment->id,PostMediaModel::TYPE_RELATE_COMMENT);
                    if ($medias){
                        //全部改为转换中
                        PostMediaModel::updateSliceStatus($comment->id,PostMediaModel::TYPE_RELATE_COMMENT);
                        //发起切片
                        PostMediaModel::makeAndSlice($medias);
                    }

                    // 对评论人通知过审
                    $msg = sprintf(SystemNoticeModel::AUDIT_COMMENT_PASS_MSG, $comment->comment);
                    $model = SystemNoticeModel::addNotice($comment->aff, $msg, '审核消息');
                    if (!$model)
                        throw new Exception('系统异常');

                    // 对上级通知评论
                    switch ($comment->pid) {
                        case 0:
                            // 对帖子作者通知评论
                            $nickname = MemberModel::firstAff($comment->aff)->nickname;
                            $post = PostModel::where('id', $comment->post_id)->first();
                            $autherAff = $post->aff;
                            $postTitle = $post->title;
                            $msg = sprintf(SystemNoticeModel::COMMENT_POST_MSG, $nickname, $postTitle, $comment->comment);
                            $model = SystemNoticeModel::addNotice($autherAff, $msg, '评论消息');
                            if (!$model)
                                throw new Exception('系统异常');
                            break;
                        default:
                            // 对评论人通知评论
                            $nickname = MemberModel::firstAff($comment->aff)->nickname;
                            $tcomment = PostCommentModel::where('id', $comment->pid)->first();
                            $commentTitle = $tcomment->comment;
                            $autherAff = $tcomment->aff;
                            $msg = sprintf(SystemNoticeModel::COMMENT_COMMENT_MSG, $nickname, $commentTitle, $comment->comment);
                            $model = SystemNoticeModel::addNotice($autherAff, $msg, '评论消息');
                            if (!$model)
                                throw new Exception('系统异常');
                            break;
                    }

                    //清理缓存
                    PostModel::clearDetailCache($postId);
                    PostCommentModel::clearCache($comment);
                }
            });
            return $this->ajaxSuccessMsg('操作成功');
        } catch (Exception $e) {
            return $this->ajaxError($e->getMessage());
        }
    }

    /**
     * @description 置顶/取消置顶
     */
    public function topSetAction(){
        try {
            if (!$this->getRequest()->isPost())
                throw new Exception('数据异常');
            $data = $this->postArray();
            $id = $data['id'];
            $comment = PostCommentModel::find($id);
            test_assert($comment,'评论不存在');
            if ($comment->is_top == 0){
                test_assert($comment->pid == 0,'二级评论不能置顶');
                $official = OfficialAccountModel::where('aff', $comment->aff)->first();
                test_assert($official, '非官方账号不能置顶');
            }
            $comment->is_top = $comment->is_top == 1 ? 0 : 1;
            $comment->save();

            return $this->ajaxSuccessMsg('操作成功');
        }catch (Exception $e){
            return $this->ajaxError($e->getMessage());
        }
    }
}