<?php

use service\UserService;
use service\SnsStatService;
use service\StatisticsService;

class RemoteController extends RemoteBaseController
{

    //项目列表
    public function project_listAction()
    {
        try {
            $service = new \service\RemoteUserContentsService();
            $list = $service->projectList();
            return $this->showJson($list);
        }catch (Throwable $e){
            return $e->getMessage();
        }
    }

    public function configAction()
    {
        $Validator = \helper\Validator::make($this->data, [
            'oauth_id'   => 'required',
            'oauth_type' => 'required',
            'version'    => 'required'
        ]);
        if ($Validator->fail($msg)) {
            return $this->showJson($msg);
        }
        $data = $this->data;
        $oauth_type = $data['oauth_type'];
        $lines_url = config('line.url');
        if ($oauth_type == MemberModel::TYPE_WEB){
            $lines_url = config('line.pwa_url');
        }
        $lines_url = explode(',', $lines_url);
        $lines_url = collect($lines_url)->shuffle()->toArray();
        $lines_url = implode(',', $lines_url);

        $req = [
            'timestamp' => strtotime(date('Y-m-d 00:00:00')),
            'config' => [
                'img_upload_url'        => config('upload.img_upload'),
                'mp4_upload_url'        => config('upload.mp4_upload'),
                'mobile_mp4_upload_url' => config('upload.mobile.mp4_upload'),
                'upload_img_key'        => config('upload.img_key'),
                'upload_mp4_key'        => config('upload.mp4_key'),
                'video_encrypt_api'     => config('video.encrypt.api'),
                'video_encrypt_referer' => config('video.encrypt.referer'),
                'video_encrypt_m3u8'    => config('video.encrypt.m3u8'),
                'm3u8_encrypt'          => 0,
                'nav_id'                => 1,//首页获取导航ID
                'github_url'            => config('github.url'),
                'lines_url'             => explode(',', $lines_url),
                'office_site'           => UserService::getShareURL(),
            ],
        ];
        $req['config']['video_encrypt_referer'] = empty($req['config']['video_encrypt_referer'])? 'https://video.iwanna.tv':$req['config']['video_encrypt_referer'];
        trigger_log(json_encode($req));
        $req = array_merge($req, VersionModel::lastVersion($oauth_type));
        if (USER_COUNTRY == 'CN') {
            $req['config']['img_base'] = trim(SNS_IMG_APP_CN, '/') ;
        } else {
            $req['config']['img_base'] = trim(SNS_IMG_APP_US, '/') ;
        }
        
	/*
        $list = MetasModel::query()
            ->selectRaw('mid as id, name')
            ->where('type', MetasModel::TYPE_CATEGORY)
            ->orderBy('order')
            ->get();
        $req['category_list'] = $list;
        */

	$list = CategoriesModel::query()
            ->selectRaw('id, name')
            ->orderBy('sort_order')
            ->get();
        $req['category_list'] = $list;
        $status_list = [
            10 => '全部',
            UserContentsModel::STATUS_WAIT   => '待审',
            UserContentsModel::STATUS_DENIED => '拒绝',
            UserContentsModel::STATUS_PASSED => '通过',
            UserContentsModel::STATUS_DRAFT => '草稿',
        ];
        $tmp = [];
        foreach ($status_list as $k => $v){
            $tmp[] = [
                'id' => $k,
                'name' => $v
            ];
        }
        $req['status_list'] = $tmp;

        return $this->showJson($req);
    }

