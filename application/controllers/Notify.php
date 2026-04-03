<?php


class NotifyController extends \Yaf\Controller_Abstract
{

    public function callpayAction(): bool
    {
        return $this->forward('Api', 'Callback', 'pay_callback');
    }

    public function notify_withdrawAction(): bool
    {
        return $this->forward('Api', 'Callback', 'notify_withdraw');
    }

    public function mv_callbackAction()
    {
        $data = $this->sliceCbData();
        $tempmv = TempMvModel::find($data['mv_id']);
        if (empty($tempmv) || $tempmv->status != TempMvModel::STATUS_INIT) {
            echo 'success';
            exit();
        }
        $tempmv->m3u8 = $data['source'];
        $tempmv->duration = $data['duration'];
        $tempmv->cover = $data['cover_thumb'];
        $tempmv->status = TempMvModel::STATUS_SLICE;
        $tempmv->save();
        $temps = TempMvModel::useWritePdo()
            ->where('id', '!=', $tempmv->id)
            ->where('cid', $tempmv->cid)
            ->get();
        if ($temps->where('status', TempMvModel::STATUS_INIT)->count() == 0) {
            $contents = ContentsModel::find($tempmv->cid);
            $contents->text = str_replace($tempmv->url, $data['source'], $contents->text);
            foreach ($temps as $temp){
                $contents->text = str_replace($temp->url, $temp->m3u8, $contents->text);
            }
            $contents->is_slice = 1;
            $contents->save();
            UserContentsModel::where('cid', $contents->cid)->update([
                'status' => UserContentsModel::STATUS_PASSED,
            ]);
        }
        echo 'success';
    }


    /**
     * @return array
     */
    protected function sliceCbData(): array
    {
        if(empty($_POST)) $_POST = $_REQUEST;
         $data = jaddslashes($_POST);
         if(isset($data['mod']))  unset($data['mod']);
         if(isset($data['code'])) unset($data['code']);
         $sign = LibCrypt::check_sign($data, '132f1537f85scxpcm59f7e318b9epa51');
         trigger_log('视频切片回调'.json_encode($data));
         if ($sign != $this->getSign($data)) {
             trigger_log('上架视频回调--验签失败：'.json_encode($data));
             exit('fail');
         }

         return [
             'cover_thumb'  => $data['cover_thumb'] ?? '',
             'thumb_width'  => $data['thumb_width'] ?? 0,
             'thumb_height' => $data['thumb_height'] ?? 0,
             'duration'     => $data['duration'] ?? 0,
             'source'       => $data['source'],
             'mv_id'        => $data['mv_id'],
         ];
         $data = $_POST;
    
    // Log incoming data
//    trigger_log('视频切片回调数据: ' . json_encode($data));

    // Validate required fields
    if (!isset($data['source']) || !isset($data['mv_id'])) {
        trigger_error('回调数据缺少必要参数: ' . json_encode($data));
        exit('fail');
    }

    return [
        'cover_thumb'  => $data['cover_thumb'] ?? '',
        'thumb_width'  => $data['thumb_width'] ?? 0,
        'thumb_height' => $data['thumb_height'] ?? 0,
        'duration'     => $data['duration'] ?? 0,
        'source'       => $data['source'],
        'mv_id'        => $data['mv_id']
    ];
    }

    private function getSign($data): string
    {
        if(isset($data['sign'])) unset($data['sign']);
        $signKey =  config('app.data_sync_key');
        ksort($data);
        $string = '';
        foreach ($data as $key => $datum) {
            if ($datum === '') {
                continue;
            }
            $string .= "{$key}={$datum}&";
        }
        $string .= 'key='.$signKey;

        return md5($string);
    }

