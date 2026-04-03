<?php

use service\PayorderService;
use Carbon\Carbon;
use tools\HttpCurl;
use service\CacheKeyService;
use service\ContentsService;

class CronController extends \Yaf\Controller_Abstract
{
    use  \website\HtmlCache;
    static $baseDir = APP_PATH . '/storage/chart/';

    public function init()
    {
        if (PHP_SAPI != 'cli') { die(); }
    }

    public function generateCDKAction()
    {
        global $argv;
        $num = $argv[2] ?? '';
        $day = $argv[3] ?? '';
        if (!$num) {
            echo '请输入数量';
            exit;
        }
        if (!$day) {
            echo '请输入vip天数';
            exit;
        }
        // $date = Carbon::parse($value);
        $file = Carbon::now()->format('Y-m-d') . '-' . $num . '-' . $day . '.txt';
        $redisKey = 'cdk:vip:' . $day;
        for ($i = 0; $i < $num; $i++) {
            $cdk = bin2hex(random_bytes(16));
            if (!redis()->sIsMember($redisKey, $cdk)) {
                redis()->sAdd($redisKey, $cdk);
                file_put_contents($file, $cdk . "\r\n", FILE_APPEND | LOCK_EX);
            }
        }
    }

    public function insertCdkAction()
    {
        $file = fopen("code.txt", "r");
        //检测指正是否到达文件的未端
        redis()->del('ant:cdk');
        while (!feof($file)) {

            $cdk = trim(fgets($file));

            redis()->sAdd('ant:cdk', $cdk);
        }
        //关闭被打开的文件
        fclose($file);

    }

    /**
     * @crontab("0 10 * * *")
     */
    public function getDayDataAction()
    {
        sleep(5);
        $date = date('Y-m-d', strtotime('-1 days'));
        SysTotalModel::crontabToDb($date);
        $service = new \service\StatisticsService();
        $data = $service->getStatisticsInfo($date);
        $model = DayDataModel::where('date', $date)->first();
        if (empty($model)){
            DayDataModel::insert($data);
        }else{
            $model->update($data);
        }

        trigger_log('处理时间：' . $date . PHP_EOL . print_r($data, true));
    }

    // 每日点击数据统计

    /**
     * @crontab("05 0 * * *")
     */
    public function day_statisticsAction()
    {
        trigger_log('加入定时任务触发');
        $date = date('Y-m-d', strtotime('-1 days'));
        //app
        call_user_func_array([DayClickModel::class, '_add_task'], [DayClickModel::TYPE_ADS, $date]);
        call_user_func_array([DayClickModel::class, '_add_task'], [DayClickModel::TYPE_NOTICE, $date]);
        call_user_func_array([DayClickModel::class, '_add_task'], [DayClickModel::TYPE_APP, $date]);
        call_user_func_array([DayClickModel::class, '_add_task'], [DayClickModel::TYPE_NOTICE_APP, $date]);
        //pc
        call_user_func_array([PcDayClickModel::class, '_add_task'], [PcDayClickModel::TYPE_ADS, $date]);
        call_user_func_array([PcDayClickModel::class, '_add_task'], [PcDayClickModel::TYPE_NOTICE, $date]);
        call_user_func_array([PcDayClickModel::class, '_add_task'], [PcDayClickModel::TYPE_APP, $date]);
        call_user_func_array([DayInviteModel::class, 'import2db'], [$date]);
    }


    public function reportUserAction()
    {
        echo __FUNCTION__ . 'start' . PHP_EOL;
        $uid = MemberModel::max('uid');
        for ($i = 1; $i <= $uid; $i++) {
            /** @var MemberModel $member */
            $member = MemberModel::where('uid', $i)->first();
            if (is_null($member)) {
                continue;
            }
            if ($member->channel && $member->channel != 'self') {
                \tools\Channel::addUserQueue($member->toArray());
                echo "uid :$i added" . PHP_EOL;
            }
            usleep(500);
        }
    }

