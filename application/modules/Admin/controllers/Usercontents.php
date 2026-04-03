<?php

/**
 * Class UsercontentsController
 *
 * @author xiongba
 * @date 2023-03-24 12:50:56
 */
class UsercontentsController extends BackendBaseController
{

    use \repositories\HoutaiRepository;

    /**
     * 列表数据过滤
     *
     * @return Closure
     */
    protected function listAjaxIteration(): Closure
    {
        return function (UserContentsModel $item) {
            $item->setHidden([]);
            $item->admin_str = '';
            if ($item->manager){
                $item->admin_str = $item->manager->username;
            }
            $item->nickname = $item->member ? $item->member->nickname : '未知';
            $tags = json_decode($item->tags , 1) ;
            if (!is_array($tags)){
                $tags = [];
            }
            $item->tags_str = join(',' ,$tags );
            $category_id = json_decode($item->category_id , 1) ;
            if (!is_array($category_id)){
                $category_id = [];
            }
            $item->category_ids = join(',' ,$category_id );
            $item->status_str = UserContentsModel::STATUS[$item->status] ?? '未知';
            $item->cover_url = TB_IMG_ADM_US . '/' . trim(parse_url($item->cover, PHP_URL_PATH), '/');
            return $item;
        };
    }

    public function setTags($value){
        return json_encode(explode(',' , $value));
    }

    protected function postArray($setPost = null)
    {
        $post = $_POST;
        if (isset($post['category_ids'])) {
            $post['category_id'] = json_encode($post['category_ids']);
        }
        return $post;
    }