    /**
     * 社区话题视频回调
     */
    public function post_mv_callbackAction()
    {
        $data = $this->sliceCbData();

        /** @var PostMediaModel $media */
        $media = PostMediaModel::where('id', $data['mv_id'])
            ->where('type', PostMediaModel::TYPE_VIDEO)
            ->where('status', PostMediaModel::STATUS_ING)
            ->first();
        if (!$media) {
            trigger_error('上架视频--没有找到:' . json_encode($data));
            exit('fail');
        }
        try {
            //\DB::beginTransaction();
            $mp4 = $media->url;
            $m3u8 = $data['source'];

            $cover = $data['cover_thumb'] ?? '';
            $data = [
                'thumb_width'  => $data['thumb_width'] ?? 0,
                'thumb_height' => $data['thumb_height'] ?? 0,
                'duration'     => $data['duration'] ?? 0,
                'media_url'    => $data['source'] ?? '',
                'status'       => PostMediaModel::STATUS_OK,
                'updated_at'   => date('Y-m-d H:i:s'),
            ];
            //有封面不替换
            if (!$media->cover){
                $data['cover'] = $cover;
            }
            if ($media->update($data) <= 0) {
                throw new Exception('系统异常');
            }

            //帖子的视频
            if ($media->relate_type == PostMediaModel::TYPE_RELATE_POST){
                $temps = PostMediaModel::useWritePdo()
                    ->where('id', '!=', $media->id)
                    ->where('pid', $media->pid)
                    ->where('relate_type', PostMediaModel::TYPE_RELATE_POST)
                    ->where('type', PostMediaModel::TYPE_VIDEO)
                    ->get();

                if ($temps->whereIn('status', [PostMediaModel::STATUS_NO, PostMediaModel::STATUS_ING])->count() == 0) {
                    $post = PostModel::find($media->pid);
                    $m3u8 = ltrim($media->media_url,'/');
                    $mp4 = ltrim($media->mp4,"/");
                    $post->content = str_replace($mp4, $m3u8, $post->content);
                    foreach ($temps as $temp){
                        $m3u8 = ltrim($temp->media_url,'/');
                        $mp4 = ltrim($temp->mp4,"/");
                        $post->content = str_replace($mp4, $m3u8, $post->content);
                    }
                    $post->updated_at = \Carbon\Carbon::now();
                    $post->is_finished = PostModel::FINISH_OK;
                    $post->save();

                    // 对发帖人通知过审
                    $msg = sprintf(SystemNoticeModel::AUDIT_POST_PASS_MSG, $post->title);
                    SystemNoticeModel::addNotice($post->aff, $msg, '审核消息');

                    PostModel::clearDetailCache($post->id);
                    PostModel::clearFirstPageCache();
                }
            }else{
                // 前段添加了视频与图文评论 需要添加审核完成和通知被评论的帖子人或者被评论的评论人
                PostCommentModel::where('id', $media->pid)->update([
                    'is_finished' => PostCommentModel::FINISH_OK,
                    'updated_at'  => \Carbon\Carbon::now()
                ]);

                $comment = PostCommentModel::where('id', $media->pid)->first();
                if ($comment->pid == 0) {
                    PostCommentModel::clearCacheWhenCreatePostComment($comment->post_id);
                } else {
                    PostCommentModel::clearCacheWhenCreateComment($comment->pid);
                }
            }
            //\DB::commit();
        } catch (Exception $exception) {
            //\DB::rollBack();
            trigger_error('上架视频--处理失败：' . $exception->getMessage());
            exit('fail');
        }
        exit('success');
    }

    public function get_contentsAction()
    {
        $data = [];
        if ($_POST['pwd'] == 'cgtb') {
            $data = ContentsModel::queryBase()
                ->with(['fields', 'relationships' => function ($query) {
                    $query->with('meta');
                }])
                ->where('type', ContentsModel::TYPE_POST)
                ->where('is_slice', 1)
                ->where('status', ContentsModel::STATUS_PUBLISH)
                ->where('app_hide', ContentsModel::APP_HIDE_NO)
                ->where('created', '<=', time())
                ->orderByDesc('created')
                ->limit(100)
                ->get()
                ->toArray();
        }
        exit(json_encode($data));
    }

