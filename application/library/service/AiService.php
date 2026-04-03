<?php

namespace service;

use AiTaskModel;
use Illuminate\Support\Collection;
use MemberFaceModel;
use CURLFile;
use LibUpload;
use Throwable;
use FaceMaterialModel;

class AiService
{
//    const  SORT_NAV = [
//        ['title' => '推荐', 'value' => 'rec', 'type' => 1],
//        ['title' => '上架时间', 'value' => 'up', 'type' => 2],
//        ['title' => '使用次数', 'value' => 'use', 'type' => 2],
//    ];
//
//    public static function list_nav()
//    {
//        return FaceCateModel::list_cate()->prepend([
//            'id'   => 0,
//            'name' => '全部'
//        ]);
//    }

    const PERMANENT_DOMAIN = 'http://chg.we-cname.com';
    const AI_API = 'http://13.213.13.9/img/getImg';
    const AI2_API = 'http://ai.ycomesc.live/api/img/getImg'; //新AI
    const CALLBACK_API = self::PERMANENT_DOMAIN . '/index.php/notify/call_ai';

    const LOG_FILE = '/storage/logs/ai.log';

    const IMAGE_FACE_LOG_FILE = '/storage/logs/img_face.log';

    const IMAGE_FACE_BACK_API = self::PERMANENT_DOMAIN . '/index.php/notify/sync_img_face';

    const IMAGE_FACE_API = 'https://ai-1.yesebo.net/head/getImg';

    const STRIP_LOG_FILE = '/storage/logs/strip.log';

    public static function addTask($img,\MemberModel $member)
    {
        $member = $member->refresh();
        if ($member->money < setting('ai_need_coins',20)) {
            test_assert(false, '金币余额不足');
        }

        $model = transaction(function () use ($member, $img) {
            $data = [
                'media_url'    => parse_url($img['media_url'], PHP_URL_PATH),
                'media_width'  => $img['thumb_width'],
                'media_height' => $img['thumb_height'],
                'status'       => AiTaskModel::STATUS_WAIT,
                'created_at'   => \Carbon\Carbon::now(),
                'updated_at'   => \Carbon\Carbon::now(),
                'aff'=>$member->aff
            ];
            $model  = AiTaskModel::create($data);
            $total = setting('ai_need_coins', 20);
            $isOk = $member->subMoney($total, \MoneyLogModel::SOURCE_AI_TY, '脱衣扣费', $model);
            if ($isOk) {
                $model->status = AiTaskModel::STATUS_PROCESSING;
                $model->pay_type = AiTaskModel::PAY_COINS;
                $model->save();
            } else {
                $model->status = AiTaskModel::STATUS_FAILD;
                $model->save();
            }
            return $model;
        });

        jobs([self::class, '_callAi'], [$model->id, TB_IMG_ADM_US .$img['media_url'], $model->aff, 1]);
    }


    public static function retry($id)
    {
        $model = AiTaskModel::query()->where('id',$id)->first();
        test_assert($model, '记录不存在');
        if($model->times == 0 && in_array($model->status,[2,3])){
            $model->status =  AiTaskModel::STATUS_PROCESSING;
            $model->times = $model->times + 1;
            $model->save();
            $media_url = parse_url($model->media_url, PHP_URL_PATH);
            jobs([self::class, '_callAi'], [$model->id, TB_IMG_ADM_US .$media_url,$model->aff,2]);
        }else{
            test_assert(false, '只能重试一次');
        }
    }

    public static function  _callAi($id,$img,$aff,$level=1){
        self::callAi2Api($id,$img,$aff,$level);
//        self::callAiApi($id,$img);
    }

    /**
     * @throws \Exception
     */
    protected static function callAiApi($id, $fr)
    {
        trigger_json('开始处理:' . $fr, self::LOG_FILE);
        $image = file_get_contents($fr);
        test_assert($image, '请求远程异常:' . $fr);
        $md5 = substr(md5($fr), 0, 16);
        $from = APP_PATH . '/storage/data/images/' . $md5 . '_fr';
        $dirname = dirname($from);
        if (!is_dir($dirname) || !file_exists($dirname)) {
            mkdir($dirname, 0755, true);
        }
        trigger_json('开始写入文件:' . $from, self::LOG_FILE);
        $rs = file_put_contents($from, $image);
        test_assert($rs, '无法写入文件:' . $from);
        $cover = new CURLFile(realpath($from), mime_content_type($from));
        $data = [
            'image'    => $cover,
            'id'       => $id,
            'callback' => replace_share(self::CALLBACK_API),
        ];
        trigger_json('开始请求参数:', self::LOG_FILE);
        trigger_json($data, self::LOG_FILE);
        $rs = LibUpload::execCurl(self::AI_API, $data);

        $img = $rs['imageUrl'] ?? '';
        test_assert($img, '请求远程AI绘图异常');
        unlink($from);
        return !empty($img) ? true : false;
    }