    /**
     * 每日凌晨2:00 - 生成文章sitemap拆分文件
     * @crontab("0 2 * * *")
     */
    public function generateArchivesSitemapAction()
    {
        trigger_log('开始生成文章sitemap');
        
        $service = new \service\SitemapService();
        $totalPages = $service->getArchivesSitemapCount();
        
        for ($page = 1; $page <= $totalPages; $page++) {
            $filepath = $service->generateAndSaveSitemap('archives', $page);
            trigger_log($filepath ? "生成第{$page}页: {$filepath}" : "第{$page}页失败");
        }
        
        trigger_log("文章sitemap生成完成，共{$totalPages}页");
    }

    /**
     * 每周一3:00 - 生成分类页sitemap
     * @crontab("0 3 * * 1")
     */
    public function generateCategorySitemapAction()
    {
        trigger_log('开始生成分类页sitemap');
        
        $service = new \service\SitemapService();
        $filepath = $service->generateAndSaveSitemap('category');
        trigger_log($filepath ? "分类页sitemap完成: {$filepath}" : "分类页sitemap失败");
    }

    /**
     * 每日凌晨4:00 - 重建主sitemap索引文件
     * @crontab("0 4 * * *")
     */
    public function rebuildMainSitemapAction()
    {
        trigger_log('开始重建主sitemap索引');
        
        $service = new \service\SitemapService();
        
        $homeFilepath = $service->generateAndSaveSitemap('home');
        trigger_log($homeFilepath ? "首页sitemap: {$homeFilepath}" : "首页sitemap失败");
        
        $mainFilepath = $service->generateAndSaveSitemap('main');
        trigger_log($mainFilepath ? "主索引: {$mainFilepath}" : "主索引失败");
    }

    /**
     * 每小时执行 - 优先提交当天新链接，然后提交1000条现有文章
     * @crontab("0 * * * *")
     */
    public function indexnowHourlySubmitAction()
    {
        trigger_log('开始每小时IndexNow提交任务');
        
        $service = new \service\IndexNowService();
        $results = $service->hourlySubmit(5000, 1000);
        
        trigger_log("新链接提交结果: " . json_encode($results['new_links'], JSON_UNESCAPED_UNICODE));
        trigger_log("现有文章提交结果: " . json_encode($results['articles'], JSON_UNESCAPED_UNICODE));
    }

    public function ttAction()
    {
        $msg = '当前时区:' . date_default_timezone_get() . PHP_EOL;
        $msg .= '当前执行时间:' . date('Y-m-d H:i:s');
        echo $msg . PHP_EOL;
        trigger_log($msg);
    }

    /**
     * 定时更新支付渠道
     * 停用 20251021
     * ("*\/15 * * * *")
     */
//    public function updatePayChannelAction()
//    {
//        $products = ProductModel::where('status', 1)
//            // ->groupBy('promo_price')
//            ->get();
//        $amount = $products->pluck('promo_price')->unique()->all();
//        foreach ($amount as &$v) {
//            $v = $v / 100;
//        }
//        // dd($amount);
//        // foreach($products as ){
//
//        // }
//
//        $data['app_name'] = config('pay.app_name');
//        $data['timestamps'] = time();
//        $sign = PayorderService::makePaySign($data, config('pay.pay_signkey'));
//        $data['sign'] = $sign;
//        $data['amounts'] = $amount;
//        $curl = new HttpCurl();
//        $result = $curl->post(config('pay.pay_channel'),  $data);
//        $result = json_decode($result);
//        // $result = my_addslashes($result);
//        // dd($result);
//        if ($result->success === true) {
//            foreach ($products as $product) {
//                PayMapModel::where('product_id', $product->id)
//                    ->whereIn('way_id', [1, 2, 3])
//                    ->delete();
//                $payWay = [1 => 'wechat', 2 => 'alipay', 3 => 'bankcard'];
//                foreach ($payWay as $k => $v) {
//                    if ($result->data->$v) {
//                        foreach ($result->data->$v as $amount) {
//                            if ($product->promo_price / 100 == $amount) {
//                                PayMapModel::create([
//                                    'product_id' => $product->id,
//                                    'type_id'    => 1,
//                                    'way_id'     => $k
//                                ]);
//                            }
//                        }
//                    }
//                }
//            }
//        }
//        dd($result);
//    }

