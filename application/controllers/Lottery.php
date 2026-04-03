<?php

class LotteryController extends \Yaf\Controller_Abstract
{

    //签到接口
    public function qdAction()
    {
        try {
            $id = $_GET['id'];
            $token = $_GET['token'];
            test_assert($id && $token, '前往'.register('site.app_name').'APP内参加抽奖活动');

            $uid = getCode2ID($token);
            test_assert($uid, '前往'.register('site.app_name').'APP内参加抽奖活动');
            $member = MemberModel::find($uid);
            test_assert($member, '前往'.register('site.app_name').'APP内参加抽奖活动');
            $lottery = LotteryModel::info($id);
            test_assert($lottery, '活动不存在');
            test_assert($lottery->lottery_status, '活动已下架');
            test_assert(\Carbon\Carbon::now()->gte($lottery->lottery_begin), '活动未开始');
            test_assert(\Carbon\Carbon::now()->lte($lottery->lottery_end), '活动已结束');
            $service = new \service\LotteryService();
            $lottery_num = $service->qd($member, $lottery->id);
            return $this->showJson(['lottery_num' => $lottery_num], 200, '签到成功');
        }catch (Throwable $e){
            return $this->errorJson($e->getMessage());
        }
    }

    //初始化接口
    public function indexAction()
    {
        try {
            $id = $_GET['id'];
            $token = $_GET['token'];
            test_assert($id && $token, '前往'.register('site.app_name').'APP内参加抽奖活动');

            $uid = getCode2ID($token);
            test_assert($uid, '前往'.register('site.app_name').'APP内参加抽奖活动');
            $member = MemberModel::find($uid);
            test_assert($member, '前往'.register('site.app_name').'APP内参加抽奖活动');
            $lottery = LotteryModel::info($id);
            test_assert($lottery, '活动不存在');
            test_assert($lottery->lottery_status, '活动已下架');
            $service = new \service\LotteryService();
            $result = $service->init($member, $lottery);
            return $this->showJson($result);
        }catch (Throwable $e){
            return $this->errorJson($e->getMessage());
        }
    }


    //抽奖接口
    public function lotteryAction()
    {
        try {
            $id = $_GET['id'];
            $onceNumber = $_GET['onceNumber'];
            $token = $_GET['token'];
            test_assert($id && $token && $onceNumber, '前往'.register('site.app_name').'APP内参加抽奖活动');

            $uid = getCode2ID($token);
            test_assert($uid, '前往'.register('site.app_name').'APP内参加抽奖活动');
            $member = MemberModel::find($uid);
            test_assert($member, '前往'.register('site.app_name').'APP内参加抽奖活动');
            $lottery = LotteryModel::find($id);
            test_assert($lottery->lottery_status, '活动已下架');
            test_assert(\Carbon\Carbon::now()->gte($lottery->lottery_begin), '活动未开始');
            test_assert(\Carbon\Carbon::now()->lte($lottery->lottery_end), '活动已结束');
            test_assert(in_array($onceNumber, [1, 5, 10, 20]), '抽奖数据异常');
            $service = new \service\LotteryService();
            $result = $service->draw($member, $id, $onceNumber);
            return $this->showJson($result);
        } catch (Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    public function lottery_logAction()
    {
        try {
            $id = $_GET['id'];
            $token = $_GET['token'];
            test_assert($id && $token, '前往'.register('site.app_name').'APP内参加抽奖活动');
            $uid = getCode2ID($token);
            test_assert($uid, '前往'.register('site.app_name').'APP内参加抽奖活动');
            $member = MemberModel::find($uid);
            test_assert($member, '前往'.register('site.app_name').'APP内参加抽奖活动');
            $lottery = LotteryModel::info($id);
            test_assert($lottery, '活动不存在');
            $list = LotteryLogModel::list($member->uid, $lottery->id);
            return $this->showJson($list);
        }catch (Throwable $exception){
            return $this->errorJson($exception->getMessage());
        }
    }

    public function showJson($data, $status = 200, $msg = null)
    {
        $data = [
            'data'   => $data,
            'msg'    => $msg,
            'status' => $status,
        ];
        $response = $this->getResponse();
        $response->setBody(json_encode($data));
        $response->setHeader('content-Type', 'application/json', true);
        return $response;
    }

    public function errorJson($msg, $status = 0, $data = null)
    {
        return $this->showJson($data, $status, $msg);
    }
}