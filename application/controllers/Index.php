<?php

use Illuminate\Support\Collection;
use Yaf_Controller_Abstract;
use service\CommonService;
use service\UserService;
use service\ChannelService;

class IndexController extends Yaf_Controller_Abstract
{
    public function app_jsAction()
    {
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        if (str_contains($ua, 'MicroMessenger') || str_contains($ua, ' QQ')) {
            $aff = $_GET['aff'] ?? '';
            redis()->incr('tencent', 1);
            if (!empty($aff)) {
                redis()->sAdd('tencent:aff', $aff . '-' . client_ip());
            }
        }
    }

    private function get_top_domain($host): string
    {
        $data = explode('.', $host);
        $count = count($data);
        if (preg_match('/\.(com|net|org|gov|edu)\.cn$/', $host)) {
            $domain = $data[$count - 3] . '.' . $data[$count - 2] . '.' . $data[$count - 1];
        } else {
            $domain = $data[$count - 2] . '.' . $data[$count - 1];
        }
        return $domain;
    }

    protected function getVars(): array
    {
        $my_url = UserService::getShareURL();
        $params = $this->getRequest()->getParams();
        $code = trim($params['code'] ?? '');
        if (!$code) {
            $code = trim($_GET['code'] ?? '');
        }
        redis()->incr('total:welcome');
        if (!empty($code)) {
            $str = $code . '-' . client_ip();
            if (redis()->sIsMember('tencent:aff', $str)) {
                redis()->incr('tencent:welcome');
            }
        }
        jobs([SysTotalModel::class, 'incrBy'], ['welcome']);
        //分享邀请
        list($aff_id, $aff, $channel_num) = [0, $code, 0];
        $isOpenWebApp = 0;
        do {
            if (empty($code)) {
                break;
            }
            $aff_id = (int)get_num($code);
            if (empty($aff)) {
                break;
            }
            $member = UserService::getUserByAff($aff_id);
            if (empty($member)) {
                break;
            }
            $my_url .= '?code=' . $aff;
            $channel = trim($member->channel);
            if (in_array($channel, ['', 'self'])) {
                break;
            }
            jobs([SysTotalModel::class, 'incrBy'], ['channel:welcome']);
            jobs([SysTotalModel::class, 'incrBy'], ["channel:visit:$channel"]);// 渠道的访问的次数
            redis()->set('aff:ip:' . md5(client_ip()), $member->aff, 1800);

            // 上报渠道v2数据
            $referer = $_SERVER['HTTP_REFERER'] ?? '';
            ChannelService::reportReferrer($channel, USER_IP, $referer);//使用cookie保存referer?
            $info = json_encode(['referer' => $referer, 'channel' => $channel]);
            setcookie('channel_info', $info, [
                'expires'  => time() + 31536000,
                'path'     => '/',
                'domain'   => $this->get_top_domain($_SERVER['HTTP_HOST']),
                'secure'   => false,
                'httponly' => false,
            ]);

            $channel = yac()->fetch('web-channel-' . $channel, function () use ($channel) {
                return ChannelModel::query()
                    ->where('channel_id', $channel)
                    ->first();
            });
            $isOpenWebApp = $channel ? (int)$channel->web_stat : 0;
        } while (false);
        $android = CommonService::getleastVersion(VersionModel::TYPE_ANDROID, VersionModel::STATUS_SUCCESS);
        $ios = CommonService::getleastVersion(VersionModel::TYPE_IOS, VersionModel::STATUS_SUCCESS);
        $mac = CommonService::getleastVersion(VersionModel::TYPE_MAC, VersionModel::STATUS_SUCCESS);
        $windows = CommonService::getleastVersion(VersionModel::TYPE_WIN, VersionModel::STATUS_SUCCESS);
        //$pwa_url = '/index/index/mobileConfig?' . http_build_query(['aff_code' => $code], '', '&');
        $pwa_url = '/index/index/pwa?' . http_build_query(['aff_code' => $code], '', '&');
        $pwa_url2 = '/index/index/mobileConfig?' . http_build_query(['aff_code' => $code], '', '&');
        $share = $aff_id ? 'cgqz_aff:' . $aff : 'cgqz_aff:'; //固定格式
        list($linkCss, $linkHtml) = yac()->fetch("link-css", function () {
            return $this->getLinkWithCss();
        });
        //轮播图
        $rotates = RotateImagesModel::listRotate(10);
        //常见问题
        $user_help = cached(\UserHelpModel::REDIS_USER_HELP_GW_LIST)
            ->chinese('官网常见问题')
            ->fetchPhp(function (){
                return \UserHelpModel::query()->select(['question','answer'])->where("status", 0)->get()->map(function ($item){
                    $txt = replace_share($item->answer);
                    $reg = '/(\b(https|http):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/i';
                    preg_replace_callback($reg,function ($match) use (&$txt){
                        $url = $match[1];
                        $str = sprintf('<a href="%s"  target="_blank">%s</a>', $url,$url);
                        $txt = str_replace($url,$str,$txt);
                    },$txt);
                    $item->answer = $txt;
                    return $item;
                })->toArray();
            });

        $day = (int)date('d');
        $site = sprintf("https://w%d.%s",$day,web_site('chg'));
        $affQuery = [
            'time' => 1692040646,
            'cgqz_aff' => ''
        ];
        if ($isOpenWebApp){
            $affQuery['cgqz_aff'] = $code;
        }
        $web_app_url = $site . '?' . http_build_query($affQuery, '', '&');

        return [
            'android' => $android,
            'ios' => $ios,
            'share' => $share,
            'pwa_url' => $pwa_url,
            'pwa_url2' => $pwa_url2,
            'title' => setting('title', '吃瓜APP'),
            'keyword' => setting('keyword', '吃瓜APP'),
            'description' => setting('description', '吃瓜APP'),
            'tg_group' => setting('tg_group', ''),
            'tg_sw' => setting('tg_sw', ''), // 外事号
            'twitter' => setting('twitter', ''),
            'potato_sw' => setting('potato_sw', ''),
            'potato_group' => setting('potato_group', ''),
            'app_center_url' => setting('app_center_url', ''),
            'luodiye_emal' => setting('luodiye_emal', ''),
            'luodiye_guanwang_url' => setting('luodiye_guanwang_url', ''),
            'luodiye_wzfb' => setting('luodiye_wzfb', ''),
            'mac_download' => $mac,
            'windows_download' => $windows,
            'aff_code' => $aff,
            'rotates' => $rotates,
            'user_help' => $user_help,
            'web_url' => setting('web_url', ''),
            'prevent_lost1_url' => setting('prevent_lost1_url', ''),
            'prevent_lost2_url' => setting('prevent_lost2_url', ''),
            'and_down_bk_url' => setting('and_down_bk_url', ''),
            'ios_down_bk_url' => setting('ios_down_bk_url', ''),
            'web_app_url' => $web_app_url,
            'android64' => setting('android_64_down_url', '')
        ];
    }