    /**
     * @description 定时发起同步线路成功率的请求
     * @crontab("0 *\/2 * * *")
     */
    public function sysLineRateAction(){
        $date = date('Y-m-d H:i:s');
        if (date('H') == 0){
            trigger_log('定时发起同步线路成功率的请求,12点不请求' . $date . PHP_EOL);
            exit();
        }
        $url = rtrim(register('site.site_url'),'/')."/ping.php?_yaf=_sys_total";

        $rs = HttpCurl::get($url);
        //设置过期时间2分钟
        $key = 'line:success:rate';
        $key = cached($key)->generateKeyname();
        redis()->expire($key,180);
        echo date('Y-m-d H:i:s');
        trigger_log('定时发起同步线路成功率的请求,当前时间:' . $date . ',结果:' . $rs .PHP_EOL);
    }

    /**
     * @return void
     * @throws Throwable
     * 停用 20251021
     * ("*\/15 * * * *")
     */
//    public function sessionAction(){
//        try {
//            LibMember::crontabUpdateSession();
//        }catch (Exception $exception){
//            trigger_log("更新日活错误:" . $exception->getMessage());
//        }
//    }

//    public function sysContentSearchAction(){
//        //6点走全量更新
//        if ((int)date('H') == 6 && (int)date('i') == 10){
//            //清理表
//            ContentsSearchModel::query()->truncate();
//            //数据导入
//            ContentsSearchModel::sysData(0);
//        }else{
//            //增量更新
//            $contentMaxId = ContentsModel::max('cid');
//            $contentSearchMaxId = ContentsSearchModel::max('cid');
//            if ($contentSearchMaxId >= $contentMaxId){
//                exit();
//            }
//            ContentsSearchModel::sysData($contentSearchMaxId);
//        }
//    }

    /**
     * @description manticore 每小时全量同步一次
     * 停用 20251021
     * ("0 *\/1 * * *")
     */
//    public function sysContentSearchAction(){
//        //清理表
//        ContentsSearchModel::query()->truncate();
//        //数据导入
//        ContentsSearchModel::sysData(0);
//    }

    //手动设置在官网跑
    public function emailTaskAction(){
        TaskEmailModel::where('status', TaskEmailModel::STATUS_WAIT)->get()->map(function (TaskEmailModel $task){
            //只执行订阅用户
            if ($task->user_type != TaskEmailModel::USER_TYPE_SUBSCRIBE){
                return null;
            }
            if (Carbon::now()->lt($task->send_time)){
                return null;
            }
            $task->status = TaskEmailModel::STATUS_PROGRESS;
            $task->save();
            $subject = $task->send_title;
            $body = $task->send_content;
            if ($task->img_url){
                $img_url = parse_url($task->img_url, PHP_URL_PATH);
                $base_url = 'https://imgpublic.ycomesc.live';
                $img_url = $base_url . $img_url;
                $info = getimagesize($img_url);
                $suffix = explode('/', $info['mime'])[1];
                $image = file_get_contents($img_url);
                $md5 = substr(md5($img_url), 0, 16);
                $from = APP_PATH . '/public/data/images/' . $md5 . '.' . $suffix;
                $filename = $md5 . '.' . $suffix;
                $dirname = dirname($from);
                if (!is_dir($dirname) || !file_exists($dirname)) {
                    mkdir($dirname, 0755, true);
                }
                file_put_contents($from, $image);
                $img_url = replace_share('{share.chg}') . '/data/images/' . $filename;
                $img = '<img src="%s" alt="'.register('site.app_name').'">';
                $body = $body . "<br/>" . sprintf($img, $img_url);
            }
            echo "开始执行邮件发送脚本~~~~~";
            //开始发送邮件
            EmailSubscribeModel::chunkById(1000,function ($items) use ($task, $subject, $body){
                collect($items)->each(function (EmailSubscribeModel $subscribeModel) use ($task, $subject, $body){
                    $res = EmailLogModel::sendContent($subscribeModel->email, $subject, $body);
                    $subscribeModel->increment('send_ct');
                    if ($res){
                        $task->increment('suc_ct');
                    }else{
                        $task->increment('fail_ct');
                    }
                });
            });
            echo "邮件发送脚本完成~~~~~";
            $task->status = TaskEmailModel::STATUS_FINISH;
            $task->save();
        });
    }