    /**
     * 试图渲染
     *
     * @return void
     */
    public function indexAction()
    {
        $rejectAry = setting('user-contents:rejects' , '配置中心配置user-contents:rejects的值');
        $rejectAry = explode("\n" , $rejectAry);
        $rejectAry2 = array_combine($rejectAry, $rejectAry);
        $cates = MetasModel::query()
            ->selectRaw('mid, name')
            ->where('type', MetasModel::TYPE_CATEGORY)
            ->orderBy('order')
            ->get()->toArray();
        $cates = json_encode($cates, JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        $this->assign('cates' , $cates);
        $this->assign('rejectAry' , $rejectAry);
        $this->assign('rejectAry2' , $rejectAry2);
        $users = UsersModel::pluck('screenName','uid')->toArray();
        $this->assign('users' , $users);
        $this->assign('fbmrid' , setting('fbmrid',27));
        $this->display();
    }


    public function previewAction()
    {
        $id = $_GET['id'] ?? 0;
        try {
            $content = UserContentsModel::find($id);
            $html = \tools\LibMarkdown::parseContent($content->body);
            $html = str_replace('<br>', "<br>\n", $html);
            $html = str_replace('</p>', "</p>\n", $html);
            $html
                = preg_replace_callback('#<img(.*?)src=[\'"]([^\\2]*?)"(.*?)>#i',
                function ($match) use (&$i) {
                    $src = url_image($match[2]);
                    $append = '';

                    return sprintf('<img %s src="%s" %s %s/> ', trim($match[1]),
                        $src, $append, trim($match[3]));
                }, $html);
            $html = preg_replace_callback('#<video([^>]*)></video>#i',
                function ($match) use (&$i) {
                    $match[0] = trim($match[0]);
                    $attr = \tools\LibMarkdown::shortcodeParseAttrs($match[1]);
                    $url = $attr['src'];
                    $src = $attr['src'];
                    //如果不是r2上传
                    if (!str_contains($url, PostMediaModel::getR2Mp4PlayUrl())){
                        //相对路径
                        $src = parse_url($url , PHP_URL_PATH);
                        $src = trim($src, '/');
                        $url = url_video($src);
                    }

                    if (!str_ends_with($src , '.mp4')){
                        $html =<<<HTML
<video id="hls-video" class="video-js vjs-16-9 vjs-fluid" playsinline webkit-playsinline controls x-webkit-airplay="true" x5-video-player-fullscreen="true" x5-video-player-typ="h5">
    <source src="$url" type="application/x-mpegURL">
</video>
HTML;
                    }else{
                        $html = <<<HTML
<video controls style="width: 580px;"> <source src="$url"> </video>
HTML;
                    }



                    return $html;
                }, $html);
            $this->assign('html', $html);
            $this->assign('title', $content->title);
            $this->display('preview');
        } catch (\Throwable $e) {
            $this->display('preview');
        }
    }

    public function passAction()
    {
        try {
            trigger_log(__LINE__);
            $id = $_POST['id'] ?? 0;
            $cate_id = $_POST['category_ids'] ?? [];
            $author_id = $_POST['author_id'] ?? 0;
            $tags = $_POST['tags'];
            $coin = $_POST['coin'] ?? 0;
            $status = $_POST['status'] ?? '';
            if (!in_array($status , [ContentsModel::STATUS_PUBLISH , ContentsModel::STATUS_WAITING])){
                throw new RuntimeException('状态错误');
            }
            trigger_log(__LINE__);
            if (empty($author_id) || empty($cate_id)){
                throw new RuntimeException('参数错误');
            }
            trigger_log(__LINE__);
            DB::beginTransaction();
            trigger_log(__LINE__);
            $userContent = UserContentsModel::find($id);
            if ($userContent->status != UserContentsModel::STATUS_WAIT){
                throw new RuntimeException('当前文章已经处理');
            }
            trigger_log(__LINE__);
            $content = ContentsModel::make();
            $content->title = $_POST['title'];
            $content->text = '<!--markdown-->'.$userContent->body;
            $content->status = $status;
            $content->modified = time();
            $content->authorId = $author_id;
            $content->type = ContentsModel::TYPE_POST;
            $content->is_slice = 0;
            $content->allowComment = 1;
            $content->allowPing = 1;
            $content->allowFeed = 1;
            $content->save();
            $userContent->cid = $content->cid;
            $userContent->status = UserContentsModel::STATUS_PASSED;
            $userContent->admin_id = $this->getUser()->uid;
            $userContent->save();
            foreach ($cate_id as $cate){
                DB::table('relationships')->insert([
                    'mid' => $cate,
                    'cid' => $content->cid,
                ]);
            }
            $fields = [
                'banner'          => 'https://www.51cg1.com'.parse_url($userContent->cover, PHP_URL_PATH),
                'contentLang'     => '0',
                'disableBanner'   => '1',
                'disableDarkMask' => '0',
                'enableFlowChat'  => '0',
                'enableMathJax'   => '0',
                'enableMermaid'   => '0',
                'headTitle'       => '0',
                'hotSearch'       => '0',
                'redirect'        => '',
                'TOC'             => '0',
            ];
            foreach ($fields as $field=>$value){
                FieldsModel::insert([
                    'cid' => $content->cid,
                    'name'        => $field,
                    'type'        => 'str',
                    'str_value'   => $value,
                ]);
            }
            if (str_contains($content->text , '.mp4') && preg_match_all('#url="([^"]+)"#', $content->text, $ary)) {
                TempMvModel::makeAndSlice($ary, $userContent, $content);
                $userContent->status = UserContentsModel::STATUS_WAIT_SLICE;
            } else {
                $content->is_slice = 1;
                $content->save();
            }
            // 支持 # 和 , 拆分标签
            $tags = preg_split('/[#,\s]+/u', str_replace('，', ',', $tags));
            $tags = collect($tags)->map(function ($tag){
                $tag = trim($tag);
                return $tag;
            })->filter(function ($tag){
                return !empty($tag);
            })->values();
            $tagsItems = MetasModel::useWritePdo()->where('type', MetasModel::TYPE_TAG)->whereIn('slug',$tags)->get();
            $diff = $tags->diff($tagsItems->pluck('slug'));
            foreach ($diff as $tag){
                $meta  = MetasModel::create([
                    'name'   => $tag,
                    'slug'   => $tag,
                    'type'   => MetasModel::TYPE_TAG,
                    'count'  => 0,
                ]);
                $tagsItems->add($meta);
            }
            if ($tagsItems->count()){
                MetasModel::whereIn('mid' , $tagsItems->pluck('mid'))->increment('count');
                foreach ($tagsItems as $item){
                    DB::table('relationships')->insert([
                        'mid' => $item->mid,
                        'cid' => $content->cid,
                    ]);
                }
            }

            if ($coin > 0){
                $userContent->income = $coin;
                /** @var MemberModel $member */
                $member = $userContent->member()->first();
                $member->addIncome($coin, $member, $userContent, MoneyIncomeLogModel::SOURCE_GAOFEI, '稿费');
                $member->clearCached();
            }
            if ($userContent->isDirty()){
                $userContent->save();
            }
            DB::commit();
            return $this->ajaxSuccessMsg('操作成功');
        }catch (\Throwable $e){
            DB::rollBack();
            return $this->ajaxError($e->getMessage());
        }
    }

    public function sliceAction(){
        try {
            $id = $_POST['_pk'] ?? 0;
            test_assert($id, '数据异常');
            $userContent =  UserContentsModel::find($id);
            test_assert($userContent, '数据不存在');
            if ($userContent->status != UserContentsModel::STATUS_WAIT_SLICE){
                return $this->ajaxError("不是等待回调状态");
            }

            $list = TempMvModel::useWritePdo()->where('cid',$userContent->cid)->where('status',TempMvModel::STATUS_INIT)->get();
            foreach ($list as $object){
                if (str_contains($object->url , '.mp4')){
                    $data = [
                        'uuid'    => $userContent->aff,
                        'm_id'    => $object->id,
                        'playUrl' => $object->url,
                        'needMp3' => 0,
                        'needImg' => 1,
                    ];
                    \tools\mp4Upload::accept($data, 'mv_callback');
                }
            }

            $this->ajaxSuccessMsg('操作完成');
        } catch (\Throwable $e) {
            return $this->ajaxError($e->getMessage());
        }
    }

    public function rejectAction()
    {
        $reason = $_POST['reason'] ?? '';
        $id = $_POST['id'] ?? 0;
        try {
            $model = UserContentsModel::find($id);
            if ($model->status != UserContentsModel::STATUS_WAIT) {
                return $this->ajaxError('已处理');
            }
            $model->status = UserContentsModel::STATUS_DENIED;
            $model->denied_reason = $reason;
            $model->denied_at = time();
            $model->admin_id = $this->getUser()->uid;
            $model->saveOrFail();
            $this->ajaxSuccessMsg('操作完成');
        } catch (\Throwable $e) {
            return $this->ajaxError($e->getMessage());
        }
    }

    // 批量审核
    public function batch_rejectAction()
    {
        try {
            $ids = $_POST['ids'] ?? '';
            $reason = $_POST['reason'] ?? '';
            test_assert($reason, '审核拒绝必须选择理由');

            $ids = array_unique(array_filter(explode(",", $ids)));
            UserContentsModel::whereIn('id', $ids)->get()->map(function ($item) use ($reason) {
                if ($item->status != UserContentsModel::STATUS_WAIT) {
                    return;
                }
                $item->status = UserContentsModel::STATUS_DENIED;
                $item->denied_reason = $reason;
                $item->denied_at = time();
                $item->admin_id = $this->getUser()->uid;
                $isOk = $item->save();
                test_assert($isOk, '审核异常');
            });
            $this->ajaxSuccessMsg('操作完成');
        } catch (\Throwable $e) {
            return $this->ajaxError($e->getMessage());
        }
    }


    /**
     * 获取本控制器和哪个model绑定
     *
     * @return string
     */
    protected function getModelClass(): string
    {
        return UserContentsModel::class;
    }

    /**
     * 定义数据操作的表主键名称
     *
     * @return string
     */
    protected function getPkName(): string
    {
        return 'id';
    }

    /**
     * 定义数据操作日志
     *
     * @return string
     * @author xiongba
     */
    protected function getLogDesc(): string
    {
        return '';
    }
}