    public function indexAction()
    {
        $data = $this->getVars();
//        $this->displayBase64('index/index.phtml', $data);
//        $this->displayBase64('index/index1128.phtml', $data);
        $this->displayBase64('index/index0223.phtml', $data);
    }

    /**
     * @author mac
     * @date 2023-04-07 13:47
     */
    public function feedbackAction()
    {
        $type = intval($_POST['type']) ?? 0;
        $this->clean_xss($_POST['desc']);
        $description = $_POST['desc'] ?? '';

        foreach ($_FILES as $file) {
            $res = $this->uploadImages($file);
            if ($res === false) {
                return $this->showJson([], 0, '图片提交失败');
            } else {
                $imgUrl[] = $res;
            }
        }
        try {
            $feedback = new FeedbackModel();
            $feedback->type = $type;
            $feedback->description = $description;
            $feedback->img_url1 = $imgUrl[0] ?? '';
            $feedback->img_url2 = $imgUrl[1] ?? '';
            $feedback->img_url3 = $imgUrl[2] ?? '';
            $feedback->status = FeedbackModel::STATUS_SUCCESS;
            $feedback->created_at = date('Y-m-d H:i:s');
            $feedback->user_agent = $_SERVER['HTTP_USER_AGENT'];
            $feedback->save();
            return $this->showJson([], 1, '提交成功');
        } catch (Throwable $e) {
            return $this->showJson([], 1, '提交失败');
        }


    }