    //不要了
//    public function contentsStatusUpdateAction(){
//        ContentsModel::where('status', ContentsModel::STATUS_WAITING)
//            ->chunkById(100,function (\Illuminate\Support\Collection $items){
//            collect($items)->each(function (ContentsModel $item){
//                //获取评论数
//                $ct = CommentsModel::where('cid', $item->cid)->where('status', CommentsModel::STATUS_APPROVED)->count();
//                if ($ct > 1){
//                    $item->status = ContentsModel::STATUS_SECRET;
//                    $item->save();
//                    trigger_log('秘闻,文章ID:' . $item->cid . PHP_EOL);
//                }
//            });
//        });
//    }

    /**
     * @description 每10分钟更新文章评论的回复数
     * @crontab("17 *\/1 * * *")
     */
    public function updateContentCommentReplyCtAction(){
        //文章评论
        CommentsModel::where('status', CommentsModel::STATUS_APPROVED)
            ->where('created', '>', TIMESTAMP - 86400)
            ->where('parent', '>', 0)
            ->where('fix_reply', 0)
            ->chunkById(100, function ($items) {
                collect($items)->each(function (CommentsModel $item){
                    //二级评论数加1
                    $c1 = CommentsModel::find($item->parent);
                    if (!empty($c1)){
                        $c1->increment('reply_ct');
                    }
                    if ($item->sec_parent){
                        //二级评论数加1
                        $c2 = CommentsModel::find($item->sec_parent);
                        if (!empty($c2)){
                            $c2->increment('reply_ct');
                        }
                    }
                    $item->fix_reply = 1;
                    $item->save();
                });
            });
        //社区评论
        PostCommentModel::where('status', PostCommentModel::STATUS_PASS)
            ->where('created_at', '>', date('Y-m-d H:i:s', strtotime('-1 day')))
            ->where('pid', '>', 0)
            ->where('fix_reply', 0)
            ->chunkById(100, function ($items) {
                collect($items)->each(function (PostCommentModel $item){
                    //二级评论数加1
                    $c1 = PostCommentModel::find($item->pid);
                    if (!empty($c1)){
                        $c1->increment('reply_ct');
                    }
                    if ($item->sec_parent){
                        //二级评论数加1
                        $c2 = PostCommentModel::find($item->sec_parent);
                        if (!empty($c2)){
                            $c2->increment('reply_ct');
                        }
                    }
                    $item->fix_reply = 1;
                    $item->save();
                });
            });
    }

    /**
     * @description 剧集予发布转到发布
     * @crontab("01 *\/1 * * *")
     */
    public function episodeUpdateAction(){
        EpisodeModel::where('is_pre', EpisodeModel::PRE_YES)
            ->where('release_time', '<', date('Y-m-d H:i:s'))
            ->chunkById(10, function ($items) {
                collect($items)->each(function (EpisodeModel $item){
                    //播放地址、和发布时间存在
                    if ($item->play_url && $item->release_time){
                        $item->is_pre = EpisodeModel::PRE_NO;
                        $item->save();
                        cached('')->clearGroup(EpisodeModel::GP_EPISODE_LIST);
                        error_log("剧集ID:" . $item->id . date('Y-m-d H:i:s') . PHP_EOL, 3, APP_PATH . '/storage/logs/updateContentCommentReplyCt.log');
                    }
                });
            });
    }