    public function loginByPasswordAction()
    {
        try {
            $Validator = \helper\Validator::make($this->data, [
                'username' => 'required',
                'password' => 'required',
            ]);
            if ($Validator->fail($msg)) {
                return $this->errorJson($msg);
            }
            $username = $this->data['username'];
            $password = $this->data['password'];
            /** @var UsersModel $user */
            $user = UsersModel::findByUsername($username);
            if (empty($user)) {
                throw new \Exception('用户名或密码错误');
            }
            $hasher = new LibCheckPassword(8, true);
            $hashValidate = $hasher->checkPassword($password, $user->password);
            if (!$hashValidate){
                throw new \Exception('用户名或密码错误');
            }

            $crypt = new LibCryptUser();
            $token = $crypt->encryptToken($user->uid, $this->data['oauth_id'], $this->data['oauth_type']);

            return $this->showJson($token);
        } catch (\Throwable $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    public function create_updateAction()
    {
        try {
            //trigger_log("远程发布端文章-接收：".var_export($this->data,true));
            test_assert($this->uid, '未登录');
            $title = $this->data['title'] ?? null;
            $created = $this->data['created'] ?? null;
            $body = $this->data['body'] ?? null;
            $tags = $this->data['tags'] ?? null;
            $cover = $this->data['cover'] ?? null;
            $category = $this->data['category_id'] ?? null;
            $is_draft = $this->data['is_draft'] ?? 3;
            $id = intval($this->data['id'] ?? 0);
            if (empty($title) || empty($body) || empty($cover)) {
                return $this->errorJson('参数错误');
            }
            //1 发布 3 草稿
            test_assert(in_array($is_draft, [1, 3]), '状态不对');
            $service = new \service\RemoteUserContentsService();
            $title = html_entity_decode($title);
            $title = strip_tags($title);
            $body = html_entity_decode($body);
            $body = strip_tags($body);
            $tags = html_entity_decode($tags);

            $service->createContentsRemote($this->uid, $title, $created, $body,$cover, $tags, $category, $is_draft, $id);
            return $this->successMsg('操作成功');
        } catch (\Throwable $e) {
            trigger_log("远程发布端文章-接收错误：".var_export($e->getMessage(),true));
            return $this->errorJson($e->getMessage());
        }
    }

    public function list_usercontentsAction(): bool
    {
        try {
            test_assert($this->uid, '未登录');
            $status = $this->data['status'] ?? UserContentsModel::STATUS_PASSED;
            $kwy = $this->data['kwy'] ?? '';
            $service = new \service\RemoteUserContentsService();

            $res = $service->listContents($this->uid, $status, $kwy, $this->page , $this->limit);
            return $this->showJson($res);
        } catch (\Throwable $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    public function del_contentsAction(): bool
    {
        try {
            test_assert($this->uid, '未登录');
            $validator = \helper\Validator::make($this->data, [
                'id' => 'required',
            ]);
            if ($validator->fail($msg)) {
                return $this->errorJson($msg);
            }
            $id = $this->data['id'];
            $service = new \service\RemoteUserContentsService();

            $service->delContents($this->uid, $id);
            return $this->successMsg('操作成功');
        } catch (\Throwable $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    public function previewAction(): ?bool
    {
        try {
            test_assert($this->uid, '未登录');
            $body = $this->data['body'] ?? '';
            if (empty($body)) {
                return $this->errorJson('参数错误');
            }
            $body = strip_tags($body);
            $body = \tools\LibMarkdown::parseContent($body);
            return $this->showJson($body);
        } catch (\Throwable $e) {
            return $this->errorJson('参数错误');
        }
    }

    /**
     * R2上传配置
     * @return bool
     */
    public function r2upload_infoAction()
    {
        try {
            test_assert($this->uid, '未登录');
            $data = \service\ObjectR2Service::r2UploadInfo();
            if (!$data) {
                return $this->errorJson('上传配置异常，关闭重试～');
            }
            return $this->showJson($data);
        }catch (Throwable $e){
            return $e->getMessage();
        }
    }

    /**
     * 视频上传
     * @return bool
     */
    public function upload_mvAction()
    {
        try {
            test_assert($this->uid, '未登录');
            $validator = \helper\Validator::make($this->data, [
                'mp4_url' => 'required',
                'cover' => 'required',
            ]);
            if ($validator->fail($msg)) {
                return $this->errorJson($msg);
            }
            //$id = $this->data['id'] ?? 0;
            $mp4_url = htmlspecialchars_decode($this->data['mp4_url']);
            $cover = $this->data['cover'];
            $name = $this->data['name'];
            $upload_type = $this->data['upload_type'] ?? UserUploadModel::UPLOAD_TYPE_COM;
            $service = new \service\RemoteUserContentsService();
            $service->uploadMv($this->uid, $name, $mp4_url, $cover, $upload_type);
            return $this->successMsg('保存成功');
        }catch (Throwable $e){
            return $e->getMessage();
        }
    }

    /**
     * 媒体列表
     * @return bool
     */
    public function mv_listAction()
    {
        try {
            test_assert($this->uid, '未登录');
            $slice_status = $this->data['slice_status'] ?? UserUploadModel::SLICE_WAIT;
            $kwy = $this->data['kwy'] ?? '';
            list($page, $limit) = \helper\QueryHelper::pageLimit();
            $service = new \service\RemoteUserContentsService();
            $list = $service->mvList($this->uid, $slice_status, $kwy, $page, $limit);
            return $this->showJson($list);
        }catch (Throwable $e){
            return $e->getMessage();
        }
    }

    /**
     * 刷评论
     * @return bool
     */
    public function add_commentsAction()
    {
        try {
            test_assert($this->uid, '未登录');
            $validator = \helper\Validator::make($this->data, [
                'id' => 'required',
                'content' => 'required',
                'begin' => 'required',
                'end' => 'required',
            ]);
            if ($validator->fail($msg)) {
                return $this->errorJson($msg);
            }
            $cid = $this->data['id'];
            $content = $this->data['content'];
            $begin = $this->data['begin'];
            $end = $this->data['end'];
            if($begin > $end){
                throw new Exception('开始时间必须小于结束时间');
            }
            $service = new \service\RemoteUserContentsService();
            $service->addComments($cid, $content, $begin, $end);
            return $this->successMsg("添加任务成功");
        }catch (Throwable $e){
            return $e->getMessage();
        }
    }

    public function userinfoAction():bool {
        return $this->showJson(array());
    }

    public function on_remote_slice_failAction()
    {
        try {
            /**
             * @var UserUploadModel $record
             */
            $id = (int)($_POST['mv_id'] ?? '');
            $msg = trim($_POST['msg'] ?? '');
            $record = UserUploadModel::where('id', $id)->first();
            if (!$record) {
                return;
            }
            $data = [
                '当前项目' => '[WEB]' . config('pay.app_name'),
                '当前模块' => '远程发布',
                '视频记录' => $record->id,
                '视频链接' => $record->mp4_url,
                '错误详情' => $msg,
            ];
            $this->send_msg($data);
        } catch (Throwable $e) {
            wf('出现异常', $e->getMessage());
        }
    }
    private function send_msg($kv)
    {
        foreach ($kv as $k => $v) {
            $lines[] = $k . ': ' . $v;
        }

        $text = implode("\n", $lines);
        $data = [
            'chat_id' => config('mv.notify.slice.chat_id'),
            'text'    => $text,
        ];
        $url = sprintf("%s%s/sendMessage",config('mv.notify.url'), config('mv.notify.token'));
        \tools\HttpCurl::post($url, $data);
    }

    /**
     * 报表统计
     *
     * @return void
     */
    public function snsstatAction()
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: *");
        header('Content-Type: application/json');

        $json = ['code' => 200, 'msg' => '', 'data' => []];
        $data = $this->data;

        try{
            if(isset($data['checkToken']))unset($data['checkToken']);
            $config = SnsStatService::getConfig();
            if(empty($config['app_id']) || empty($config['agent_id']) || empty($config['app_secret'])){
                throw new Exception('插件缺少配置，请联系管理员');
            }
            
            SnsStatService::checkSignByPing($data, $config);
            $date_day = $data['date']??'';
            if( strtotime($date_day)===false ){
                throw new Exception('日期格式不正确');
            }

            $date_day = date('Y-m-d', strtotime($date_day));

            $dau = StatisticsService::getOnlineData($date_day);
            $json['data'] = [
                "dau"=>(string)$dau,
                "install"=>'0',
                "recharge"=>'0',
                "date"=>date('Ymd', strtotime($date_day)),
                //"self_recharge"=> 0
            ];

            $recharge = SnsStatService::getDauStat($config['agent_id'], $date_day);
            if(isset($recharge['status']) && $recharge['status']==1){
                $json['data']['install'] = $recharge['data']['member'] ?? '0';
                $json['data']['recharge'] = $recharge['data']['order_charge'] ?? '0.00';
                $json['data']['recharge'] += $recharge['data']['order_charge_coin'] ?? '0.00';
                $json['data']['recharge'] = $json['data']['recharge'] .'';
            }

            echo json_encode($json);
        }catch(Exception $e){
            $json['code'] = -1;
            $json['msg'] = $e->getMessage().', '.$config['app_id'];
            trigger_log(__FUNCTION__."报表统计错误：". $e->getMessage()." data:".var_export($data,true));
            echo json_encode($json);
        }
    }
}