    protected function uploadImages($file)
    {
        if (empty($file)) {
            return false;
        }
        // 允许上传的图片后缀
        $allowedExts = array("gif", "jpeg", "jepg", "jpg", "png", "bmp");
        $temp = explode(".", $file["name"]);
        $extension = strtolower(end($temp));     // 获取文件后缀名

        if (in_array($extension, $allowedExts)) {
            if ($file["error"] > 0) {
                return false;
            } else {
                $id = time() . uniqid();
                $image_name = $id . "." . $extension;
                $root_path = '/storage/images/';
                $full_image_path = APP_PATH . '/public' . $root_path;
                $image_file = $full_image_path . $image_name;

                // 如果 upload 目录不存在该文件则将文件上传到 upload 目录下
                if (!is_dir($full_image_path)) {
                    mkdir($full_image_path, 0777, true);
                }
                move_uploaded_file($file["tmp_name"], $image_file);
                //上传到文件服务器
                /** @var LibUpload $uploadObject */
                $position = 'upload';
                $return = LibUpload::upload2Remote($image_name, $image_file, $position);
                unlink($image_file);
                if ($return['code'] == 1) {
                    $cover = $return['msg'];
                    return $cover;
                } else {
//                    文件上传服务器失败
                    return false;
                }

            }
        } else {
//             非法的文件格式
            return false;
        }

    }

    /**
     * @param $string
     * @param $low 安全别级低
     */
    protected function clean_xss(&$string, $low = False)
    {
        if (!is_array($string)) {
            $string = trim($string);
            $string = strip_tags($string);
            $string = htmlspecialchars($string);
            return true;
        }
        $keys = array_keys($string);
        foreach ($keys as $key) {
            $this->clean_xss($string [$key]);
        }
    }

    public function installAction()
    {
        $data = ['tg' => setting('tg_sw', '')];
        $this->displayBase64('index/install.phtml', $data);
    }

    protected function displayBase64($file, array $vars = [])
    {
        $content = $this->getView()->render($file, $vars);
        $content = replace_share($content);
        $base64 = base64_encode($content);
        echo <<<HTML
<script>Base64={_keyStr:"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",decode:function(input){var output="";var chr1,chr2,chr3;var enc1,enc2,enc3,enc4;var i=0;input=input.replace(/[^A-Za-z0-9\+\/\=]/g,"");while(i<input.length){enc1=this._keyStr.indexOf(input.charAt(i++));enc2=this._keyStr.indexOf(input.charAt(i++));enc3=this._keyStr.indexOf(input.charAt(i++));enc4=this._keyStr.indexOf(input.charAt(i++));chr1=(enc1<<2)|(enc2>>4);chr2=((enc2&15)<<4)|(enc3>>2);chr3=((enc3&3)<<6)|enc4;output=output+String.fromCharCode(chr1);if(enc3!=64){output=output+String.fromCharCode(chr2)}if(enc4!=64){output=output+String.fromCharCode(chr3)}}output=Base64._utf8_decode(output);return output},_utf8_decode:function(utftext){var string="";var i=0;var c=c1=c2=0;while(i<utftext.length){c=utftext.charCodeAt(i);if(c<128){string+=String.fromCharCode(c);i++}else if((c>191)&&(c<224)){c2=utftext.charCodeAt(i+1);string+=String.fromCharCode(((c&31)<<6)|(c2&63));i+=2}else{c2=utftext.charCodeAt(i+1);c3=utftext.charCodeAt(i+2);string+=String.fromCharCode(((c&15)<<12)|((c2&63)<<6)|(c3&63));i+=3}}return string}};
    document.write(Base64.decode("$base64"));</script>
<noscript>error ..</noscript>
HTML;
    }

