<?php

use helper\Validator;

class RankController extends BaseController
{

    public function rank_listAction(){
        try {
            $validator = Validator::make($this->data, [
                'type' => 'required|enum:day,week,month', //类型 日 月 周
            ]);
            if ($validator->fail($msg)) {
                throw new Exception($msg);
            }
            if ($this->page > 1){
                return $this->listJson([]);
            }
            $type = $this->data['type'];
            $sort_key = '';
            $expire_time = 600;
            switch ($type){
                case 'day':
                    $sort_key = date('Ymd');
                    break;
                case 'week':
                    $expire_time = 3600;
                    $sort_key = date('W');
                    break;
                case 'month':
                    $expire_time = 7200;
                    $sort_key = date('Ym');
                    break;
                default:
                    test_assert(false, '类型错误');
                    break;
            }
            $redis_key = sprintf(ContentsModel::CONTENTS_RANK_VIEW, $sort_key);
            $list = cached(sprintf('contents:view:rank:v1:%s', $type))
                ->group('contents:view:rank')
                ->chinese('文章排行榜')
                ->fetchJson(function () use ($redis_key, $expire_time){
                    $rankInfo = redis()->zRevRange($redis_key, 0,  19, ['withscores' => true]);
                    if (!$rankInfo){
                        return [];
                    }
                    $cids = array_keys($rankInfo);
                    $table = \Yaf\Registry::get('database')->prefix;
                    $fullTable = $table.'contents';
                    $list = ContentsModel::query()
                        ->with([
                            'relationships' => function ($query) {
                                $query->with('meta');
                            },
                        ])
                        ->selectRaw("$fullTable.cid,title,created,`order`,type,status,commentsNum,is_home,home_top,is_slice,authorId,view,fake_view")
                        ->with('fields', 'author')
                        ->whereIn('cid', $cids)
                        ->whereIn('type', [ContentsModel::TYPE_POST, ContentsModel::TYPE_BIG_WENT, ContentsModel::TYPE_SKITS])
                        ->where('status', ContentsModel::STATUS_PUBLISH)
                        ->where('is_slice', 1)
                        ->where('app_hide', ContentsModel::APP_HIDE_NO)
                        ->limit(20)
                        ->get()
                        ->each(function (ContentsModel $model) use ($rankInfo){
                            $model->loadTagWithCategory();
                            $model->val = max($rankInfo[$model->cid], 0);
                        });

                    if (!is_array($list)){
                        $list = $list->toArray();
                    }
                    array_multisort(array_column($list, 'val'), SORT_DESC, $list);
                    return array_slice($list, 0, 10);
                }, $expire_time);

            return $this->listJson($list);
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    public function hot_listAction(){
        try {
//            $cids = setting('contents_hot_rank', '');
//            $cids = explode(',', $cids);
//            $cids = collect($cids)->map(function ($cid){
//                return intval($cid);
//            })->toArray();
//            test_assert($cids, '热门文章未配置');
            if ($this->page > 1){
                return $this->listJson([]);
            }
            $list = cached('contents:hot:rank')
                ->group('contents:view:rank')
                ->chinese('文章排行榜')
                ->fetchPhp(function (){
                    $table = \Yaf\Registry::get('database')->prefix;
                    $fullTable = $table.'contents';

                    return ContentsModel::query()
                        ->with([
                            'relationships' => function ($query) {
                                $query->with('meta');
                            },
                        ])
                        ->selectRaw("$fullTable.cid,title,created,`order`,type,status,commentsNum,is_home,home_top,is_slice,authorId,view,fake_view")
                        ->with('fields', 'author')
//                        ->whereIn('cid', $cids)
                        ->whereIn('type', [ContentsModel::TYPE_POST, ContentsModel::TYPE_BIG_WENT, ContentsModel::TYPE_SKITS])
                        ->where('status', ContentsModel::STATUS_PUBLISH)
                        ->where('is_slice', 1)
                        ->where('app_hide', ContentsModel::APP_HIDE_NO)
                        ->limit(10)
                        ->orderByDesc('fake_view')
                        ->get()
                        ->each(function (ContentsModel $model){
                            $model->loadTagWithCategory();
                        });
                    //return array_keep_idx($list, $cids, 'cid');
                });

            return $this->listJson($list);
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

}