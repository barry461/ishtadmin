<?php

use service\CommonService;
use service\CommunityService;
use service\UserService;
use service\InfoService;
use Carbon\Carbon;
use service\ChannelService;
use Tbold\Serv\biz\BizAppVisit;

/**
 * Class HomeController
 * 公共功能 不需要用户信息相关的
 */
class HomeController extends BaseController
{
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
        $member = $this->member;
        $commonService = new CommonService();
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
                'img_upload_url'        => config('upload.img_upload') ,
                'mp4_upload_url'        => config('upload.mp4_upload'),
                'mobile_mp4_upload_url' => config('upload.mobile.mp4_upload'),
                'upload_img_key'        => config('upload.img.key'),
                'upload_mp4_key'        => config('upload.mp4.key'),
                'office_site'           => UserService::getShareURL(),
                'official_group'        => setting('tg_group', 'https://t.me/kkcmguanfang'),
                'video_encrypt_api'     => config('video.encrypt.api'),
                'video_encrypt_referer' => config('video.encrypt.referer'),
                'video_encrypt_m3u8'    => config('video.encrypt.m3u8'),
                'm3u8_encrypt'          => 0,
                'nav_id'                => 1,//首页获取导航ID
                'github_url'            => config('github.url'),
                'lines_url'             => explode(',', $lines_url),
                'tips_share_text'       => '成功邀请1人，获得3天会员', // 分享页面,
                'solution'              => UserService::getShareURL() . '#android_poisoning.html', // 安装爆毒,
                'forever_www'           => setting('forever_www', 'https://51cg.life'),
                'post_rule'             => setting('post:rule' , ''),
                'tg_up_auth'            => setting('tg_up_auth' , ''),
                'days'                  => 60,
                'secret_tips'           => setting('secret_tips', '当前内容为吃瓜封禁秘闻#仅对少部分会员用户开放'),
                'login_tips'            => setting('login_tips', 0),
            ],
        ];
        $req['help'] = $commonService->helpFeedbackList();
        $req = array_merge($req, VersionModel::lastVersion($oauth_type));
        if (USER_COUNTRY == 'CN') {
            $req['config']['img_base'] = trim(SNS_IMG_APP_CN, '/') ;
        } else {
            $req['config']['img_base'] = trim(SNS_IMG_APP_US, '/') ;
        }

        //$ads = AdsModel::listPos(AdsModel::POSITION_SCREEN);
        $ads = CommonService::getAds($member,AdsModel::POSITION_SCREEN);
        $req['ads'] = $ads ? collect($ads)->shuffle()->first() : NULL;

        $req['notice'] = CommonService::getNotice($member, NoticeModel::POS_HOME);
        $req['pop_ads'] = CommonService::getNotice($member, NoticeModel::POS_HOME,false);

        //社区sort-tab
        $req['community_sort_tab'] = [
            ['name' => '最新', 'sort' => 'new'],
            ['name' => '热门', 'sort' => 'hot'],
            ['name' => '文字', 'sort' => 'txt'],
            ['name' => '图片', 'sort' => 'pic'],
            ['name' => '视频', 'sort' => 'video']
        ];

        $req['rank_config'] = [
            [
                'current'       => 1,
                'id'            => 1,
                'name'          => '吃瓜热榜',
                'type'          => 'list',
                'api_list'      => 'api/rank/hot_list',
                'params_list'   => ['type' => 'hot'],
            ],
            [
                'current'       => 0,
                'id'            => 2,
                'name'          => '日榜单',
                'type'          => 'list',
                'api_list'      => 'api/rank/rank_list',
                'params_list'   => ['type' => 'day'],
            ],
            [
                'current'       => 0,
                'id'            => 3,
                'name'          => '周榜单',
                'type'          => 'list',
                'api_list'      => 'api/rank/rank_list',
                'params_list'   => ['type' => 'week'],
            ],
            [
                'current'       => 0,
                'id'            => 4,
                'name'          => '月榜单',
                'type'          => 'list',
                'api_list'      => 'api/rank/rank_list',
                'params_list'   => ['type' => 'month'],
            ],
        ];

        $tab = [
            [
                'current'       => 0,
                'id'            => 1000,
                'name'          => '关注',
                'type'          => 'follow',
                'api_list'      => 'api/community/list_construct',
                'params_list'   => ['type' => 'follow'],
            ],
            [
                'current'       => 0,
                'id'            => 2000,
                'name'          => '订阅',
                'type'          => 'subscription',
                'api_list'      => 'api/community/list_construct',
                'params_list'   => ['type' => 'subscription'],
            ],
            [
                'current' => 1,//当前tab 默认展示
                'id'            => 3000,
                'name'          => '推荐',
                'type'          => 'recommend',
                'api_list'      => 'api/community/list_construct',
                'params_list'   => ['type' => 'recommend', 'sort' => 'new'],
            ],
        ];
        $service = new CommunityService();
        $navs = $service->getListCates();
        /** @var PostTopicCategoryModel[] $navs */
        $_data = [];
        foreach ($navs as $val) {
            $_data['current'] = 0;
            $_data['id'] = $val->id;
            $_data['name'] = $val->name;
            $_data['type'] = 'category';
            $_data['api_list'] = 'api/community/list_construct';
            $_data['params_list'] = ['type' => 'category', 'sort' => 'new', 'category_id' => $val->id];
            $tab[] = $_data;
        }
        $req['community_config'] = $tab;

        $app_show = (int)setting('home_app_list_show', 0);
        $req['apps'] = [];
        if ($app_show == 1){
            $req['apps'] = NoticeAppModel::listApps();
        }
        $req['play_tip'] = setting('play_tip', '');
        $req['ai_need_coins'] = intval(setting('ai_need_coins',20));
        $req['reward_coins'] = setting('reward_coins','1,5,10,20,50,100');
        return $this->showJson($req);
    }

    public function domainCheckReportAction()
    {
        $Validator = \helper\Validator::make($this->data, [
            'list' => 'list'
        ]);
        if ($Validator->fail($msg)) {
            return $this->errorJson($msg);
        }
        $commonService = new CommonService();
        $rs = $commonService->domainCheckReport($this->data['list'], $this->member);
        if ($rs) {
            return $this->showJson('成功');
        }
    }

    public function getContactListAction()
    {
        $list['office_contact'] = json_decode(setting('office_contact'));
        $list['download_link'] = json_decode(setting('download_link'));
        return $this->showJson($list);
    }

    public function appclickAction()
    {
        if (!isset($this->data['type'])) {
            $this->data['type'] = DayClickModel::TYPE_APP;
        }
        return $this->forward('Api', 'Home', 'click_report');
    }

    // 点击上报
    public function click_reportAction(): bool
    {
        try {
            $Validator = \helper\Validator::make($this->data, [
                'type' => 'required',
                'id'   => 'required'
            ]);
            $rs = $Validator->fail($msg);
            test_assert(!$rs, $msg);

            $id = $this->data['id'];
            $type = $this->data['type'];

            $commonService = new CommonService();
            if (CommonService::isPcQuest($this->member->oauth_type)){
                $commonService->pcClickReport($type,$id);
            }else{
                $commonService->appClickReport($type,$id);
            }
            return $this->successMsg('成功');
        } catch (Throwable $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    // 上报渠道v2数据
    public function visit_reportAction(): bool
    {
        try {
            $Validator = \helper\Validator::make($this->data, [
                'type'   => 'required|enum:1,2',
                'second' => 'required|min:1',
            ]);
            $rs = $Validator->fail($msg);
            test_assert(!$rs, $msg);

            $type = $this->data['type'];
            $second = $this->data['second'];
            switch ($type) {
                case 1:
                    ChannelService::reportVisit($this->member, USER_IP, BizAppVisit::ID_WATCH_MV, $second);
                    break;
                case 2:
                    ChannelService::reportVisit($this->member, USER_IP, BizAppVisit::ID_DWELL_TIME, $second);
                    break;
            }
            return $this->successMsg('成功');
        } catch (Throwable $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    // 点击上报
    public function error_reportAction(): bool
    {
        return $this->showJson([
            'id' => 0,
            'status' => 1,
            'url' => ''
        ]);
        try {
            $id = $this->data['id'] ?? 0;
            $text = $this->data['text'];
            $scr_img = $this->data['scr_img'] ?? '';
            //更新截屏地址
            if ($id){
                if ($scr_img){
                    $domain = DomainErrorLogModel::find($id);
                    if ($domain){
                        $domain->scr_img = $scr_img;
                        $domain->save();
                    }
                }
                return $this->successMsg('成功');
            }
            $text = htmlspecialchars_decode($text);
            $text1 = json_decode($text,true);
            if (json_last_error() != JSON_ERROR_NONE) {
                $text1 = $text;
            }else{
                if ($text1['status_code'] == 650){
                    return $this->successMsg('此信息不需要');
                }
            }
            if (is_array($text1) && !array_key_exists('test_host',$text1)){
                return $this->errorJson('系统版本过低。');
            }
            $data = [
                'server' => $_SERVER,
                'report' => $text1,
            ];
            $domain = DomainErrorLogModel::create(
                [
                    'ip' => USER_IP,
                    'position' => $this->position['area'],
                    'city' => $this->position['city'],
                    'text' => json_encode($data),
                    'aff' => $this->member->aff,
                    'scr_img' => $scr_img,
                    'created_at' => \Carbon\Carbon::now()
                ]
            );
            return $this->showJson([
                'id' => $domain->id,
                'status' => 1,
                'url' => ''
            ]);
        } catch (Throwable $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    /**
     * R2上传配置
     * @return bool
     */
    public function r2upload_infoAction()
    {
        try {
            $this->verifyMemberSayRole();
            $data = \service\ObjectR2Service::r2UploadInfo();
            if (!$data) {
                return $this->errorJson('上传配置异常，关闭重试～');
            }
            return $this->showJson($data);
        }catch (Throwable $e){
            return $e->getMessage();
        }
    }

}