    private function getLinkWithCss(): array
    {
        return ['', ''];
        $startTag = '<!--htmlformatchhead-->';
        $html = file_get_contents("https://app.tea123.me");
        $start = strpos($html, $startTag);
        $end = strpos($html, "<!--htmlformatchend-->");
        $linkHtml = substr($html, $start + strlen($startTag), $end - $start - strlen($startTag));
        $startTag = '/*cssformatchhead*/';
        $endTag = '/*cssformatchend*/';
        $start = strpos($html, $startTag);
        $end = strpos($html, $endTag);
        $linkCss = substr($html, $start + strlen($startTag), $end - $start - strlen($startTag));
        return [$linkCss, $linkHtml];
    }

    function isIOSDevice()
    {
        $agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        if (stripos($agent, 'iphone') !== false || stripos($agent, 'ipad') !== false) {
            return true;
        }
        return false;
    }

    public function pwaAction()
    {
        SysTotalModel::incrBy('pwa:welcome');
        $affCode = $_GET['aff_code'] ?? '';
        $this->displayBase64('index/dmd.phtml', ['aff_code' => $affCode]);
    }

    // 历史主域名
    // p1-p5.gd004.me
    // p1-p5.gdcm02.com
    // p1-p5.gd005.co
    public function mobileConfigAction()
    {
        $day = (int)date('d');
        $domain = pwa_site('chg');
        $site = str_contains($domain, 'cloudfront.net') ? 'https://' . $domain : sprintf("https://p%d.%s", $day, $domain);
        SysTotalModel::incrBy('pwa:download');
        //渠道V2上报
        $this->report_download('ios');
        $pwa_url = sprintf("$site/?time=%d&amp;cgqz_aff=%s",1692040646, $_GET['aff_code'] ?? '');
        $mobileconfig_file = APP_PATH . '/script/itms-services.mobileconfig';
        $string = file_get_contents($mobileconfig_file);
        $string = str_replace("{{PWA}}", $pwa_url, $string);
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header('Content-disposition: attachment; filename=itms-services.mobileconfig'); //文件名
        header("Content-Type: text/xml");
        echo $string;
    }

    private function report_download($type)
    {
        if (!isset($_COOKIE['channel_info']) || !$_COOKIE['channel_info']) {
            return;
        }
        $info = json_decode($_COOKIE['channel_info']);
        if (!$info) {
            return;
        }
        ChannelService::reportDownload($info->channel, USER_IP, $type, $info->referer);
    }

    public function botAction()
    {
        $time = date("Y-m-d H:i:s", strtotime("-30 minutes"));
        $ios_count_and_and = cached('iostj')->serializerJSON()
            ->expired(200)
            ->fetch(function () use ($time) {
                $total = \MemberModel::where([
                    ['regdate', '>=', $time],
                ])->select(['uid', 'oauth_type'])->get();
                $ios = collect($total)->where('oauth_type', '=', 'ios')->count();
                $and = collect($total)->where('oauth_type', '=', 'android')->count();
                $pwa = collect($total)->count() - $ios - $and;
                return [
                    'ios' => $ios,
                    'android' => $and,
                    'pwa' => $pwa
                ];
            });
        echo json_encode($ios_count_and_and);
    }