    public function getCgxwContentsAction()
    {
        if ($_POST['pwd'] != '0d992045f25fb2e1af6b882d94b79545'){
            echo json_encode(['status' => 0, 'msg' => '非法请求']);
        }
        $p = $_POST['page'] ?? 1;
        $l = $_POST['limit'] ?? 20;
        $mid = $_POST['mid'] ?? 7834;
        $cid  = $_POST['cid'] ?? 0;
        $table = \Yaf\Registry::get('database')->prefix;
        $fullTable = $table.'contents';
        $data = ContentsModel::query()
            ->with([
                'relationships' => function ($query) {
                    $query->with('meta');
                },
            ])
            ->selectRaw("$fullTable.cid,title,created,`order`,type,status,commentsNum,is_home,home_top,is_slice,authorId,`text`")
            ->with('fields', 'author')
            ->when($cid, function ($query, $cid) {
                $query->where('cid', '>', $cid);
            })
            ->whereIn('status', [ContentsModel::STATUS_PUBLISH, ContentsModel::STATUS_SECRET])
            ->where('type', ContentsModel::TYPE_POST)
            ->where('is_slice', 1)
            ->where('app_hide', ContentsModel::APP_HIDE_NO)
            ->when($mid == 7834, function ($query, $mid) {
                $query
                    ->where('relationships.mid', $mid)
                    ->join('relationships', 'relationships.cid', 'contents.cid');
            })
            ->orderBy('created')
            ->forPage($p, $l)
            ->get()
            ->each(function (ContentsModel $model) {
                $model->loadTagWithCategory();
            });

        exit(json_encode($data));
    }

    /**
     * 用户视频回调
     */
    public function user_mv_callbackAction()
    {
        trigger_log('远程发布端-收到视频切片回调请求' );
        $data = $this->sliceCbData();
        trigger_log('远程发布端-收到视频切片回调数据: ' . json_encode($data));

        /** @var UserUploadModel $media */
        $mv_id = $data['mv_id']??'';
        try {
            $media = UserUploadModel::where('id', $data['mv_id'])->first();
            if (!$media) {
                throw new Exception('上架视频--没有找到:' . json_encode($data));
            }

            if($media['slice_status'] == UserUploadModel::SLICE_SUCCESS){
                trigger_log('远程发布端-收到视频切片回调 id: ' .$mv_id .' 已经成功');
                exit('success');
            }

            $data = [
                'slice_status' => UserUploadModel::SLICE_SUCCESS,
                'm3u8_url'     => $data['source'] ?? '',
                'updated_at'   => date('Y-m-d H:i:s'),
            ];
            if ($media->update($data) <= 0) {
                trigger_log('远程发布端-收到视频切片回调 id: ' .$mv_id .' 更新状态失败：'. json_encode($data));
                throw new Exception('系统异常');
            }else{
                trigger_log('远程发布端-收到视频切片回调 id: ' .$mv_id .' 更新状态成功');
            }
        } catch (Exception $exception) {
            //\DB::rollBack();
            trigger_log('上架视频--处理失败：id:'.$mv_id.' - ' . $exception->getMessage());
            exit('fail');
        }
        exit('success');
    }

     /**
     * 附件视频回调
     */
   