    /**
     * @throws \Exception
     */
    protected static function callAi2Api($id, $fr,$aff,$level=1)
    {
        trigger_json('开始处理:' . $fr, self::LOG_FILE);
        $image = file_get_contents($fr);
        test_assert($image, '请求远程异常:' . $fr);
        $md5 = substr(md5($fr), 0, 16);
        $from = APP_PATH . '/storage/data/images/' . $md5 . '_fr';
        $dirname = dirname($from);
        if (!is_dir($dirname) || !file_exists($dirname)) {
            mkdir($dirname, 0755, true);
        }
        trigger_json('开始写入文件:' . $from, self::LOG_FILE);
        $rs = file_put_contents($from, $image);
        test_assert($rs, '无法写入文件:' . $from);
        $cover = new CURLFile(realpath($from), mime_content_type($from));
        $data = [
            'image'    => $cover,
            'project' => config('system.name', '51cg'),//新AI
            'id'       => $id,
            'callback' => replace_share(self::CALLBACK_API),
            'userId' => $aff,//新AI
            'isSync' => 2,//1-同步，2-异步//新AI
            'isForceQueue' => $level,//是否插队，1-普通队列，2-普通插队（队尾添加）3-高级插队（队头添加）//新AI
        ];
        trigger_json('开始请求参数:', self::LOG_FILE);
        trigger_json($data, self::LOG_FILE);
        try {
            $rs = LibUpload::execCurl(config('ai1.url'), $data);
            if(isset($rs['code']) && $rs['code'] != 200){
                //AI绘图失败直接更新失败
                $aiModel =  AiTaskModel::query()->where('id',$id)->first();
                //退款维护失败
                self::retund($aiModel);

                trigger_json('AI绘图失败错误信息:'.$rs['msg'], self::LOG_FILE);
            }
        }catch (\Exception $e) {
            trigger_json('AI绘图错误:'.$e->getMessage(), self::LOG_FILE);
        }
        unlink($from);
        return $rs['code'] == 200 ? true : false;
    }
    public static function retund($model){
        if(!$model){
            return false;
        }
        $model->status = AiTaskModel::STATUS_FAILD;
//        if($model->refunded != 1){
//            $member = \MemberModel::firstAff($model->aff);
//            //失败返回 次数或者金币
//            if($model->pay_type == AiTaskModel::PAY_TIMES){
//                $member->increment('ty_times');
//                $model->refunded = 1;
//            }
//            if($model->pay_type == AiTaskModel::PAY_COINS){
//                $total = setting('ai.coins',20);
//                $member->addMoney($total,MoneyLog::SOURCE_AI,'失败返回',$model);
//                $model->refunded = 1;
//            }
//        }
        $model->save();
    }
    /**
     * @throws \Exception
     */
    protected static function uploadImg($fr)
    {
        trigger_json('开始处理:' . $fr, self::LOG_FILE);
        $image = file_get_contents($fr);
        test_assert($image, '请求远程异常:' . $fr);
        $md5 = substr(md5($fr), 0, 16);
        $to = APP_PATH . '/storage/data/images/' . $md5 . '_to';
        $dirname = dirname($to);
        if (!is_dir($dirname) || !file_exists($dirname)) {
            mkdir($dirname, 0755, true);
        }
        $rs = file_put_contents($to, $image);
        test_assert($rs, '无法写入文件:' . $to);
        $return = LibUpload::upload2Remote(uniqid(), $to, 'upload');
        test_assert($return, '上传文件异常');
        test_assert($return['code'] == 1, '上传文件异常');
        unlink($to);
        trigger_json('处理完成:' . $return['msg'], self::LOG_FILE);
        return $return['msg'];
    }

