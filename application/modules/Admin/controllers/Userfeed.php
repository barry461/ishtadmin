<?php

use service\AppFeedSystemService;

/**
 * Class UserfeedController
 * @author xiongba
 * @date 2020-06-05 08:06:15
 */
class UserfeedController extends BackendBaseController
{

    use \repositories\HoutaiRepository;

    /**
     * 列表数据过滤
     * @return Closure
     */
    protected function listAjaxIteration()
    {
        return function ($item) {
            /** @var UserFeedModel $item */
            if ($item->message_type == 2){
                $url =  url_image($item->question);
                $item->question_str = sprintf('<img src="%s" onclick="url_cover_show(\'%s\')"/>' , url_image($item->question),$url);
            }else{
                $item->question_str = $item->question;
            }
            $item->admin_str = '';
            if ($item->manager){
                $item->admin_str = $item->manager->username;
            }
            // if ($item->image_1){
            //     $url =  url_upload($item->image_1);
            //     $item->image_str = sprintf('<img src="%s" onclick="url_cover_show(\'%s\')"/>' , url_upload($item->question),$url);
            // }

            $item->status_name = UserFeedModel::FEED_STATUS[$item->status];
            if ($item->user){
                $item->uid = $item->user->aff;
            }else{
                $item->uid = '';
            }
            $item->vip_level_str = MemberModel::VIP_LEVEL[$item->user->vip_level] ?? '未知';
            $result = $item->toArray();
            return array_merge($result, $this->getMemberBasis($item->uuid));
        };
    }

    protected function getLocatonStr($ip): string
    {
        $position = \tools\IpLocation::getLocation($ip);
        if (!is_array($position) || empty($position)){
            return '';
        }
        //$country = $position['country'] ?? '中国';
        $city = $position['city'] ?? '火星';
        $province = $position['province'] ?? '火星';
        return sprintf('%s%s',$province,$city);
    }


    protected function formatKey($key, $value)
    {
        if (!preg_match_all("#^([a-zA-Z_\d]+)$#i", trim($key))) {
            return [false , $value];
        }

        if ($key == 'aff_code') {
            $value = get_num($value);
            $key= 'aff';
        }

        if ($key === 'aff'){
            $data = MemberModel::find($value);
            if (!empty($data)){
                $value = $data->uuid;
                $key = 'uuid';
            }
        }


        return [$key, $value];
    }

    /**
     * 试图渲染
     * @return string
     */
    public function indexAction()
    {
        $this->assign('huifuList' , FeedQuickModel::getHuifuSelectOptions());
        $this->display();
    }

    /**
     * 详情
     * @param $uuid
     */
    public function detailAction()
    {
        $uuid = $_GET['uuid'] ?? null;
        $items = UserFeedModel::query()
            ->where('uuid', $uuid)
            ->orderBy('created_at', 'desc')
            ->get();

        $this->assign('uuid', $uuid);
        $this->assign('items', $items);
        $this->assign('reply', $this->getFeedItems());
        $this->assign('img_url', TB_IMG_ADM_US);
        $this->display('detail');
    }


    private function getFeedItems()
    {
        // $items = redis()->getWithSerialize(FeedQuickModel::REDIS_FEED_ITEMS);
        // if (!$items) {
            $items = FeedQuickModel::query()->get()->toArray();
            foreach ($items as &$item){
                $item['title'] = replace_share($item['title']);
            }
            // redis()->setWithSerialize(FeedQuickModel::REDIS_FEED_ITEMS, $items, 86400);
        // }
        return $items;
    }


    /**
     * 批量回复
     */
    public function doBatchBackAction()
    {
        $content = $_POST['content'] ?? null;
        $uuids = $_POST['uuids'] ?? '';
        $uuids = explode(',', $uuids);

        if (empty($content)) {
            return $this->ajaxError('请选择回复内容');
        }
        if (!$uuids) {
            return $this->ajaxError('用户uuid不能为空');
        }

        foreach ($uuids as $uuid) {
            $data = [
                'uuid'         => $uuid,
                'question'     => $content,
                'message_type' => 1,
                'status'       => 2,
                'admin_id'       => $this->getUser()->uid,
            ];
            UserFeedModel::create($data);
            UserFeedModel::query()->where('uuid', $uuid)->update(['is_replay' => 1]);
        }
        return $this->ajaxSuccess('回复成功');
    }

