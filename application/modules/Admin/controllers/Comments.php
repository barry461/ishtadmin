<?php

/**
 * Class CommentsController
 *
 * @author xiongba
 * @date 2022-11-03 08:12:48
 */
class CommentsController extends BackendBaseController
{

    use \repositories\HoutaiRepository;

    /**
     * 列表数据过滤
     *
     * @return Closure
     */
    protected function listAjaxIteration()
    {
        $handle = SensitiveWordsModel::sensitiveHandle();
        return function (CommentsModel $item) use($handle) {
            $item->text_show = htmlspecialchars(addslashes($item->text));
            if ($item->text && $handle->islegal($item->text)){
                $item->text_show = $handle->mark($item->text_show, '<mark>', '</mark>');
            }
            if ($item->author && $handle->islegal($item->author)){
                $item->author = $handle->mark($item->author, '<mark>', '</mark>');
            }
            $item->setHidden([]);
            $item->time_line = formatTimestamp(strtotime($item->created));
            $item->admin_str = '';
            if ($item->manager){
                $item->admin_str = $item->manager->username;
            }
            $item->text = htmlspecialchars(addslashes($item->text));
            $item->status_str = CommentsModel::STATUS_TIPS[$item->status];
            $item->status_class = 'status-' . $item->status;
            $item->is_official = 0;
            if ($item->parent == 0 && $item->app_aff > 0 && redis()->sIsMember(OfficialAccountModel::OFFICIAL_ACCOUNT_SET, $item->app_aff)){
                $item->is_official = 1;
            }
            $item->app_aff_str = $item->app_aff;
            if ($item->app_aff == 0){
                $item->app_aff_str =  'web评论';
            }
            
            // 添加文章标题
            $item->article_title = '';
            if ($item->contents && $item->contents->title) {
                $item->article_title = $item->contents->title;
            }

            return $item;
        };
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
            if ($value !== '' && $key!='c_title') {
                $where[] = [$key, '=', $value];
            }

            if ($key == 'c_title') {
                $ids = ContentsModel::query()->where('title', $value)->get()->pluck('cid')->toArray();
                $ids = $ids ? implode(",", $ids) : '0';
                $where[] = [\DB::raw("cid in ($ids)"),'1'];
            }
        }