    /**
     * @throws \Exception
     */
    public static function callback(): bool
    {
        try {
            $id = (int)($_POST['id'] ?? 0);
            $img = trim($_POST['image'] ?? '');
            $code = (int)($_POST['code'] ?? 0);

            trigger_json('收到回调', self::LOG_FILE);
            trigger_json($_POST, self::LOG_FILE);
            test_assert($id, '回调异常');

            /** @var AiTaskModel $rs */
            $rs = AiTaskModel::where('id', $id)
                ->where('status', AiTaskModel::STATUS_PROCESSING)
                ->first();
            if (!$rs) {
                exit('success');
            }

            // 如果失败则删除任务 并标记
            if ($code == 0) {
                //退款
                self::retund($rs);
                exit('success');
            }

            // 上传远程图片
            test_assert($img, '回调成功,图片地址异常');
            $url = self::uploadImg($img);
            $rs->status = AiTaskModel::STATUS_FINISHED;
            $rs->media_1 = $img;
            $rs->media_2 = $url;
            $rs->updated_at = \Carbon\Carbon::now();
            $isOk = $rs->save();
            test_assert($isOk, '系统异常');
            exit('success');
        } catch (Throwable $e) {
            trigger_json($e->getMessage(), self::LOG_FILE);
            exit('fail');
        }

    }

    /**
     * 获取我的脱衣记录
     * @param $aff
     * @param $page
     * @param $limit
     */
    public function getlist($aff,$page, $limit){
       $list =  AiTaskModel::query()
               ->where('aff',$aff)
                ->orderByDesc('id')
                ->forPage($page, $limit)
                ->get();
       $data = [];
       if($list){
           foreach ($list as $val){
               $item = [];
               $item['id'] = $val['id'];
               $item['img_url'] = $val['media_url'];
               $item['created_at'] = $val['created_at'];
               $item['times'] = $val['times'];
               $item['status'] = $val['status'];
               $item['media_2'] = $val['media_2'];
               $data[] = $item;
           }
       }
       return $data;
    }
    public function myStrip($aff,$page, $limit,$status='all'){
        $list =  AiTaskModel::query()
            ->when(in_array($status,[0,1,2,3]),function ($query)use($status){
                if(in_array($status,[0,1])){
                    return $query->whereIn('status',[0,1]);
                }else{
                    return  $query->where('status', $status);
                }
            })
            ->where('aff',$aff)
            ->where('is_delete', AiTaskModel::DELETE_NO)
            ->orderByDesc('id')
            ->forPage($page, $limit)
            ->get();
        $data = [];
        if($list){
            foreach ($list as $val){
                $item = [];
                $item['id'] = $val['id'];
                $item['img_url'] = $val['media_url'];
                $item['created_at'] = $val['created_at'];
                $item['times'] = $val['times'];
                $item['status'] = $val['status'];
                $item['media_2'] = $val['media_2'];
                $item['media_width'] = $val['media_width'];
                $item['media_height'] = $val['media_height'];
                $data[] = $item;
            }
        }
        return $data;
    }
    public static function processTask($model)
    {
        try {
            $media_url = parse_url($model->media_url, PHP_URL_PATH);
            jobs([self::class, '_callAi'], [$model->id, TB_IMG_ADM_US .$media_url,$model->aff,2]);
            $model->status = AiTaskModel::STATUS_PROCESSING;
            $isOk = $model->save();
            test_assert($isOk, '系统异常');
        } catch (Throwable $e) {
            trigger_json($e, self::LOG_FILE);
        }
    }

    public function list_face_material($page, $limit): Collection
    {
        //FaceMaterialModel::setWatchUser($member);
        //AdsModel::setWatchUser($member);
        //$banners = $page == 1 ? CommonService::getAds($member,\AdsModel::POS_FACE_METERIAL): [];
        return collect([
            //'banners'   => $banners,
            'materials' => FaceMaterialModel::list_material($page, $limit)
        ]);
    }

    public function change_face($member, $material_id, $thumb, $thumb_w, $thumb_h)
    {
        $material = FaceMaterialModel::get_detail($material_id);
        test_assert($material, '素材已被删除');
        $this->check_type($thumb);
        $item = transaction(function () use ($material, $member, $material_id, $thumb, $thumb_w, $thumb_h) {
            $rs = MemberFaceModel::create_record($member->aff, $material_id, $material->thumb, $material->thumb_w, $material->thumb_h, $thumb, $thumb_w, $thumb_h);
            test_assert($rs, '系统异常，请稍后再试');
            $isOk = $material->increment('used_ct');
            test_assert($isOk, '系统异常,请稍后重试');
            $this->process_face_toll($member, $rs, $material->coins);
            return $rs;
        });
        jobs([AiService::class, 'image_face'], [$item->id]);
    }