    /**
     * 批量屏蔽
     */
    public function screenAction()
    {
        $uuids = $_POST['uuids'] ?? '';
        $uuids = explode(',', $uuids);

        try {
            UserFeedModel::query()
                ->whereIn('uuid', $uuids)
                ->get()
                ->map(function ($item) {
                    /** @var UserFeedModel $item */
                    if ($item->is_replay == UserFeedModel::IS_SCREEN) {
                        return;
                    }
                    transaction(function() use($item){
                        $item->replay_old = $item->is_replay;
                        $item->is_replay = UserFeedModel::IS_SCREEN;
                        $item->admin_id = $this->getUser()->uid;
                        $isOk = $item->save();
                        test_assert($isOk, '系统异常');
                    });
                });
            return $this->ajaxSuccess('统一屏蔽成功');
        } catch (Throwable $e) {
            return $this->ajaxError($e->getMessage());
        }
    }

    /**
     * 批量恢复屏蔽
     */
    public function unScreenAction()
    {
        $uuids = $_POST['uuids'] ?? '';
        $uuids = explode(',', $uuids);

        try {
            UserFeedModel::where('is_replay', UserFeedModel::IS_SCREEN)
                ->whereIn('uuid', $uuids)
                ->get()
                ->map(function ($item) {
                    transaction(function() use($item){
                        $item->is_replay = $item->replay_old;
                        $item->replay_old = UserFeedModel::IS_UN_READ;
                        $item->admin_id = $this->getUser()->uid;
                        $isOk = $item->save();
                        test_assert($isOk, '系统异常');
                    });
                });
            return $this->ajaxSuccess('批量恢复屏蔽成功');
        } catch (Throwable $e) {
            return $this->ajaxError($e->getMessage());
        }
    }

    /**
     * 回复
     */
    public function backAction()
    {
        $uuid = $this->post['uuid'] ?? null;
        $reply =  $this->post['reply'] ?? null;
        $type = $this->post['type'] ?? 1;
        if (!$uuid || !$reply) {
            return $this->ajaxError('参数有误！');
        }
        $data = [
            'uuid'         => $uuid,
            'question'     => $reply,
            'message_type' => $type,
            'status'       => 2,
            'admin_id'       => $this->getUser()->uid
        ];
        $model = UserFeedModel::create($data);
        UserFeedModel::query()->where('uuid', $uuid)->update(['is_replay' => 1]);
        /** @var MemberModel $member */
        $member = MemberModel::where('uuid' , $uuid)->first();

        // 工单系统处理
        if (setting('enable_public_feed' , 0)){
            (new AppFeedSystemService())->sendRemoteRequest(null, [
                    'app'       => VIA,
                    'uuid'      => $model->uuid,
                    'app_type'  => $member->oauth_type,
                    'aff'       => $member->aff,
                    'product'   => 0,
                    'type'      => $model->message_type,
                    'nickname'  => $member->nickname,
                    'content'   => $model->question ?: 'xxx图片',
                    'version'   => $member->app_version,
                    'ip'        => USER_IP,
                    'vip_level' => MemberModel::VIP_LEVEL[$member->vip_level] ?? '大众',
                    'status'    => 1,
                ]
            );
        }
        return $this->ajaxSuccess('回复成功');
    }

    /**
     *  统一回复
     */
    public function someBackAction()
    {
        $content = trim($_POST['content']) ?? null;
        $uuids = $_POST['uuids'] ?? '';
        $uuids = explode(',', $uuids);
        $uuids = array_unique($uuids);

        if (!$content || !$uuids) {
            return $this->ajaxError('uuid和回复内容不能为空', 0);
        }

        foreach ($uuids as $uuid) {
            $data = [
                'uuid'         => $uuid,
                'question'     => $content,
                'message_type' => 1,
                'status'       => 2,
            ];
            UserFeedModel::create($data);
            $uuid_arr[] = $uuid;

            UserFeedModel::remoteQuest($uuid, $content);
        }
        UserFeedModel::query()->whereIn('uuid', $uuid_arr)->update(['is_replay' => 1]);
        return $this->ajaxSuccess('回复成功');
    }

    public function listAjaxWhere()
    {
        if (isset($_GET['where'])) {
            return [];
        }
        return [
            ['is_replay', '=', UserFeedModel::IS_UN_READ]
        ];
    }

    // 获取数据
    public function listAjaxAction() {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            return $this->ajaxError('加载错误');
        }
        $page = intval($_GET['page'] ?? 1);
        $limit = intval($_GET['limit'] ?? 10); 
        $page  = ($page - 1) * $limit;
        $where = array_merge(
            $this->getSearchWhereParam(),
            $this->getSearchLikeParam()
        );
        foreach ($where as $key => $value) {
            if (isset($value[0]) && $value[0] == 'uuid') {
                $where[$key][0] = 'user_feed.uuid';
            }
        }

        $data = UserFeedModel::query()
                ->leftJoin('members', 'members.uuid', '=', 'user_feed.uuid')
                ->leftJoin('managers', 'managers.uid', '=', 'user_feed.admin_id')
                ->where($where)
                ->select(['members.*', 'user_feed.*','managers.username as admin_str'])
                ->offset($page)
                ->limit($limit)
                ->groupBy('user_feed.uuid');