    /**
     * @description 刷评论
     * @crontab("*\/12 * * * *")
     */
    public function addCommentsAction(){
        CommentsTaskModel::where('is_run', CommentsTaskModel::RUN_WAIT)
            ->chunkById(15, function ($items) {
                collect($items)->map(function (CommentsTaskModel $item){
                    //30天还没有刷评论，直接放弃
                    if (TIMESTAMP - 30 * 86400 > strtotime($item->created_at)){
                        $item->is_run = CommentsTaskModel::RUN_GIVE_UP;
                        $item->save();
                        return;
                    }
                    //草稿删除，直接放弃
                    $userContent = UserContentsModel::find($item->cid);
                    if (empty($userContent)){
                        $item->is_run = CommentsTaskModel::RUN_GIVE_UP;
                        $item->save();
                        return;
                    }
                    if ($userContent->cid){
                        $content = ContentsModel::find($userContent->cid);
                        //文章审核通过,才添加评论
                        if ($content && $content->status == ContentsModel::STATUS_PUBLISH){
                            $content_created = $content->getRawOriginal('created');
                            //刷评论的时间来了 刷评论开始时间
                            $text = $item->content;
                            $arr = explode(PHP_EOL, $text);
                            $arr = array_filter($arr);
                            foreach ($arr as $v){
                                $nickname = \tools\MemberRand::randNickname();
                                $thumb = \tools\MemberRand::randAvatar();
                                $created = rand($content_created + $item->begin * 3600, $content_created + $item->end * 3600);
                                $data = [
                                    'cid'          => $content->cid,
                                    'created'      => $created,
                                    'author'       => $nickname,
                                    'reply_author' => '',
                                    'reply_aff'    => 0,
                                    'thumb'        => $thumb,
                                    'app_aff'      => 0,
                                    'authorId'     => 0,
                                    'ownerId'      => $content->authorId,
                                    'mail'         => '',
                                    'url'          => '',
                                    'ip'           => '0.0.0.0',
                                    'agent'        => 'web',
                                    'text'         => $v,
                                    'type'         => CommentsModel::TYPE_COMMENT,
                                    'status'       => CommentsModel::STATUS_APPROVED,
                                    'parent'       => 0,
                                    'sec_parent'   => 0 //二级评论ID
                                ];
                                CommentsModel::create($data);
                            }
                            $content->increment('commentsNum', count($arr));
                            $item->is_run = CommentsTaskModel::RUN_SUCCESS;
                            $item->save();
                        }
                    }
                });
            });
    }

    /**
     * @description 远程防毒包下载
     * 停用 20251021
     * ("*\/20 * * * *")
     */
//    public function downloadCustomApkAction(){
//        // 安卓防毒包
//        $antivirus_android = \service\CommonService::get_main_android_least_version_v2(VersionModel::CUSTOM_OK);
//        //防毒包下载
//        if ($antivirus_android) {
//            $version_and = $antivirus_android->apk;
//            VersionModel::defend_apk($version_and, 1);
//            //发布订阅
//            $data = json_encode([$version_and, 1]);
//            redis()->publish(VersionModel::PUBLISH_APK_DOWN_CHANNEL, $data);
//        }
//    }

//    public function downloadApk1Action(){
//        // 安卓主包
//        $android = \service\CommonService::get_main_android_least_version_v2(VersionModel::CUSTOM_NO);
//        //主包下载
//        if ($android) {
//            $version_and = $android->apk;
//            //发布订阅
//            redis()->publish(VersionModel::PUBLISH_APK_DOWN_CHANNEL, $version_and);
//        }
//    }

    //通过订阅 包下载
    // 停用 20251021
//    public function downloadApkAction(){
//        $fn = function ($redis, $channel, $msg) {
//            list($apk, $is_update) = json_decode($msg, true);
//            VersionModel::defend_apk($apk, $is_update);
//        };
//        //订阅包下载频道
//        redis()->subscribe([VersionModel::PUBLISH_APK_DOWN_CHANNEL], $fn);
//    }