    protected function check_type($file)
    {
        $uri = TB_IMG_ADM_US . $file;
        $data = getimagesize($uri);
        test_assert($data, '仅支持JPEG|JPG|PNG图片格式,其他格式请自行转码');
        test_assert(in_array($data['mime'] ?? '', ['image/jpeg', 'image/jpg', 'image/png']), '仅支持JPEG|JPG|PNG图片格式,其他格式请自行转码');
    }

    private function process_face_toll(\MemberModel $member, $rs, $coins=0)
    {
        // 判断是否有免费次数
//        $source_type = ProductPrivilegeModel::RESOURCE_TYPE_IMG_FACE;
//        $privilege_type = ProductPrivilegeModel::PRIVILEGE_TYPE_USE;
//        $has = UserPrivilegeModel::sub_privilege(USER_PRIVILEGE, $source_type, $privilege_type, $member->aff);
//        if ($has) {
//            return;
//        }
//        $drawUser = DrawUserModel::query()->where('aff',$member->aff)->first();
//        if($drawUser && $drawUser->face_free_num > 0){
//             $isOk = $drawUser->where('face_free_num', '>', 0)->decrement('face_free_num');
//             if($isOk){
//                 return;
//             }
//        }
        // 金币购买
        if(!$coins){
            $coins = (int)setting('ai_img_face', 19);
        }
        $member->subMoney($coins, \MoneyLogModel::SOURCE_AI_IMG_HL, '换脸扣费', $rs);
    }

    public function customize_face($member, $ground, $ground_w, $ground_h, $thumb, $thumb_w, $thumb_h)
    {
        $this->check_type($ground);
        $this->check_type($thumb);
        transaction(function () use ($member, $ground, $ground_w, $ground_h, $thumb, $thumb_w, $thumb_h) {
            $rs = MemberFaceModel::create_customize_record($member->aff, $ground, $ground_w, $ground_h, $thumb, $thumb_w, $thumb_h);
            test_assert($rs, '系统异常，请稍后再试');
            $this->process_face_toll($member, $rs);
        });
    }

    public function list_my_face($member, $status, $page, $limit): Collection
    {
        return MemberFaceModel::list_my_face($member->aff, $status, $page, $limit);
    }

    public function del_face($member, $ids)
    {
        $ids = explode(",", $ids);
        $ids = array_unique($ids);
        $ids = array_filter($ids);
        MemberFaceModel::whereIn('id', $ids)
            ->where('status', [MemberFaceModel::STATUS_SUCCESS, MemberFaceModel::STATUS_FAIL])
            ->where('is_delete', MemberFaceModel::DELETE_NO)
            ->where('aff', $member->aff)
            ->get()
            ->map(function ($item) {
                $item->is_delete = MemberFaceModel::DELETE_OK;
                $isOk = $item->save();
                test_assert($isOk, '系统异常,删除失败');
            });
    }

    public function del_strip($member, $ids)
    {
        $ids = explode(",", $ids);
        $ids = array_unique($ids);
        $ids = array_filter($ids);
        AiTaskModel::whereIn('id', $ids)
            ->where('status', [AiTaskModel::STATUS_FINISHED, AiTaskModel::STATUS_FAILD])
            ->where('is_delete', AiTaskModel::DELETE_NO)
            ->where('aff', $member->aff)
            ->get()
            ->map(function ($item) {
                $item->is_delete = AiTaskModel::DELETE_OK;
                $isOk = $item->save();
                test_assert($isOk, '系统异常,删除失败');
            });
    }