        $data = $data->orderBy('user_feed.updated_at', 'desc')->get();

        if ($data) {
            $data = $data->toArray();
            foreach ($data as $key => &$value) {
                $value['created_str'] = $value['created_at'];
                $value['status_name'] = UserFeedModel::FEED_STATUS[$value['status']];
                $value['vip_level_str'] = MemberModel::VIP_LEVEL[$value['vip_level']] ?? '未知';
                $value['uid'] = $value['aff'] ?: '';
                $value['member_nickname'] = $value['nickname'] ?? '';
                $value['member_phone'] = $value['phone'] ?? '';
                $value['member_thumb'] = url_avatar($value['thumb'] ?? null);
                $value['member_lastip'] = $value['lastip'] ?: $value['regip']; 
                $value['member_oauthstr'] = $value['oauth_type'] . ' - ' . $value['app_version'];
                $value['member_uuid'] = $value['uuid'];
                $value['expired_at'] = strtotime($value['expired_at']) > TIMESTAMP ? $value['expired_at'] : '';
                $value['member_isvip'] = strtotime($value['expired_at']) > TIMESTAMP ? 1 : 0;
                $value['no_reply_ct'] =  UserFeedModel::where('uuid',$value['uuid'])->where('is_replay',0)->count();
                $record = UserFeedModel::where('uuid',$value['uuid'])->orderByDesc('created_at')->first();
                $value['message_type'] = $record->message_type;
                $value['question'] = $record->question;
                $value['image_1'] = $record->image_1;
                if ($value['message_type'] == 2) {
                    $url =  url_image($value['question']);
                    $value['question_str'] = sprintf('<img src="%s" onclick="url_cover_show(\'%s\')"/>' , url_image($value['question']),$url);
                } else {
                    $value['question_str'] = $value['question'];
                }

                if ($value['image_1']){
                    $url =  url_image($value['image_1']);
                    $value['image_str'] = sprintf('<img src="%s" onclick="url_cover_show(\'%s\')"/>' , url_image($value['question']),$url);
                }
                $value['location_str'] = '';
                if ($value['user_ip']){
                    $value['location_str'] = $this->getLocatonStr($value['user_ip']);
                }
            }
            $sub = UserFeedModel::query()
                        ->leftJoin('members', 'members.uuid', '=', 'user_feed.uuid')
                        ->where($where)
                        ->select(['user_feed.id'])
                        ->groupBy('user_feed.uuid');

            $total = \DB::table(\DB::raw("({$sub->toSql()}) as sub"))->mergeBindings($sub->getQuery())->count();
        }