    /**
     * 获取google在线人数
     * @crontab("0 * * * *")
     */
    public function getGoogleDataAction()
    {
        try {
            date_default_timezone_set("ETC/GMT-7");

            $hour = date('H');
            $minute = date('i');
            $date = date('Y-m-d');
            if (substr($hour, 0, 1) === '0') {
                $hour = substr($hour, 1);
            }

            echo date('Y-m-d H:i:s')." 开始执行";
            // 获取半小时数据
            if($minute == '00') {
                $maxAttempts = 3;
                for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
                    $res = \service\StatisticsService::getOnlineData('online');
                    wf("请求在线人数结果",$res,true);
                    if (is_array($res) && $res['count'] > 0) {
                        $num = $res['count'];

                        $hasDate = UserOnlineModel::query()->where('date', $date)->first();
                        if (!$hasDate) {
                            UserOnlineModel::insert(['date' => $date]);
                        }

                        $colum = "t" . $hour;
                        UserOnlineModel::query()->where('date', $date)->update([
                            $colum => $num
                        ]);
                        break;
                    }
                }
            }
        }catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * 清理空标签
     * 
     * @crontab("0 0 * * *")
     */
    public function cleanEmptyTags()
    {
        echo "开始检查空标签...\n";

        try {
            
            // 获取所有空标签
            $emptyTags = $this->findEmptyTags();

            if (empty($emptyTags)) {
                echo "没有发现空标签。\n";
                return;
            }

            echo "发现 " . count($emptyTags) . " 个空标签：\n";

            // 执行删除
            $deletedCount = $this->deleteEmptyTags($emptyTags);
            
            echo "成功删除了 {$deletedCount} 个空标签。\n";
            echo "清理完成！\n";

        } catch (\Exception $e) {
            echo "错误: " . $e->getMessage() . "\n";
        }
    }

    /**
     * 查找所有空标签
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function findEmptyTags()
    {
        // 查找没有关联任何已发布文章的标签
        return TagsModel::whereDoesntHave('relationships', function ($query) {
            $query->whereHas('content');
        })->get();
    }

    /**
     * 删除空标签
     * 
     * @param \Illuminate\Database\Eloquent\Collection $emptyTags
     * @return int 删除的标签数量
     */
    private function deleteEmptyTags($emptyTags)
    {
        $deletedCount = 0;
        
        foreach ($emptyTags as $tag) {
            try {
                // 删除标签的所有关联关系（如果有的话）
                TagRelationshipsModel::where('tag_id', $tag->id)->delete();
                
                // 删除标签本身
                $tag->delete();
                
                // 清除相关缓存
                $this->clearTagCache($tag->id);
                
                $deletedCount++;
                echo "已删除标签: ID {$tag->id}, 名称 '{$tag->name}'\n";
                
            } catch (\Throwable $e) {
                echo "删除标签 ID {$tag->id} 时出错: " . $e->getMessage() . "\n";
            }
        }
        
        // 清除全局标签缓存
        $this->clearGlobalTagCache();
        
        return $deletedCount;
    }

    /**
     * 清除单个标签的缓存
     * 
     * @param int $tagId
     */
    private function clearTagCache($tagId)
    {
        try {
            cached(sprintf(TagsModel::CK_TAG_ID, $tagId))->clearCached();
        } catch (\Exception $e) {
            // 忽略缓存清除错误
        }
    }

    /**
     * 清除全局标签相关缓存
     */
    private function clearGlobalTagCache()
    {
        try {
            cached('tags-list-new')->clearCached();
            cached('gp:tag-detail')->clearCached();
            cached('gp:tags-list-new')->clearCached();
            cached(TagsModel::GP_TAG)->clearCached();
        } catch (\Exception $e) {
            // 忽略缓存清除错误
        }
    }

    private function _initNewHtmlCache()
    {
        static $NEWHTMLCACHE = null;

        if( is_null($NEWHTMLCACHE) ){
            $options = require APP_PATH.'/application/html.php';
            $this->NewHtmlCache(is_array($options) ? $options : []);
            $NEWHTMLCACHE = &$this;
        }
        return $NEWHTMLCACHE;
    }

