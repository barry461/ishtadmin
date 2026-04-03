<?php

class PostController extends BackendBaseController
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
        return function (PostModel $item) use($handle) {
            if ($item->content_word && $handle->islegal($item->content_word)){
                $item->content_word = $handle->mark($item->content_word, '<mark>', '</mark>');
            }
            if ($item->title && $handle->islegal($item->title)){
                $item->title = $handle->mark($item->title, '<mark>', '</mark>');
            }
            $item->admin_str = '';
            if ($item->manager){
                $item->admin_str = $item->manager->username;
            }
            $item->bast_str = PostModel::BEST_TIPS[$item->is_best] ?? '未置精';
            $item->title = trim($item->title);
            $item->deleted_str = PostModel::DELETED_TIPS[$item->is_deleted] ?? '';
            $item->finish_str = PostModel::FINISH_TIPS[$item->is_finished] ?? '';
            $item->status_str = PostModel::STATUS_TIPS[$item->status] ?? '';
            $item->subscribe_str = PostModel::SUBSCRIBE_TIPS[$item->is_subscribe] ?? '';
            $item->category_str = PostModel::TYPE_TIPS[$item->category] ?? '';
            $item->topic_str = $item->topic ? $item->topic->name : '';
            $item->is_ban = 0;
            if ($item->user){
                $role_id = $item->user->role_id;
                if (in_array($role_id,[MemberModel::ROLE_BAN,MemberModel::ROLE_FORBIDDEN])){
                    $item->is_ban = 1;
                }
            }
            return $item;
        };
    }

    /**
     * 试图渲染
     * @return void
     */
    public function indexAction()
    {
        //拒绝原因
        $refuseReason = setting('post_refuse_reason','');
        $refuseReason = explode("\r\n", $refuseReason);
        $refuseList = [];
        foreach ($refuseReason as $v){
            $refuseList[$v] = $v;
        }

        $topics = PostTopicModel::get()->pluck('name', 'id')->toArray();
        $this->assign('topicArr', $topics);
        $this->assign('refuseReason', $refuseList);
        $this->assign('topicId', $_GET['topic_id'] ?? '');
        $this->assign('aff', $_GET['aff'] ?? '');
        $this->display();
    }

    public function deleteAfterCallback($model, $isDelete)
    {
        if ($isDelete) {
            PostMediaModel::where('pid', $model->id)->delete();

            //用户发帖数
            if ($model && $model->status == PostModel::STATUS_PASS){
                $postClub = \PostClubsModel::where('aff', $model->aff)->first();
                if ($postClub){
                    $postClub->decrement('post_num');
                }

                $member = \MemberModel::findByAff($model->aff);
                if ($member){
                    $member->decrement('post_count');

                    MemberModel::clearFor($member->toArray());
                }

                //修复话题的发帖数
                $topic = \PostTopicModel::find($model->topic_id);
                if ($topic){
                    $topic->decrement('post_num');
                    cached(PostTopicModel::POST_TOPIC_ALL_GROUP_KEY)->clearCached();
                }
            }
        }
    }

    protected function getModelObject()
    {
        return PostModel::with('medias', 'member', 'topic','manager');
    }

    /**
     * 获取本控制器和哪个model绑定
     * @return string
     */
    protected function getModelClass(): string
    {
        return PostModel::class;
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
        PostModel::clearDetailCache($model->id);
        PostModel::clearFirstPageCache();
    }

    protected function doSave($data)
    {
        list('category' => $category, '_pk' => $id, 'aff' => $aff, 'title' => $title, 'cityname' => $cityname, 'ipstr' => $ipstr) = $data;
        test_assert($aff, 'aff必传');
        test_assert($title, '标题必传');
        test_assert($member = MemberModel::findByAff($aff), '用户不存在');

        if (empty($ipstr)) {
            $ipstr = '192.168.1.1';
        }
        if (empty($cityname)) {
            $cityname = '火星';
        }

        $oldModel = null;
        if (empty($id)) {
            $post = PostModel::create([
                 'topic_id' => $data['topic_id'],
                 'category' => $data['category'],
                 'content' => '',
                 'content_word' => '',
                 'aff' => $member->aff,
                 'ipstr' => $ipstr,
                 'cityname' => $cityname,
                 'refresh_at' => date('Y-m-d H:i:s'),
                 'created_at' => date('Y-m-d H:i:s'),
                 'title' => addslashes(emojiEncode($title)),
                 'status' => $data['status'],
                 'set_top' => $data['set_top'],
                 'is_best' => $data['is_best'],
                 'is_deleted' => $data['is_deleted'],
                 'is_finished' => $data['is_finished'],
                 'refuse_reason' => $data['refuse_reason'],
                 'coins' => 0,
                 'admin_id' => $this->getUser()->uid
            ]);
            $post = $this->getModelObject()->useWritePdo()->where('id' , $post->id)->first();
            //维护发帖数量
            if ($post->status == PostModel::STATUS_PASS){
                $this->postNum($post);
            }
        } else {
            $post = PostModel::find($id);
            $oldModel = clone $post;
            test_assert($post, '帖子不存在');
            $post->fill($data);
            if ($post->status != PostModel::STATUS_PASS && $data['status'] == PostModel::STATUS_PASS){
                $post->admin_id = $this->getUser()->uid;
            }
            $post->saveOrFail();
            //维护发帖数量
            if ($oldModel->status != PostModel::STATUS_PASS && $data['status'] == PostModel::STATUS_PASS){
                $this->postNum($post);
            }
        }

        //切片
        if ($post->status == PostModel::STATUS_PASS && $post->is_finished == PostModel::FINISH_NO){
            //全部改为转换中
            $medias = PostMediaModel::getMakeSliceList($post->id,PostMediaModel::TYPE_RELATE_POST);
            if ($medias){
                //全部改为转换中
                PostMediaModel::updateSliceStatus($post->id,PostMediaModel::TYPE_RELATE_POST);
                PostMediaModel::makeAndSlice($medias);
            }
        }

        PostModel::clearDetailCache($post->id);
        PostModel::clearFirstPageCache();

        //新增
        if($oldModel == null){
            if($post->status == PostModel::STATUS_PASS){
                //维护发帖数量
                $this->postNum($post);
            }
        }else{
            //更新
            if ($post->staus == PostModel::STATUS_PASS && $oldModel->status != PostModel::STATUS_PASS){
                //维护发帖数量
                $this->postNum($post);
            }
        }

        return $post;
    }

    /**
     * @description 维护用户发帖数量
     */
    public function postNum(\PostModel $post){
        $aff = $post->aff;
        \PostClubsModel::where('aff',$aff)->increment('post_num');

        $member = \MemberModel::findByAff($aff);
        $member->increment('post_count');
        MemberModel::clearFor($member->toArray());

        \PostTopicModel::where('id', $post->topic_id)->increment('post_num');
        cached('')->clearGroup(PostTopicModel::POST_TOPIC_ALL_GROUP_KEY);
    }

    public function fix_post_numAction(){
        //修复用户的发帖数
        $posts = PostModel::query()
            ->selectRaw("count(1) as num,aff")
            ->where('status',PostModel::STATUS_PASS)
            ->groupBy('aff')
            ->get()
            ->toArray();

        foreach ($posts as $post){
            $postClub = \PostClubsModel::where('aff', $post['aff'])->first();
            if ($postClub){
                $postClub->post_num = $post['num'];
                $postClub->save();
            }

            $member = \MemberModel::findByAff($post['aff']);
            if ($member){
                $member->post_count = $post['num'];
                $member->save();

                MemberModel::clearFor($member->toArray());
            }
        }

        //修复话题的发帖数
        $posts = PostModel::query()->selectRaw("count(1) as num,topic_id")->where('status',PostModel::STATUS_PASS)->groupBy('topic_id')->get()->toArray();
        \PostTopicModel::query()->update(['post_num' => 0]);
        foreach ($posts as $post){
            $topic = \PostTopicModel::find($post['topic_id']);
            $topic->post_num = $post['num'];
            $topic->save();
        }
        cached('')->clearGroup(PostTopicModel::POST_TOPIC_ALL_GROUP_KEY);

        //修复帖子评论数
        $this->fixPostCommentNum();

        $this->ajaxSuccessMsg('修复成功');
    }

    public function fixPostCommentNum(){
        $posts = PostModel::query()->where('status',PostModel::STATUS_PASS)->where('is_finished',PostModel::FINISH_OK)->get();
        foreach ($posts as $post){
            $comment_num = PostCommentModel::query()->where('post_id',$post->id)->where('status',PostCommentModel::STATUS_PASS)->count('id');
            $post->comment_num = $comment_num;
            $post->save();
        }
    }

    public function pass_allAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->ajaxError('请求错误');
        }
        $post = $this->postArray();
        $ary = explode(',', $post['value'] ?? '');
        $ary = array_filter($ary);
        $posts = PostModel::whereIn('id', $ary)
            ->where('status', PostModel::STATUS_WAIT)
            ->get();

        try {
            transaction(function () use ($posts) {
                /** @var PostModel $post */
                foreach ($posts as $post) {
                    $data = [
                        'status' => PostModel::STATUS_PASS,
                        'admin_id' => $this->getUser()->uid,
                    ];
                    $ret = PostModel::where('id', $post->id)
                        ->update($data);
                    if ($ret <= 0)
                        throw new Exception('系统异常');

                    //视频切片
                    if ($post->category == PostModel::TYPE_VIDEO){
                        $medias = PostMediaModel::getMakeSliceList($post->id,PostMediaModel::TYPE_RELATE_POST);
                        //全部改为转换中
                        PostMediaModel::updateSliceStatus($post->id,PostMediaModel::TYPE_RELATE_POST);
                        PostMediaModel::makeAndSlice($medias);
                    }else{
                        // 对发帖人通知过审
                        $msg = sprintf(SystemNoticeModel::AUDIT_POST_PASS_MSG, $post->title);
                        $model = SystemNoticeModel::addNotice($post->aff, $msg, '审核消息');
                        if (!$model)
                            throw new Exception('系统异常');

                        //MemberModel::where('aff', $post->aff)->increment('post_count');
                        PostModel::clearDetailCache($post->id);
                        PostModel::clearFirstPageCache();
                    }

                    //维护发帖数量
                    $this->postNum($post);
                }
            });
            return $this->ajaxSuccessMsg('操作成功');
        } catch (Exception $e) {
            return $this->ajaxError($e->getMessage());
        }
    }

    public function passAction(){
        try {
            if (!$this->getRequest()->isPost())
                throw new Exception('未找到帖子');

            $data = $this->postArray();
            $data['status'] = PostModel::STATUS_PASS;

            $this->audit($data);
        }catch (Exception $e){
            return $this->ajaxError($e->getMessage());
        }

    }

    public function refuseAction(){
        try {
            if (!$this->getRequest()->isPost())
                throw new Exception('未找到帖子');

            $data = $this->postArray();
            $data['status'] = PostModel::STATUS_UNPASS;

            $this->audit($data);
        }catch (Exception $e){
            return $this->ajaxError($e->getMessage());
        }

    }

    public function batch_refuseAction(){

        if (!$this->getRequest()->isPost())
            throw new Exception('数据异常');

        $data = $this->postArray();

        $refuse_reason = $data['refuseReason'] ?? null;
        $postIds = $data['ids'] ?? '';
        $postIds = explode(',', $postIds);

        if (empty($refuse_reason)) {
            return $this->ajaxError('请选择拒绝原因');
        }
        if (!$postIds) {
            return $this->ajaxError('帖子ID不能为空');
        }

        //过滤已经审核通过的
        $posts = PostModel::query()->whereIn('id',$postIds)->where('status',PostModel::STATUS_WAIT)->get();
        if (!$posts){
            return $this->ajaxError('没有待审核的帖子');
        }

        try {
            transaction(function () use($posts,$refuse_reason){
                foreach ($posts as $post){
                    $post->status = PostModel::STATUS_UNPASS;
                    $post->refuse_reason = $refuse_reason;
                    $post->admin_id = $this->getUser()->uid;
                    $ret = $post->save();
                    test_assert($ret,"系统异常");

                    // 对发帖人通知过审
                    $msg = sprintf(SystemNoticeModel::AUDIT_POST_UNPASS_MSG, $post->title, $refuse_reason);
                    $model = SystemNoticeModel::addNotice($post->aff, $msg, '审核消息');
                    test_assert($model,"系统异常");
                }
            });

            $this->ajaxSuccessMsg("批量拒绝操作成功");
        }catch (Exception $e){
            return $this->ajaxError($e->getMessage());
        }
    }

    public function audit($data)
    {
        try {
            /* @var PostModel $post **/
            $post = PostModel::where('id', $data['id'])
                ->first();

            if (!$post)
                throw new Exception('未找到帖子');

            $post->status = $data['status'];
            $post->refuse_reason = $data['refuse_reason'];
            $post->admin_id = $this->getUser()->uid;
            $ret = $post->save();
            if (!$ret)
                throw new Exception('系统异常');

            if ($data['status'] == PostModel::STATUS_UNPASS) {
                // 对发帖人通知过审
                $msg = sprintf(SystemNoticeModel::AUDIT_POST_UNPASS_MSG, $post->title, $data['refuse_reason']);
                $model = SystemNoticeModel::addNotice($post->aff, $msg, '审核消息');
                if (!$model)
                    throw new Exception('系统异常');
            } else {
                //发起切片
                if ($post->category == PostModel::TYPE_VIDEO) {
                    $medias = PostMediaModel::getMakeSliceList($post->id,PostMediaModel::TYPE_RELATE_POST);
                    //全部改为转换中
                    PostMediaModel::updateSliceStatus($post->id,PostMediaModel::TYPE_RELATE_POST);
                    PostMediaModel::makeAndSlice($medias);
                } else {
                    // 对发帖人通知过审
                    $msg = sprintf(SystemNoticeModel::AUDIT_POST_PASS_MSG, $post->title);
                    $model = SystemNoticeModel::addNotice($post->aff, $msg, '审核消息');
                    if (!$model)
                        throw new Exception('系统异常');

                    PostModel::clearDetailCache($post->id);
                    PostModel::clearFirstPageCache();
                }

                //维护发帖数量
                $this->postNum($post);
            }

            return $this->ajaxSuccessMsg('操作成功');
        } catch (Exception $e) {
            return $this->ajaxError($e->getMessage());
        }
    }

    public function txtAction()
    {
        $id = $_GET['id'] ?? 0;
        $post = \PostModel::where('id', $id)->first();
        $txt = $post->content;

        //替换图片
        $reg = '/(\b(https|http):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|]\.(png|jpg|gif|jpeg))/i';
        preg_match_all($reg,$txt,$match);
        foreach ($match[1] as $img){
            $img_path = parse_url($img,PHP_URL_PATH);
            $txt = str_replace($img, "{{img-cdn}}".$img_path, $txt);
        }
        //替换视频
        $reg2 = "/[dplayer[^<>]*url=[\"]([^\"]+)[\"]/Ui";
        preg_match_all($reg2,$txt,$match);
        foreach ($match[1] as $m){
            if (substr($m,-3) == 'mp4'){
                if (!str_contains($m, PostMediaModel::getR2Mp4PlayUrl())){
                    $m = ltrim($m,'/');
                    $txt = str_replace($m, "{{mp4-cdn}}/".$m, $txt);
                }
            }else{
                $txt = str_replace($m, "{{m3u8-cdn}}/". ltrim(parse_url($m,PHP_URL_PATH), '/'), $txt);
            }
        }

        $this->assign('post_id', $post->id);
        $this->assign('post_txt', $txt);
        $this->assign('post_title', $post->title);
        $this->assign('mp4_domain', 'https://play.xmyy8.co');
        $this->assign('m3u8_domain', 'https://video.iwanna.tv');

        $this->display();
    }

    public function txt_saveAction()
    {
        try {
            $txt = $_POST['txt'] ?? '';
            $pk = $_POST['_pk'] ?? 0;
            /* @var PostModel $post **/
            $post = PostModel::where('id', $pk)->first();
            test_assert($post, '不存在此帖子');
            if ($post->status == PostModel::STATUS_PASS && $post->is_finished == PostModel::FINISH_NO){
                return $this->ajaxSuccess('帖子中有视频正在切片中，不能修改内容');
            }

            transaction(function () use ($post, $txt) {
                //解析markdown 获取类型
                $txt = str_replace("{{mp4-cdn}}/", '', $txt);
                $txt = str_replace("{{m3u8-cdn}}/", '', $txt);
                $txt = str_replace("{{img-cdn}}", TB_IMG_PWA_CN, $txt);
                if ($post->content == $txt){
                    return $this->ajaxSuccess('保存成功');
                }
                $txtTmp = $txt;
                $txtTmp = PostModel::replaceSym($txtTmp);
                $content = \tools\LibMarkdown::loadMarkdown($txtTmp);
                $videos = \tools\LibMarkdown::getVideoFromHtml($content);
                $word = \tools\LibMarkdown::getWordFromHtml($content);
                $imgs = \tools\LibMarkdown::getImgFromHtml($content);
                $covers = \tools\LibMarkdown::getCoversFromHtml($content);

                //如果不是文字帖子，先清理
                if ($post->type != PostModel::TYPE_TXT){
                    //清理原记录视频和图片
                    PostMediaModel::where('pid', $post->id)->delete();
                }
                //先确定类型
                $type = PostModel::TYPE_TXT;
                $is_finished = PostModel::FINISH_OK;
                if (count($imgs) > 0){
                    $type = PostModel::TYPE_IMG;
                    foreach ($imgs as $img){
                        $img = parse_url($img,PHP_URL_PATH);
                        PostMediaModel::createRecord(
                            $post->aff, PostMediaModel::TYPE_IMG, PostMediaModel::TYPE_RELATE_POST,
                            $post->id, $img,'',PostMediaModel::STATUS_OK);
                    }
                }
                if (count($videos) > 0){
                    $type = PostModel::TYPE_VIDEO;
                    foreach ($videos as $key => $video){
                        if (!str_contains($video, PostMediaModel::getR2Mp4PlayUrl())){
                            //相对路径
                            $video = ltrim(parse_url($video,PHP_URL_PATH),'/');
                        }
                        $status = PostMediaModel::STATUS_OK;
                        //mp4
                        if (substr($video,-4) == '.mp4'){
                            $is_finished = PostModel::FINISH_NO;
                            if ($post->status == PostModel::STATUS_PASS){
                                $status = PostMediaModel::STATUS_ING;
                            }else{
                                $status = PostMediaModel::STATUS_NO;
                            }
                        }
                        $media = PostMediaModel::createRecord(
                            $post->aff, PostMediaModel::TYPE_VIDEO, PostMediaModel::TYPE_RELATE_POST,
                            $post->id, $video,$covers[$key],$status);
                        if (substr($video,-4) == '.mp4' && $post->status == PostModel::STATUS_PASS){
                            $url = $video;
                            if (!str_contains($video, PostMediaModel::getR2Mp4PlayUrl())){
                                $url =  "/".$video;
                            }
                            //发起切片
                            $data = [
                                [
                                    'aff'       => $media->aff,
                                    'id'        => $media->id,
                                    'media_url' => $url
                                ]
                            ];
                            PostMediaModel::makeAndSlice($data);
                        }
                    }
                }

                $post->category = $type;
                $post->is_finished = $is_finished;
                $post->content = $txt;
                $post->video_num = count($videos);
                $post->photo_num = count($imgs);
                $word = trim(str_replace(" ","",$word));
                $post->content_word = $word ?: $post->title;
                $isOk = $post->save();
                test_assert($isOk, '系统异常,保存失败');

                PostModel::clearDetailCache($post->id);
                PostModel::clearFirstPageCache();
            });

            return $this->ajaxSuccess('保存成功');
        } catch (Throwable $e) {
            return $this->ajaxError($e->getMessage());
        }
    }

    public function cached_clearAction()
    {
        \PostModel::clearAllCache();
        return $this->ajaxSuccessMsg('操作成功');
    }

    /**
     * @description 封禁用户
     */
    public function banAction(){
        try {
            if (!$this->getRequest()->isPost())
                throw new Exception('数据异常');
            $data = $this->postArray();
            $id = $data['id'];
            $post = PostModel::find($id);
            test_assert($post,'帖子不存在');
            transaction(function () use($post){
                $aff = $post->aff;
                //帖子不通过
                $isOk = PostModel::where('aff',$aff)->update(['status' => PostModel::STATUS_UNPASS]);
                test_assert($isOk,'系统异常');
                //评论删除
                PostCommentModel::where('aff',$aff)->get()->map(function ($item){
                    $isOk = $item->delete();
                    test_assert($isOk,'系统异常');
                });
                //用户封禁
                $model = PostBanModel::where('aff', $aff)->first();
                if (!$model) {
                    $isOk = PostBanModel::create([
                        'aff' => $aff,
                        'num' => 3,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                    test_assert($isOk,'系统异常');
                }
                //永久禁言
                $member = MemberModel::findByAff($aff);
                $member->ban_post = MemberModel::BAN_POST_YES;
                $member->role_id = MemberModel::ROLE_BAN;
                $isOk = $member->save();
                test_assert($isOk,'系统异常');
                $member->clearCached();

                if ($member->auth_status == MemberModel::AUTH_STATUS_YES){
                    $creator = PostCreatorModel::findByAff($aff);
                    if ($creator){
                        $creator->ban_post = MemberModel::BAN_POST_YES;
                        $isOk = $creator->save();
                        test_assert($isOk,'系统异常');
                    }
                }
            });
            return $this->ajaxSuccessMsg('操作成功');
        }catch (Exception $e){
            return $this->ajaxError($e->getMessage());
        }
    }

    public function delAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->ajaxError('请求错误');
        }
        $post = $this->postArray();
        /** @var PostModel $model */
        $model = PostModel::where('id', $post['_pk'])->first();
        test_assert($model, '帖子不存在');
        $model->is_deleted = PostModel::DELETED_OK;
        $isOK = $model->save();

        if ($isOK) {
            return $this->ajaxSuccessMsg('操作成功');
        } else {
            return $this->ajaxError('操作错误');
        }
    }

    public function delAllAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->ajaxError('请求错误');
        }
        $post = $this->postArray();
        $ary = explode(',', $post['value'] ?? '');

        try {
            \DB::beginTransaction();
            foreach ($ary as $id) {
                if (empty($id)) {
                    continue;
                }
                /** @var PostModel $model */
                $model = PostModel::where('id', $id)->first();
                $model->is_deleted = PostModel::DELETED_OK;
                $isOk = $model->save();
                test_assert($isOk, '删除失败');
            }
            \DB::commit();
            return $this->ajaxSuccessMsg('操作成功');
        } catch (\Exception $e) {
            \DB::rollBack();
            return $this->ajaxError('操作错误');
        }
    }
}