        $result = [
            'count' => empty($data) ? 0 : $total,
            'data'  => $data,
            "msg"   => '',
            'code'  => 0
        ];
        return $this->ajaxReturn($result);
    }  

    /**
     * 获取本控制器和哪个model绑定
     * @return string
     */
    protected function getModelClass(): string
    {
        return UserFeedModel::class;
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

    public function ddAction(){

        /**
         * exportExcel($data,$title,$filename);
         * 导出数据为excel表格
         * @param $data    一个二维数组,结构如同从数据库查出来的数组
         * @param $title   excel的第一行标题,一个数组,如果为空则没有标题
         * @param $filename 下载的文件名
         * @examlpe
         * exportExcel($arr,array('id','账户','密码','昵称'),'文件名!');
         */
        function exportExcel($data = array(), $title = array(), $filename = 'report')
        {
            ob_end_clean();
            ob_start();
            header("Content-type:application/octet-stream");
            header("Accept-Ranges:bytes");
            header("Content-type:application/vnd.ms-excel");
            header("Content-Disposition:attachment;filename=" . $filename . ".xls");
            header("Pragma: no-cache");
            header("Expires: 0");
            //导出xls开始
            if (!empty($title)) {
                foreach ($title as $k => $v) {
                    $title[$k] = iconv("UTF-8", "GB2312", $v);
                }
                $title = implode("\t", $title);
                echo "$title\n";
            }
            if (!empty($data)) {
                foreach ($data as $key => $val) {
                    foreach ($val as $ck => $cv) {
                        $data[$key][$ck] = iconv("UTF-8", "GB2312", $cv);
                        //$data[$key][$ck] = mb_convert_encoding($cv, "gb2312", "UTF-8");
                    }
                    $data[$key] = implode("\t", $data[$key]);
                }

                echo implode("\n", $data);
            }
        }
        $total = array();
        $d =  date('Y-m-d');
        UserFeedModel::query()
            ->where('created_at','>',$d
            )->where('status',1)
            ->where('message_type',1)
            ->chunkById(100,function ($items) use (&$total){
                $items->each(function (UserFeedModel $item) use (&$total){
                    $temp = [];
                    /** @var MemberModel $member */
                    $member = MemberModel::where('uuid',$item->uuid)->first();
                    if ($member){
                        $temp['aff'] = $member->aff;
                    }else{
                        $temp['aff'] = '100010';
                    }
                    $item->question = trim($item->question);
                    if (str_contains($item->question,"\n")){
                        $item->question = str_replace("\n",'',$item->question);
                    }
                    if (strlen($item->question) > 50){
                        $item->question = substr($item->question,0,40);
                    }
                    $temp['question'] = $item->question;
                    $temp['ip'] = $item->user_ip;
                    $temp['position'] = '';
                    if ($item->user_ip){
                        $temp['position'] = $this->getLocatonStr($item->user_ip);
                    }
                    $temp['created_at'] = $item->created_at;
                    $total[] = $temp;
                });
            });
        exportExcel($total, array('用户aff', '用户反馈内容', '用户ip', 'ip位置','反馈时间'), 'feed');
        exit;
    }
    public function ddnewAction(){
        //时间
        $d = date('Y-m-d');
        set_time_limit(0);
        $spredsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spredsheet->getActiveSheet();
        //设置标题
        $sheet->setTitle(date('Ymd'));
        //设置表头
        $k = 1;
        $sheet->setCellValue('A' . $k, '用户aff');
        $sheet->setCellValue('B' . $k, '用户反馈内容');
        $sheet->setCellValue('C' . $k, '用户ip');
        $sheet->setCellValue('D' . $k, 'ip位置');
        $sheet->setCellValue('E' . $k, '反馈时间');
        //设置表格宽度
        $sheet->getColumnDimension('B')->setWidth(120);
        //垂直居中
        $sheet->getStyle('B')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        //获取数据
        $k = 2;
        UserFeedModel::query()
            ->where('created_at','>',$d)
            ->where('status',1)
            ->chunkById(100,function ($items) use (&$k, $sheet){
                $items->each(function (UserFeedModel $item) use (&$k, $sheet){
                    /** @var MemberModel $member */
                    $member = MemberModel::where('uuid',$item->uuid)->first();
                    if ($member){
                        $sheet->setCellValue('A' . $k, $member->aff);
                    }else{
                        $sheet->setCellValue('A' . $k, 100010);
                    }
                    //文字
                    if ($item->message_type == 1){
                        $item->question = trim($item->question);
                        if (str_contains($item->question,"\n")){
                            $item->question = str_replace("\n",'',$item->question);
                        }
                        if (strlen($item->question) > 50){
                            $item->question = substr($item->question,0,40);
                        }
                        $sheet->setCellValue('B' . $k, $item->question);
                    }else{
                        $base_url = "https://imgpublic.ycomesc.live";
                        $url = $base_url. '/' .trim($item->question, '/');
                        $file_info = pathinfo($url);
                        $basename = $file_info['basename'];
                        $image = file_get_contents($url);
                        $to = APP_PATH . '/storage/data/images/feed/' . $basename;
                        $dirname = dirname($to);
                        if (!is_dir($dirname) || !file_exists($dirname)) {
                            mkdir($dirname, 0755, true);
                        }
                        file_put_contents($to, $image);
                        list($width, $height) = getimagesize($to);
                        $drawing[$k] = new PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                        $drawing[$k]->setName('img');
                        $drawing[$k]->setDescription('Logo');
                        $drawing[$k]->setPath($to);
                        $drawing[$k]->setWidth($width * 0.5);
                        $drawing[$k]->setHeight($height * 0.5);
                        $drawing[$k]->setCoordinates('B'.$k);
                        $drawing[$k]->setOffsetX(0);
                        $drawing[$k]->setOffsetY(0);
                        $drawing[$k]->setWorksheet($sheet);
                        $sheet->getRowDimension($k)->setRowHeight($height * 0.5 * 0.75);
                    }
                    $sheet->setCellValue('C' . $k, $item->user_ip);
                    $position = '';
                    if ($item->user_ip){
                        $position = $this->getLocatonStr($item->user_ip);
                    }

                    $sheet->setCellValue('D' . $k, $position);
                    $sheet->setCellValue('E' . $k, $item->created_at);
                    $k++;
                });
            });
        $file_name = date('Y-m-d', time()) . rand(1000, 9999);
        $file_name = $file_name . ".xls";
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $file_name . '"');
        header('Cache-Control: max-age=0');
        $writer = PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spredsheet, 'Xls');
        //  注意createWriter($spreadsheet, 'Xls') 第二个参数首字母必须大写
        $writer->save('php://output');
    }


}