        return $where;
    }

    protected function getSearchBetweenParam()
    {
        $get = $this->getRequest()->getQuery();
        $get['between'] = $get['between'] ?? [];
        $where = [];
        foreach ($get['between'] as $key => $value) {
            if ($key == 'created'){
                if ($value['from'] !== '__undefined__'){
                    $where[] = [$key, '>=', strtotime($value['from'])];
                }
                if ($value['to'] !== '__undefined__'){
                    $where[] = [$key, '<=', strtotime($value['to'] . ' 23:59:59')];
                }
            }else{
                if ($value['from'] !== '__undefined__'){
                    $where[] = [$key, '>=', $value['from']];
                }
                if ($value['to'] !== '__undefined__'){
                    $where[] = [$key, '<=', $value['to'] . ' 23:59:59'];
                }
            }
        }

        return $where;
    }

    public function passAction()
    {
        $id = $_POST['value'] ?? '';
        $idAry = explode(',', $id);

        try {
            CommentsModel::useWritePdo()
                ->with('contents')
                ->whereIn('coid', $idAry)
                ->get()
                ->each(function (CommentsModel $item) {
                    $cid = $item->cid;
                    // 只有待审核或过滤状态可以通过，已通过或已拒绝的不处理
                    if (!in_array($item->status, [CommentsModel::STATUS_WAITING, CommentsModel::STATUS_FILTER])) {
                        return;
                    }
                    $item->update([
                        'status' => CommentsModel::STATUS_APPROVED,
                        'admin_id' => $this->getUser()->uid
                    ]);
                    if ($item->app_aff){
                        MemberModel::where('aff' , $item->app_aff)->increment('unread_reply');
                    }
                    $item->contents->increment('commentsNum');
                    cached('')->clearGroup("list-comment:$cid");
                });
            $this->ajaxSuccessMsg('操作成功');
        }catch (Exception $e){
            $this->ajaxError($e->getMessage());
        }
    }


    public function delAction()
    {
        $_POST['value'] = $_POST['_pk'];
        return $this->delAllAction();
    }

    public function delAllAction()
    {
        $id = $_POST['value'] ?? '';
        $idAry = explode(',', $id);

        CommentsModel::useWritePdo()
            ->with('contents')->whereIn('coid', $idAry)
            ->get()
            ->each(function (CommentsModel $item) {
                if ($item->status == CommentsModel::STATUS_APPROVED) {
                    $item->contents->decrement('commentsNum');
                }
                $item->delete();
            });

        return $this->ajaxSuccessMsg('操作成功');
    }


    public function spamAction(){
        $id = $_POST['value'] ?? '';
        $idAry = explode(',', $id);

        CommentsModel::useWritePdo()
            ->with('contents')->whereIn('coid', $idAry)
            ->get()
            ->each(function (CommentsModel $item) {
                if ($item->status == CommentsModel::STATUS_APPROVED) {
                    $item->contents->decrement('commentsNum');
                }
                $item->status = CommentsModel::STATUS_SPAM;
                $item->admin_id = $this->getUser()->uid;
                $item->save();
            });
        return $this->ajaxSuccessMsg('操作成功');
    }

    public function filterAction(){
        $id = $_POST['value'] ?? '';
        $idAry = explode(',', $id);

        CommentsModel::useWritePdo()
            ->with('contents')->whereIn('coid', $idAry)
            ->get()
            ->each(function (CommentsModel $item) {
                if ($item->status == CommentsModel::STATUS_APPROVED) {
                    $item->contents->decrement('commentsNum');
                }
                $item->status = CommentsModel::STATUS_FILTER;
                $item->admin_id = $this->getUser()->uid;
                $item->save();
            });
        return $this->ajaxSuccessMsg('操作成功');
    }

    public function delSameAction(){
        try {
            $id = $_POST['coid'];
            test_assert($id, '数据异常');

            $comment = CommentsModel::find($id);
            test_assert($comment, '评论不存在');

            CommentsModel::useWritePdo()
                ->where('status', CommentsModel::STATUS_WAITING)
                ->where('text', $comment->text)
                ->get()
                ->each(function (CommentsModel $item) {
                    $is_ok = $item->delete();
                    test_assert($is_ok, '删除失败');
                });
            return $this->ajaxSuccessMsg('操作成功');
        }catch (Throwable $e){
            return $this->ajaxError($e->getMessage());
        }
    }

    public function banIpAction(){
        try {
            $id = $_POST['coid'];
            test_assert($id, '数据异常');

            $comment = CommentsModel::find($id);
            test_assert($comment, '评论不存在');
            if (filter_var($comment->ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) || $comment->ip == 'unknown'){
                $host = $comment->ip;
            }else{
                $ip1 = 'https://' . $comment->ip;
                $host = parse_url($ip1, PHP_URL_HOST);
                if (!$host){
                    $host = $comment->ip;
                }
            }
            //添加IP
            redis()->sAdd(BAN_IPS_KEY, $host);
            //删除该IP下所有的评论
            jobs([CommentsModel::class, 'delByIp'], [$comment->ip]);

            //同步到web端
            $ips = redis()->sMembers(BAN_IPS_KEY);
            if (count($ips) > 0){
                $ips = implode(',',$ips);
            }else{
                $ips = '';
            }
            $url = 'https://51cg1.com/ping.php?_yaf=ban-ip';
            \tools\HttpCurl::post($url,['ip' => $ips]);

            return $this->ajaxSuccessMsg('操作成功');
        }catch (Throwable $e){
            return $this->ajaxError($e->getMessage());
        }
    }

    public function banIpGroupAction(){
        try {
            $id = $_POST['coid'];
            test_assert($id, '数据异常');

            $comment = CommentsModel::find($id);
            $ip_v6 = false;
            test_assert($comment, '评论不存在');
            if (filter_var($comment->ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) || $comment->ip == 'unknown'){
                $host = $comment->ip;
                $ip_v6 = true;
            }else{
                $ip1 = 'https://' . $comment->ip;
                $host = parse_url($ip1, PHP_URL_HOST);
                if (!$host){
                    $host = $comment->ip;
                }
            }

            if (!$ip_v6){
                $ipRange = CommentsModel::getIPRange($comment->ip);
                foreach (CommentsModel::generateIPRange($ipRange['start'],$ipRange['end']) as $host) {
                    //添加IP
                    redis()->sAdd(BAN_IPS_KEY, $host);
                    //删除该IP下所有的评论
                    jobs([CommentsModel::class, 'delByIp'], [$host]);
                }
            }else{
                //添加IP
                redis()->sAdd(BAN_IPS_KEY, $host);
                //删除该IP下所有的评论
                jobs([CommentsModel::class, 'delByIp'], [$comment->ip]);
            }

            //同步到web端
            $ips = redis()->sMembers(BAN_IPS_KEY);
            if (count($ips) > 0){
                $ips = implode(',',$ips);
            }else{
                $ips = '';
            }
            $url = 'https://51cg1.com/ping.php?_yaf=ban-ip';
            \tools\HttpCurl::post($url,['ip' => $ips]);

            return $this->ajaxSuccessMsg('操作成功');
        }catch (Throwable $e){
            return $this->ajaxError($e->getMessage());
        }
    }

    protected function getModelObject()
    {
        return CommentsModel::with(['contents','manager']);
    }


    /**
     * 试图渲染
     *
     * @return void
     */
    public function indexAction()
    {
        $hour = date('H');
        $showLike = true;
//        if (in_array($hour,[20,21,22,23])){
//            $showLike = false;
//        }

        $this->assign('showLike',$showLike);
        $this->assign('get' , $_GET);
        $this->display();
    }


    /**
     * 获取本控制器和哪个model绑定
     *
     * @return string
     */
    protected function getModelClass(): string
    {
        return CommentsModel::class;
    }

    /**
     * 定义数据操作的表主键名称
     *
     * @return string
     */
    protected function getPkName(): string
    {
        return 'coid';
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

    public function saveAction()
    {
        try {
            $data = $_POST;
            $cid = $data['cid'];
            $app_aff = $data['app_aff'];
            $text = $data['text'];
            $status = $data['status'];
            $is_top = $data['is_top'];
            if (!$cid || !$app_aff || !$text){
                return $this->ajaxError('数据异常');
            }
            $content = ContentsModel::find($cid);
            if (!in_array($content->type, [ContentsModel::TYPE_POST, ContentsModel::TYPE_SKITS])){
                return $this->ajaxError('此类型文章不能添加评论');
            }
            $member = MemberModel::findByAff($app_aff);
            test_assert($member, '用户不存在');
            if (!redis()->sIsMember(OfficialAccountModel::OFFICIAL_ACCOUNT_SET, $app_aff)){
                return $this->ajaxError('只有官方账号才能添加文章评论');
            }
            $data = [
                'cid'          => $cid,
                'created'      => time(),
                'author'       => $member->nickname,
                'reply_author' => '',
                'reply_aff'    => 0,
                'thumb'        => $member->thumb,
                'app_aff'      => $member->aff,
                'authorId'     => 0,
                'ownerId'      => $content->authorId,
                'mail'         => '',
                'url'          => '',
                'ip'           => client_ip(),
                'agent'        => 'app',
                'text'         => $text,
                'type'         => CommentsModel::TYPE_COMMENT,
                'status'       => $status,
                'is_top'       => $is_top,
                'parent'       => 0,
                'sec_parent'   => 0, //二级评论ID
                'admin_id'     => $this->getUser()->uid
            ];
            $comment = CommentsModel::create($data);
            test_assert($comment, '评论添加失败');
            ContentsModel::find($cid)->increment('commentsNum');
            return $this->ajaxSuccessMsg('操作成功');
        } catch (\Throwable $e) {
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
            $coid = $data['coid'];
            $comment = CommentsModel::find($coid);
            test_assert($comment,'评论不存在');
            test_assert($comment->parent == 0, '一级评论才能操作');
            if ($comment->is_top == CommentsModel::TOP_NO){
                $official = OfficialAccountModel::where('aff', $comment->app_aff)->first();
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