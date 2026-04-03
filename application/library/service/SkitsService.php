<?php
namespace service;

use EpisodeModel;
use MemberModel;
use MoneyLogModel;
use ProductPrivilegeModel;
use SkitsModel;
use SkitsPayModel;
use UserPrivilegeModel;

class SkitsService
{
    public function unlockSkits(MemberModel $member, $skitsId){
        $skits = SkitsModel::find($skitsId);
        test_assert($skits, '合集不存在');
        test_assert($skits->is_open, '此合集不能购买');
        //判断是否有会员权限
        $hasPrivilege = UserPrivilegeModel::hasPrivilege(USER_PRIVILEGE,
            ProductPrivilegeModel::RESOURCE_TYPE_SKITS,
            ProductPrivilegeModel::PRIVILEGE_TYPE_VIEW);
        if ($hasPrivilege){
            test_assert(false, '已经有观看权限，无需购买');
        }
        $record = SkitsPayModel::where('aff', $member->aff)
            ->where('type', SkitsPayModel::TYPE_SKITS)
            ->where('cid', $skitsId)
            ->first();
        if (!empty($record)){
            test_assert(false, '已经购买');
        }
        //扣款
        transaction(function () use ($member, $skits){
            $model = SkitsPayModel::make();
            $model->aff = $member->aff;
            $model->type = SkitsPayModel::TYPE_SKITS;
            $model->cid = $skits->id;
            $model->created_at = \Carbon\Carbon::now();
            $model->updated_at = \Carbon\Carbon::now();
            $isOk = $model->save();
            test_assert($isOk, '解锁失败');
            $description = "解锁 短剧合集ID:" . $skits->id;
            //折扣
            $discount = UserPrivilegeModel::hasPrivilege(USER_PRIVILEGE,
                ProductPrivilegeModel::RESOURCE_TYPE_SKITS,
                ProductPrivilegeModel::PRIVILEGE_TYPE_DISCOUNT);
            if ($discount){
                $discount = $discount / 100;
            }else{
                $discount = 1;
            }
            $coins = ceil($skits->coins * $discount);
            $isOk = $member->subMoney($coins, MoneyLogModel::SOURCE_SKITS, $description, $model);
            test_assert($isOk , '扣款失败');
            $skits->increment('buy_num', 1, ['buy_coins' => \DB::raw('buy_coins + ' . $coins)]);

            $key = SkitsPayModel::generateSkitsRk($member->aff);
            redis()->sAdd($key, $skits->id);
            $member->clearCached();
        });
    }

    public function buyEpisode(MemberModel $member, $episodeId){
        $episode = EpisodeModel::find($episodeId);
        test_assert($episode, '剧集不存在');
        test_assert($episode->status, '剧集不存在');
        //判断是否有会员权限
        $hasPrivilege = UserPrivilegeModel::hasPrivilege(USER_PRIVILEGE,
            ProductPrivilegeModel::RESOURCE_TYPE_SKITS,
            ProductPrivilegeModel::PRIVILEGE_TYPE_VIEW);
        if ($hasPrivilege){
            test_assert(false, '已经有观看权限，无需购买');
        }
        $exist = SkitsPayModel::where('aff', $member->aff)
            ->where('type', SkitsPayModel::TYPE_EPISODE)
            ->where('cid', $episodeId)
            ->exists();
        if ($exist){
            test_assert(false, '已经购买');
        }
        transaction(function () use ($member, $episode){
            $model = SkitsPayModel::make();
            $model->aff = $member->aff;
            $model->type = SkitsPayModel::TYPE_EPISODE;
            $model->cid = $episode->id;
            $model->created_at = \Carbon\Carbon::now();
            $model->updated_at = \Carbon\Carbon::now();
            $isOk = $model->save();
            test_assert($isOk, '解锁失败');
            $description = "解锁 短剧,剧集ID:" . $episode->id;
            //折扣
            $discount = UserPrivilegeModel::hasPrivilege(USER_PRIVILEGE,
                ProductPrivilegeModel::RESOURCE_TYPE_SKITS,
                ProductPrivilegeModel::PRIVILEGE_TYPE_DISCOUNT);
            if ($discount){
                $discount = $discount / 100;
            }else{
                $discount = 1;
            }
            $coins = ceil($episode->coins * $discount);
            $isOk = $member->subMoney($coins, MoneyLogModel::SOURCE_EPISODE, $description, $model);
            test_assert($isOk , '扣款失败');
            $episode->increment('buy_num', 1, ['buy_coins' => \DB::raw('buy_coins + ' . $coins)]);

            $key = SkitsPayModel::generateEpisodeRk($member->aff);
            redis()->sAdd($key, $episode->id);
            $member->clearCached();
        });
        return ['playUrl' => $episode->play_url];
    }
}