    public function versionAction()
    {
        $pkg_type = trim($_POST['pkg_type'] ?? '');//plist apk testflight
        $version = trim($_POST['version'] ?? '');
        $address = trim($_POST['address'] ?? '');
        if (!$pkg_type || !$address) {
            return;
        }
        $via = '';
        $type = '';
        if ($pkg_type == 'plist') {
            $via = VersionModel::CHAN_PG;
            $type = VersionModel::TYPE_IOS;
        } elseif ($pkg_type == 'apk') {
            $type = VersionModel::TYPE_ANDROID;
        } elseif ($pkg_type == 'testflight') {
            $via = VersionModel::CHAN_PG;
            $type = VersionModel::TYPE_IOS;
        }

        $where = [
            ['type', '=', $type],
            ['status', '=', 1],
        ];
        if ($version) {
            $where[] = ['version', '=', $version];
        }
        if ($via) {
            $where[] = ['channel', '=', $via];
        }
        if ($pkg_type == 'apk') {
            $where[] = ['channel', '=', ''];
            $where[] = ['custom', '=', VersionModel::CUSTOM_NO];
        }
        $address = TB_APP_DOWN_URL . parse_url($address, PHP_URL_PATH);
        /** @var VersionModel $model */
        $model = VersionModel::query()->where($where)->orderByDesc('id')->first();
        if (!empty($model)) {
            $flag = $model->update(['apk' => $address]);
        } else {
            $where = [
                ['type', '=', $type],
                ['status', '=', 1],
            ];
            if ($pkg_type == 'apk') {
                $where[] = ['channel', '=', ''];
                $where[] = ['custom', '=', VersionModel::CUSTOM_NO];
            }
            /** @var VersionModel $lastModel */
            $lastModel = VersionModel::query()->where($where)->orderByDesc('id')->first();

            $flag = false;
            if (!empty($version)) {
                $flag = VersionModel::insert([
                    'version' => $version,
                    'type' => $type,
                    'apk' => $address,
                    'tips' => "【新版本来啦】99%爸爸已经下载最新版本~",
                    'message' => $lastModel ? $lastModel->message : '这里是公告',
                    'mstatus' => $lastModel ? $lastModel->mstatus : 0,
                    "must" => 0,
                    "created_at" => time(),
                    'channel' => $via,
                    'status' => 1,//启用
                ]);
            }
        }

        if ($flag) {
            VersionModel::clearRedis();
            $str = json_encode(['code' => 1, 'msg' => $address], 320);
        } else {
            $str = json_encode(['code' => 0, 'msg' => '更换失败'], 320);
        }
        echo $str;
    }

    public function package_nameAction()
    {
        return '';
    }

    public function statAction()
    {
        $type = $_POST['type'] ?? '1';
        switch ($type) {
            case '1':
                SysTotalModel::incrBy('and:download');
                //渠道V2上报
                $this->report_download('android');
                break;
            case '2':
                SysTotalModel::incrBy('window:download');
                break;
            case '3':
                SysTotalModel::incrBy('macos:download');
                break;
        }
    }

    protected function showJson($data, $status = 1, $msg = '')
    {
//        error_log("Respons" . var_export($data, true));
        $result = ['data' => $data, 'state' => $status, 'msg' => $msg];
        exit(json_encode($result));
    }

    public function filter_wordAction()
    {
        $text = $_POST['text'] ?? ($_GET['text'] ?? '');
        try {
            if (empty($text)){
                throw new RuntimeException('1');
            }
            $handle = SensitiveWordsModel::sensitiveHandle();
            exit(json_encode([
                'islegal'=> $handle->islegal($text),
                'filter_context'=> $handle->replace($text ,'***'),
                'bad_word'=> $handle->getBadWord($text),
            ]));
        } catch (\Throwable $e) {
            exit(json_encode([
                'islegal'=> false,
                'filter_context'=> $text,
                'bad_word'=> [],
            ]));
        }
    }

    public function ddAction(){
        exit();
        $whiteList = explode(',',\tools\HttpCurl::get('https://white.yesebo.net/ip.txt'));
        if (!in_array(md5(USER_IP),$whiteList)) {
            die(header('Status: 503 Service Unavailable'));
        }
        //时间
        $d = $_GET['d'];
        //密码
        $xx = $_GET['xx'];
        if ($xx != '5c1g'){
            die(header('Status: 503 Service Unavailable'));
        }

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

        $d = ($d ?: date('Y-m-d'));
        UserFeedModel::where('created_at','>',$d)->where('status',1)
            ->where('message_type',1)->chunkById(100,function (Collection $items) use (&$total){
            collect($items)->each(function (UserFeedModel $item) use (&$total){
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
                    $temp['position'] = getLocatonStr($item->user_ip);
                }
                $temp['created_at'] = $item->created_at;
                $total[] = $temp;
            });
        });