    /**
     * 清理缓存减缓列表图片不显示
     *
     * @crontab("40 4 * * *")
     */
    public function clean_all_cacheAction()
    {
        try {
            // 1.
            yacsys()->expire('category:categories', 1);
            yacsys()->expire('category:pages', 1);
            //数据缓存
            CacheKeyService::clear_group('gp:content:home-list');
            CacheKeyService::clear_group('gp:content:home-count');

            //2.
            yacsys()->expire('category:categories', 1);
            yacsys()->expire('category:pages', 1);
            AppCategoryModel::clearCache();
            PcAppCategoryModel::clearCache();
            //数据缓存
            CacheKeyService::clear_group('gp:content:category-list');
            CacheKeyService::clear_group('gp:content:category-list-count');
            YacCacheManager::clearCache('metas');

            //3 数据缓存
            CacheKeyService::clear_group('list-comment-list');

            //4 数据缓存
            CacheKeyService::clear_group('gp:advert-list');

            // 5
            yacsys()->delete('options:all');
            YacCacheManager::clearCache('all');

            // yac缓存
            $this->_initNewHtmlCache()->del("/");

            $serviceContent = new ContentsService();
            $cates = $serviceContent->categoryMeats();
            foreach ($cates as $c_row){
                $this->_initNewHtmlCache()->del("/category/".$c_row['slug']."/");
            }

            echo date('Y-m-d H:i:s')." ".__FUNCTION__." 清理完成！\n";
        } catch (\Exception $e) {
            echo "错误: " . $e->getMessage() . "\n";
        }
    }
    /**
     * @description 每10分钟上报在线人数
     * @crontab("01 *\/10 * * *")
     */
    public function reportOnlineUserAction()
    {
        $date = date('Y-m-d H:i:s');
        trigger_log("开始上报在线人数: {$date}");
        
        try {
            // 1. 获取在线人数
            $onlineData = \service\StatisticsService::getOnlineData('online');
            $count = is_array($onlineData) ? ($onlineData['count'] ?? 0) : 0;
            
            // 2. 构造数据
            $payload = [
                'app_id'     => config('adscenter.app_code'),
                'channel'    => 'server', 
                'event_id'   => \Tracking\Helper::generateTraceId(),
                'client_ts'  => time(),
                'event'      => 'realtime_online',
                'payload'    => [
                    'platform_quantity' => (int)$count
                ]
            ];
            
            // 3. 上报 (批量接口格式: JSON Array)
            $url = 'https://api.shuifeng.cc/api/eventTracking/batchReport.json'; // 测试环境
            $body = json_encode([$payload]);
            
            // 使用 HttpCurl 发送
            $res = \tools\HttpCurl::post($url, $body, ['Content-Type: application/json']);
            
            trigger_log("在线人数上报结果 (Count: {$count}): {$res}");
            echo "Online Users Report ({$count}): {$res}\n";
            
        } catch (\Exception $e) {
            trigger_log("在线人数上报失败: " . $e->getMessage());
            echo "Error: " . $e->getMessage() . "\n";
        }
    }

    public function updateConfigAction()
    {
        $logFile = APP_PATH . '/storage/logs/config_update.log';
        try {
            $url = 'https://config.microservices.vip/2020090623125271421.txxxx';
            $local = APP_PATH . '/storage/local.json';

            // 使用较长的超时时间
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_TIMEOUT, 15);  // 15 秒超时
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            $json = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $error = curl_error($curl);
            curl_close($curl);

            if (!empty($json) && $httpCode == 200) {
                // 验证 JSON 格式
                $data = json_decode($json, true);
                if (is_array($data) && !empty($data)) {
                    file_put_contents($local, $json, LOCK_EX);

                    // 清除 filecached 缓存，强制下次请求重新加载
                    filecached()->del('config_center');

                    error_log(date('Y-m-d H:i:s') . " - 配置更新成功，大小: " . strlen($json) . " bytes" . PHP_EOL, 3, $logFile);
                } else {
                    error_log(date('Y-m-d H:i:s') . " - 配置更新失败: JSON 格式无效" . PHP_EOL, 3, $logFile);
                }
            } else {
                error_log(date('Y-m-d H:i:s') . " - 配置更新失败: HTTP {$httpCode}, Error: {$error}" . PHP_EOL, 3, $logFile);
            }

        } catch (\Exception $e) {
            error_log(date('Y-m-d H:i:s') . " - 配置更新异常: " . $e->getMessage() . PHP_EOL, 3, $logFile);
        }
    }
}
