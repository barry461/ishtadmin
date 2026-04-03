<?php

use service\AppService;
use service\CommonService;
use helper\QueryHelper;

class AppController extends BaseController
{
    // 获取类型
    public function indexAction()
    {
        $server = new AppService();
        $data = [
            // 获取banner
            //'banner'     => AdsModel::listPos(AdsModel::POS_APP_CENTER_BANNER),
            'banner'     => CommonService::getAds($this->member,AdsModel::POS_APP_CENTER_BANNER),
            // 获取运用
            'categories' => $server->listCategories($this->member),
        ];
        return $this->showJson($data);
    }

    // 翻页
    public function listAction()
    {
        try {
            $validator = \helper\Validator::make($this->data, [
                'id' => 'required|numeric',
            ]);
            $rs = $validator->fail($msg);
            test_assert(!$rs, $msg);

            $id = $this->data['id'];
            list($page, $limit, $ix) = QueryHelper::pageLimit();
            $server = new AppService();
            $list = $server->listApps($this->member, $id, $page, $ix, 100);
            return $this->listJson($list);
        } catch (Throwable $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    // 点击上报
    public function clickAction()
    {
        if (!isset($this->data['type'])) {
            $this->data['type'] = DayClickModel::TYPE_APP;
        }
        return $this->forward('Api', 'Home', 'click_report');
    }
}