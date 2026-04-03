<?php

namespace service;

use MoneyLogModel as MoneyLog;
use MoneyIncomeLogModel as IncomeLog;
use ProductPrivilegeModel;
use UserPrivilegeModel;

class PostClubService
{

    public function createOrUpdate(
        \MemberModel $member,
        $month,
        $quarter,
        $year
    ) {
        $club = \PostClubsModel::findByAff($member->aff, true);
        if (empty($club)) {
            $club = \PostClubsModel::make();
            $club->aff = $member->aff;
        }
        $club->whenSet($month > 0, 'month', $month);
        $club->whenSet($quarter > 0, 'quarter', $quarter);
        $club->whenSet($year > 0, 'year', $year);
        $club->status = \PostClubsModel::STATUS_YES;
        $club->save();
        $member->post_club_month = $club->month;
        $member->post_club_quarter = $club->quarter;
        $member->post_club_year = $club->year;
        $member->post_club_id = $club->id;
        $member->save();
        $creator = \PostCreatorModel::findByAff($member->aff,true);
        if (empty($creator)){
            $creator = \PostCreatorModel::make();
            $creator->aff = $member->aff;
            $creator->nickname = $member->nickname;
            $creator->status = \MemberModel::AUTH_STATUS_YES;
            $creator->created_at = \Carbon\Carbon::now();
            $creator->updated_at = \Carbon\Carbon::now();
        }
        $creator->post_club_month = $club->month;
        $creator->post_club_quarter = $club->quarter;
        $creator->post_club_year = $club->year;
        $creator->save();
        $member->clearCached();

        //删除帖子列表缓存
        jobs([\PostModel::class, 'clearAllCache']);
    }

    public function joinClub(\MemberModel $member, $clubAff, $type)
    {
        $member = $member->refresh();
        return transaction(function () use ($member, $clubAff, $type) {
            $club = \PostClubsModel::findByAff($clubAff, true);
            test_assert($club, '订阅信息不存在');
            test_assert($club->status == \PostClubsModel::STATUS_YES, '当前订阅状态异常');
            test_assert($member->aff != $club->aff, '您不能订阅自己');
            test_assert(isset(\PostClubMembersModel::TYPE[$type]) , '订阅的类型错误');
            $model = \PostClubMembersModel::findByAffClubId($member->aff , $club->id);
            if (empty($model)){
                $model = \PostClubMembersModel::make();
                $model->aff = $member->aff;
                $model->club_id = $club->id;
                $model->club_aff = $club->aff;
            }
            $time = \PostClubMembersModel::TIME[$type] ?? 0;
            //折扣
            $discount = UserPrivilegeModel::hasPrivilege(USER_PRIVILEGE,
                ProductPrivilegeModel::RESOURCE_TYPE_POST,
                ProductPrivilegeModel::PRIVILEGE_TYPE_DISCOUNT);
            if ($discount){
                $discount = $discount / 100;
            }else{
                $discount = 1;
            }

            if ($type == \PostClubMembersModel::TYPE_MONTH) {
                $coins = ceil($club->month * $discount);
                $club->month_income += $coins;
                $description = "订阅 月卡";
            } elseif ($type == \PostClubMembersModel::TYPE_QUARTER) {
                $coins = ceil($club->quarter * $discount);
                $club->quarter_income += $coins;
                $description = "订阅 季卡";
            } elseif ($type == \PostClubMembersModel::TYPE_YEAR) {
                $coins = ceil($club->year * $discount);
                $description = "订阅 年卡";
                $club->year_income += $coins;
            } else {
                throw new \RuntimeException('数据错误');
            }
            if ($time <= 0 || $coins <= 0) {
                throw new \RuntimeException('数据错误');
            }
            if (empty($model->exists)) {
                $club->member_num += 1;
            }
            $model->type = max($type , $model->type);
            $model->expired_at = max($model->expired_at , time()) + $time * 86400;
            $isOk = $model->save();
            test_assert($isOk , '订阅失败1');
            $isOk = $club->save();
            test_assert($isOk , '订阅失败2');
            $isOk = $member->subMoney($coins, MoneyLog::SOURCE_POSTCLUB, $description, $model);
            test_assert($isOk , '扣款失败');
            if ($club->user->uid) {
                $isOk = $club->user->addIncome($coins, $member, $model, IncomeLog::SOURCE_POSTCLUB, $description);
                test_assert($isOk, '添加用户收益失败');
                $club->user->post_club_total = $club->getTotalIncomeAttribute();
                $isOk = $club->user->save();
                test_assert($isOk , '保存用户的收益失败');
            }

            $key = \PostClubMembersModel::generateRk($member->aff);
            redis()->hSet($key, $club->aff, $model->expired_at);
            $member->clearCached();
        });
    }

    //订阅列表
    public static function listSubscribe(\MemberModel $member, $page, $limit)
    {
        \MemberModel::setWatchUser($member);
        return \PostClubMembersModel::query()
            ->with('club_member:uid,aff,uuid,nickname,thumb,person_signnatrue,vip_level,followed_count,post_count')
            ->where('aff', $member->aff)
            ->where('expired_at', '>',TIMESTAMP)
            ->orderByDesc('created_at')
            ->forPage($page,$limit)
            ->get()
            ->pluck('club_member')
            ->map(function (\MemberModel $item){
                return [
                    'uid'        => $item->uid,
                    'aff'        => $item->aff,
                    'nickname'   => $item->nickname,
                    'vip_level'  => $item->vip_level,
                    'person_signnatrue' => $item->person_signnatrue,
                    'followed_count' => $item->followed_count,
                    'post_count' => $item->post_count,
                    'thumb'      => $item->thumb,
                    'is_follow'  => $item->is_follow,
                    'thumb_bg'   => $item->thumb_bg,
                    'vip_bg'     => $item->vip_bg,
                ];
            });
    }
}