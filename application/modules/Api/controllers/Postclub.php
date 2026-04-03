<?php

use helper\QueryHelper;
use service\PostClubService;

class PostclubController extends BaseController
{

    public function create_clubAction()
    {
        try {
            $month = $this->data['month'] ?? null;
            $quarter = $this->data['quarter'] ?? null;
            $year = $this->data['year'] ?? null;
            if (empty($month) && empty($quarter) && empty($year)) {
                throw new RuntimeException('价格设置错误');
            }
            $this->verifyMemberSayRole();
            $this->verifyFrequency(24 * 3600 , 1 , 'create_club' , '每次设置订阅价格，需要间隔至少1天');
            test_assert($this->member->auth_status,'不是官方认证博主,不能设置订阅价格');
            $service = new PostClubService;
            $service->createOrUpdate($this->member, $month, $quarter, $year);

            return $this->successMsg('ok');
        } catch (\Throwable $e) {
            return $this->errorJson($e->getMessage());
        }
    }


    public function join_clubAction(): bool
    {
        try {
            $Validator = \helper\Validator::make($this->data, [
                'post_aff' => 'required|numeric',
                'type'    => 'required|enum:month,quarter,year',
            ]);
            if ($Validator->fail($msg)) {
                throw new RuntimeException($msg);
            }
            $clubAff = $this->data['post_aff'];
            $type = $this->data['type'];

            //频率控制
            $key = sprintf('join:club:%d:%d', $this->member->aff, $clubAff);
            \helper\Util::PanicFrequency($key, 1, 10);

            $types = [
                'month' => PostClubMembersModel::TYPE_MONTH,
                'quarter' =>  PostClubMembersModel::TYPE_QUARTER,
                'year' => PostClubMembersModel::TYPE_YEAR,
            ];
            $type = $types[$type];

            $service = new PostClubService;
            $service->joinClub($this->member, $clubAff, $type);

            $postId = $this->data['post_id'];
            $content = '';
            if ($postId){
                $content = PostModel::getPostContentById($postId);
                $content = PostModel::replaceSym($content);
                $content = \tools\LibMarkdown::loadMarkdown($content);
                $content = \PostModel::symReplace($content);
            }

            return $this->showJson(['content' => $content]);
        } catch (\Throwable $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    //我的订阅
    public function list_subscribeAction(){
        try {
            $service = new PostClubService();
            list($page, $limit) = QueryHelper::pageLimit();
            $res = $service->listSubscribe($this->member, $page, $limit);
            return $this->listJson($res);
        }catch (Throwable $e){
            return $this->errorJson($e->getMessage());
        }
    }


}