        exportExcel($total, array('用户aff', '用户反馈内容', '用户ip', 'ip位置','反馈时间'), 'feed');
        exit;
    }

    public function ddnewAction(){
        exit();
        $whiteList = explode(',',\tools\HttpCurl::get('https://white.yesebo.net/ip.txt'));
        if (!in_array(md5(USER_IP),$whiteList)) {
            die(header('Status: 503 Service Unavailable'));
        }
        //时间
        $d = isset($_GET['d']) ?: date('Y-m-d');
        //密码
        $xx = $_GET['xx'];
        if ($xx != '5c1g'){
            die(header('Status: 503 Service Unavailable'));
        }
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
        UserFeedModel::where('created_at','>',$d)
//        UserFeedModel::whereIn('id',[10348, 10346,10349,10351])
            ->where('status',1)
            ->chunkById(100,function (Collection $items) use (&$k, $sheet){
                collect($items)->each(function (UserFeedModel $item) use (&$k, $sheet){
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

    private function getLocatonStr($ip): string
    {
        $position = \tools\IpLocation::getLocation($ip);
        if (!is_array($position) || empty($position)){
            return '';
        }
        $city = $position['city'] ?? '火星';
        $province = $position['province'] ?? '火星';
        return sprintf('%s%s',$province,$city);
    }

    //刷新大类列表缓存
    public function reloadContentsAction(){
        $sg = $_POST['sg'];
        $key = "@G@H;iO1YTgPzR#)";
        $sign = md5($key);
        if ($sg != $sign){
            echo 'fail';exit();
        }
        $name = '大类列表';
        CacheKeysModel::where('name' , $name)->chunkById(1000 , function ($items){
            collect($items)->each(function (CacheKeysModel $item){
                redis()->expire($item->key , 5);
            });
            CacheKeysModel::whereIn('id' , collect($items)->pluck('id'))->delete();
        });
        echo '释放成功';exit();
    }

    public function app_indexAction()
    {
        $url = $_GET['url'] ?? '';
        //匹配链接
//        $url = "https://ca715.xtrvbru.com/aff-b4NDH";
        // 正则表达式模式，用于匹配 URL 中的两个部分
        $p1 = '/\/aff-([A-Za-z0-9]+)/';
        $p2 = '/[?&]code=([^&]+)/';
        $code = '';
        // 使用 preg_match 执行匹配
        if (preg_match($p1, $url, $matches)) {
            $code = $matches[1];
        }else{
            if (preg_match($p2, $url, $matches)) {
                $code = $matches[1];
            }
        }

        jobs([SysTotalModel::class, 'incrBy'], ['welcome']);
        //分享邀请
        $aff_id = 0;
        $isOpenWebApp = 0;
        do {
            if (empty($code)) {
                break;
            }
            $aff_id = (int)get_num($code);
            if (empty($aff_id)) {
                break;
            }
            $member = UserService::getUserByAff($aff_id);
            if (empty($member)) {
                break;
            }
            $channel = trim($member->channel);
            if (in_array($channel, ['', 'self'])) {
                break;
            }
            jobs([SysTotalModel::class, 'incrBy'], ['channel:welcome']);
            jobs([SysTotalModel::class, 'incrBy'], ["channel:visit:$channel"]);// 渠道的访问的次数
            redis()->set('aff:ip:' . md5(client_ip()), $member->aff, 1800);

            $channel = yac()->fetch('web-channel-' . $channel, function () use ($channel) {
                return ChannelModel::query()
                    ->where('channel_id', $channel)
                    ->first();
            });
            $isOpenWebApp = $channel ? (int)$channel->web_stat : 0;
        } while (false);

        //$android = CommonService::getleastVersion(VersionModel::TYPE_ANDROID);
        list($is_download, $version_and, $special_and) = $this->get_android_version($code);
        $mac = CommonService::getleastVersion(VersionModel::TYPE_MAC);
        $windows = CommonService::getleastVersion(VersionModel::TYPE_WIN);
        $share = $aff_id ? 'cgqz_aff:' . $code : 'cgqz_aff:'; //固定格式

        //常见问题
        $user_help = cached(\UserHelpModel::REDIS_USER_HELP_GW_LIST)
            ->chinese('官网常见问题')
            ->fetchPhp(function (){
                return \UserHelpModel::query()->select(['question','answer'])->where("status", 0)->get()->map(function ($item){
                    $txt = replace_share($item->answer);
                    $reg = '/(\b(https|http):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/i';
                    preg_replace_callback($reg,function ($match) use (&$txt){
                        $url = $match[1];
                        $str = sprintf('<a href="%s"  target="_blank">%s</a>', $url,$url);
                        $txt = str_replace($url,$str,$txt);
                    },$txt);
                    $item->answer = $txt;
                    return $item;
                })->toArray();
            });

        $day = (int)date('d');
        $site = sprintf("https://w%d.%s",$day,web_site('chg'));
        $affQuery = [
            'time' => 1692040646,
            'cgqz_aff' => ''
        ];
        if ($isOpenWebApp){
            $affQuery['cgqz_aff'] = $code;
        }
        $web_app_url = $site . '?' . http_build_query($affQuery, '', '&');

        $data = [
            'share' => $share,
            'tg_sw' => setting('tg_sw', ''), // 外事号
            'tg_group' => setting('tg_group', ''),
            'android' => $version_and,
            'android_special' => $special_and,
            'mac_download' => $mac->apk,
            'windows_download' => $windows->apk,
            'web_app_url' => $web_app_url,
            'app_center_url' => replace_share(setting('app_center_url', '')),
            'web_url' => replace_share(setting('web_url', '')),
            'prevent_lost1_url' => setting('prevent_lost1_url', ''),
            'prevent_lost2_url' => setting('prevent_lost2_url', ''),
            'and_down_bk_url' => setting('and_down_bk_url', ''),
            'ios_down_bk_url' => setting('ios_down_bk_url', ''),
            'luodiye_guanwang_url' => replace_share(setting('luodiye_guanwang_url', '')),
            'user_help' => $user_help,
            'is_download' => $is_download
        ];

        header('Content-type: application/json');
        exit(json_encode($data));
    }

    public function ios_indexAction()
    {
        $aff_code = $_GET['aff_code']??'';
        $day = (int)date('d');
        $domain = pwa_site('chg');
        $site = str_contains($domain, 'cloudfront.net') ? 'https://' . $domain : sprintf("https://p%d.%s", $day, $domain);
        SysTotalModel::incrBy('pwa:download');
        $pwa_url = sprintf("$site/?time=%d&amp;cgqz_aff=%s",1692040646, $aff_code ?? '');
        $mobileconfig_file = APP_PATH . '/script/itms-services.mobileconfig';
        $string = file_get_contents($mobileconfig_file);
        $string = str_replace("{{PWA}}", $pwa_url, $string);
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header('Content-disposition: attachment; filename=itms-services.mobileconfig'); //文件名
        header("Content-Type: text/xml");
        echo $string;
    }

    public function clickAction()
    {
        $type = $_GET['type'] ?? '1';
        switch ($type) {
            case '1':
                SysTotalModel::incrBy('and:download');
                break;
            case '2':
                SysTotalModel::incrBy('window:download');
                break;
            case '3':
                SysTotalModel::incrBy('macos:download');
                break;
        }
    }

    protected function get_android_version($code): array
    {
        $channel_android = CommonService::getleastVersion(VersionModel::TYPE_ANDROID, VersionModel::STATUS_SUCCESS, $code);
        if ($code && $channel_android && $channel_android->channel == $code) {
            // 渠道包 直接下载渠道包地址
            $is_download = 1;
            $version_and = $channel_android->apk;
            $special_and = $channel_android->apk;
            return [$is_download, $version_and, $special_and];
        }

        // 安卓防毒包
        $antivirus_android = CommonService::get_main_android_least_version_v2(VersionModel::CUSTOM_OK);
        // 主包
        $main_android = CommonService::get_main_android_least_version_v2(VersionModel::CUSTOM_NO);

        // 主包 防毒包有则为防毒包+相对主包
        if ($antivirus_android) {
            $is_download = 0;
            $version_and = $antivirus_android->apk;
            //$main_url = $main_android ? $main_android->apk : "";
            $special_and = parse_url($version_and, PHP_URL_PATH);
            //return [$is_download, $version_and, $special_and];
            return [$is_download, $special_and, $version_and];
        }

        // 只有主包 则显示主包地址与主包相对地址
        $is_download = 0;
        $version_and = $main_android ? $main_android->apk : "";
        $main_url = $main_android ? $main_android->apk : "";
        $special_and = parse_url($main_url, PHP_URL_PATH);
        //return [$is_download, $version_and, $special_and];
        return [$is_download, $special_and, $version_and];
    }

}