   public function attachment_callbackAction()
    {   
        try {
            $data = $this->sliceCbData();
            trigger_log('收到视频切片回调: ' . json_encode($data));

            //查找并验证视频记录
            $media = AttachmentModel::where('id', $data['mv_id'])
                ->where('slice_status', AttachmentModel::SLICE_PROCESS)
                ->first();
                
            if (!$media) {
                trigger_error('未找到处理中的视频记录: ' . json_encode($data));
                exit('fail');
            }

            //更新视频切片状态
            $updateData = [
                'slice_status' => AttachmentModel::SLICE_SUCCESS,
                'm3u8_url'    => $data['source'] ?? '',
                'updated_at'  => date('Y-m-d H:i:s'),
            ];

            if (!$media->update($updateData)) {
                throw new Exception('更新视频记录失败');
            }
            trigger_log("视频ID:{$media->id} 更新切片状态成功");

            //如果没有关联文章ID，直接返回成功
            if (!$media->cid) {
                trigger_log("视频ID:{$media->id} 无关联文章，处理完成");
                exit('success');
            }

            //查找关联文章
            $content = ContentsModel::where('cid', $media->cid)->first();
            if (!$content) {
                trigger_log("文章ID:{$media->cid} 不存在，仅更新视频状态");
                exit('success');
            }

            //检查文章内容中是否包含该视频
            $existingUrls = $this->extractUrls($content->text);
            if (!in_array($media->mp4_url, $existingUrls)) {
                trigger_log("视频URL:{$media->mp4_url} 未在文章内容中找到，可能已被删除");
                exit('success');
            }

           
            $pattern = '/\[dplayer url="' . preg_quote($media->mp4_url, '/') . '"([^\]]*)\]/';
            $replacement = '[dplayer url="' . $data['source'] . '"$1]';
            $newText = preg_replace($pattern, $replacement, $content->text);

         
            if ($newText !== $content->text) {
                $content->update([
                    'text' => $newText,
                    'updated' => time()
                ]);
                trigger_log("文章ID:{$media->cid} 视频URL更新成功");
            }

          
            $pendingVideos = AttachmentModel::where('cid', $media->cid)
                ->where('slice_status', '!=', AttachmentModel::SLICE_SUCCESS)
                ->count();

            if ($pendingVideos === 0) {
                $content->update(['is_slice' => 1]);
                trigger_log("文章ID:{$media->cid} 所有视频处理完成，更新切片状态");
            } else {
                trigger_log("文章ID:{$media->cid} 还有 {$pendingVideos} 个视频待处理");
            }

            exit('success');

        } catch (Exception $exception) {
            trigger_log('视频切片回调处理失败: ' . $exception->getMessage());
            exit('fail');
        }
    }

    /**
     * 从文本中提取所有视频URL
     * @param string $text
     * @return array
     */
    private function extractUrls($text) 
    {
        preg_match_all('/\[dplayer url="([^"]+)"/', $text, $matches);
        return array_map('trim', $matches[1] ?? []);
    }

    /**
     * 用户包养视频回调
     */
    public function userBaoyangCallbackAction()
    {
        $data = $this->sliceCbData();

        /** @var InfoVipResourcesModel $media */
        $media = InfoVipResourcesModel::where('id', $data['mv_id'])
            ->where('status', InfoVipResourcesModel::STATUS_ACCEPT)
            ->first();
        if (!$media) {
            trigger_error('用户包养视频回调--没有找到:' . json_encode($data));
            exit('fail');
        }
        try {
            $data = [
                'status' => InfoVipResourcesModel::STATUS_ACCEPTED,
                'url'     => $data['source'] ?? '',
                'updated_at'   => time(),
                "cover" => $data['cover_thumb'] ?? '',
            ];
            if ($media->update($data) <= 0) {
                throw new Exception('系统异常');
            }

            InfoVipModel::find($media->info_id)->update(['status' => InfoVipModel::STATUS_PASS, 'updated_at' => time()]);
        } catch (Exception $exception) {
            //\DB::rollBack();
            trigger_error('包养视频回调--处理失败：' . $exception->getMessage());
            exit('fail');
        }
        exit('success');
    }



    //AI脱衣回调
    public function call_aiAction(){
        trigger_json('AI回调入口', \service\AiService::LOG_FILE);
        \service\AiService::callback();
    }

    //图片换脸回调
    public function sync_img_faceAction()
    {
        \service\AiService::image_face_back();
    }
}