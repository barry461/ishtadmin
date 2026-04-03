<?php


namespace service;

class AppService
{
    public function listCategories(\MemberModel $member)
    {
        if (CommonService::isPcQuest($member->oauth_type)){
            return \PcAppCategoryModel::listCategories();
        }else{
            return \AppCategoryModel::listCategories();
        }
    }

    public function listApps(\MemberModel $member, $id, $page, $ix, $limit)
    {
        if (CommonService::isPcQuest($member->oauth_type)){
            return \PcAppModel::listApps($id, $page, $ix, $limit);
        }else{
            return \AppModel::listApps($id, $page, $ix, $limit);
        }
    }
}