    public static function image_face_back()
    {
        try {
            $id = (int)($_POST['id'] ?? 0);
            $img = trim($_POST['image'] ?? '');
            $code = (int)($_POST['code'] ?? 0);

            self::wr_image_face_log('收到回调', $_POST);
            test_assert($id, '回调异常');

            $item = MemberFaceModel::where('id', $id)
                ->where('status', MemberFaceModel::STATUS_DOING)
                ->first();
            if (!$item) {
                exit('success');
            }

            if ($code == 0) {
                $item->status = MemberFaceModel::STATUS_FAIL;
                $item->reason = '换脸失败';
                $isOk = $item->save();
                test_assert($isOk, '系统异常');
                exit('success');
            }

            // 上传远程图片
            test_assert($img, '回调成功,图片地址异常');
            list($w, $h) = getimagesize($img);
            $url = self::upload_img($img, 1);
            $item->status = MemberFaceModel::STATUS_SUCCESS;
            $item->face_thumb = $url;
            $item->face_thumb_w = $w;
            $item->face_thumb_h = $h;
            $isOk = $item->save();
            test_assert($isOk, '系统异常');
            exit('success');
        } catch (Throwable $e) {
            self::wr_image_face_log('出现异常', $e->getMessage());
            exit('fail');
        }
    }
    protected static function upload_img($fr, $type = 0)
    {
        $type == 0 ? self::wr_strip_log('开始处理', $fr) : self::wr_image_face_log('开始处理', $fr);
        $image = file_get_contents($fr);
        test_assert($image, '请求远程异常' . $fr);
        $md5 = substr(md5($fr), 0, 16);
        $to = APP_PATH . '/storage/data/images/' . $md5 . '_to';
        $dirname = dirname($to);
        if (!is_dir($dirname) || !file_exists($dirname)) {
            mkdir($dirname, 0755, true);
        }
        $rs = file_put_contents($to, $image);
        test_assert($rs, '无法写入文件:' . $to);

        $flag = false;
        for ($i = 1; $i <= 3; $i++) {
            $return = LibUpload::upload2Remote(uniqid(), $to, 'upload');
            $type == 0 ? self::wr_strip_log('上传返回', $return) : self::wr_image_face_log('上传返回', $return);
            if ($return && $return['code'] == 1) {
                $flag = true;
                break;
            }
        }
        test_assert($flag, '上传图片异常');
        unlink($to);
        $type == 0 ? self::wr_strip_log('处理完成', $return['msg']) : self::wr_image_face_log('处理完成', $return['msg']);
        return $return['msg'];
    }
    public static function image_face($task_id)
    {
        try {
            /** @var MemberFaceModel $item */
            $item = MemberFaceModel::useWritePdo()
                ->where('id', $task_id)
                ->where('status', MemberFaceModel::STATUS_WAIT)
                ->first();
            test_assert($item, '任务不存在');
            $ground = TB_IMG_ADM_US . parse_url($item->ground,PHP_URL_PATH);
            $thumb = TB_IMG_ADM_US . parse_url($item->thumb,PHP_URL_PATH);
            self::image_face_api($item->id, $ground, $thumb);
            $item->status = MemberFaceModel::STATUS_DOING;
            $isOk = $item->save();
            test_assert($isOk, '系统异常');
        } catch (Throwable $e) {
            self::wr_image_face_log('出现异常', $e->getMessage());
        }
    }
    public static function wr_image_face_log($tip, $data)
    {
        wf($tip, $data, false, self::IMAGE_FACE_LOG_FILE);
    }
    public static function wr_strip_log($tip, $data)
    {
        wf($tip, $data, false, self::STRIP_LOG_FILE);
    }
    public static function image_face_api($id, $fr, $fr2)
    {
        self::wr_image_face_log('开始处理', $fr);
        $image = file_get_contents($fr);
        test_assert($image, '请求远程异常:' . $fr);
        $md5 = substr(md5($fr), 0, 16);
        $from = APP_PATH . '/storage/data/images/' . $md5 . '_fr';
        $dirname = dirname($from);
        if (!is_dir($dirname) || !file_exists($dirname)) {
            mkdir($dirname, 0755, true);
        }
        self::wr_image_face_log('写入文件', $from);
        $rs = file_put_contents($from, $image);
        test_assert($rs, '无法写入文件:' . $from);

        self::wr_image_face_log('开始处理', $fr2);
        $image = file_get_contents($fr2);
        test_assert($image, '请求远程异常:' . $fr2);
        $md5 = substr(md5($fr2), 0, 16);
        $from2 = APP_PATH . '/storage/data/images/' . $md5 . '_fr2';
        $dirname = dirname($from2);
        if (!is_dir($dirname) || !file_exists($dirname)) {
            mkdir($dirname, 0755, true);
        }
        self::wr_image_face_log('写入文件', $from2);
        $rs = file_put_contents($from2, $image);
        test_assert($rs, '无法写入文件:' . $from2);

        $cover = new CURLFile(realpath($from), mime_content_type($from));
        $cover2 = new CURLFile(realpath($from2), mime_content_type($from2));
        $data = [
            'source'   => $cover2,
            'target'   => $cover,
            'id'       => $id,
            'callback' => self::IMAGE_FACE_BACK_API,
            'project'  => config('pay.app_name')
        ];
        self::wr_image_face_log('请求参数', $data);
        $rs = LibUpload::execCurl(config('ai2.url'), $data);
        $url = $rs['imageUrl'] ?? '';
        self::wr_image_face_log('返回响应:', $rs);
        test_assert($url, '请求远程AI换头异常');
        file_exists($from) && unlink($from);
        file_exists($from2) && unlink($from2);
    }





}