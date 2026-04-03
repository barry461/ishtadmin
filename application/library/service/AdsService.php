<?php

namespace service;

use tools\HttpCurl;
use AdsModel;
use BannerModel;
use NoticeModel;

class AdsService
{
    const ADD_MSG_TPL = <<<KL
操作人员: %s
项目名称: %s
广告类型: %s
操作: %s
新值:
    位置: %s
    状态: %s
    有效: %s
    链接: %s
操作记录: %s
操作时间: %s
KL;
    const UPDATE_MSG_TPL = <<<KL
操作人员: %s
项目名称: %s
广告类型: %s
操作类型: %s
原值: 
    位置: %s
    状态: %s
    有效: %s
    链接: %s
新值:
    位置: %s
    状态: %s
    有效: %s
    链接: %s
操作记录: %s
操作时间: %s
KL;
    const DELETE_MSG_TPL = <<<KL
操作人员: %s
项目名称: %s
广告类型: %s
操作类型: %s
原值: 
    位置: %s
    状态: %s
    有效: %s
    链接: %s
操作记录: %s
操作时间: %s
KL;
    const NOTIFY_URL = 'https://tg.microservices.vip/index.php?m=index&a=sendMessage';
    const SIGN_KEY = 'er9bEFZko1lUkyCsUtFWO3WtFnTN';
    const ACTION_TIPS = [
        'created' => '新增',
        'updated' => '更新',
        'deleted' => '删除',
    ];

    public static function to_remote($msg, $last_link)
    {
        $at = time();
        $sign = md5($at . self::SIGN_KEY);
        $data = [
            'msg'         => $msg,
            'timestamps'  => $at,
            'app_name'    => config('pay.app_name'),
            'sign'        => $sign,
            'newest_link' => $last_link
        ];
        return HttpCurl::post(self::NOTIFY_URL, $data);
    }

    private static function send_add_log($record_id, $username, $type, $action_str, $title, $icon, $position_str, $status_str, $valid_at, $link)
    {
        return self::to_remote(sprintf(self::ADD_MSG_TPL,
            $username, register('site.app_name').'APP', $type, $action_str,
            $position_str, $status_str, $valid_at, $link,
            $record_id, date('Y-m-d H:i:s')
        ), $link);
    }

    private static function send_update_log($record_id, $username, $type, $action_str, $old_title, $old_icon, $old_position_str, $old_status_str, $old_valid_at, $old_link, $new_title, $new_icon, $new_position_str, $new_status_str, $new_valid_at, $new_link)
    {
        return self::to_remote(sprintf(self::UPDATE_MSG_TPL,
            $username, register('site.app_name').'APP', $type, $action_str,
            $old_position_str, $old_status_str, $old_valid_at, $old_link,
            $new_position_str, $new_status_str, $new_valid_at, $new_link,
            $record_id, date('Y-m-d H:i:s')
        ), $new_link);
    }

    private static function send_delete_log($record_id, $username, $type, $action_str, $title, $icon, $position_str, $status_str, $valid_at, $link)
    {
        return self::to_remote(sprintf(self::DELETE_MSG_TPL,
            $username, register('site.app_name').'APP', $type, $action_str,
            $position_str, $status_str, $valid_at, $link,
            $record_id, date('Y-m-d H:i:s')
        ), $link);
    }

    private static function send_log($record_id, $action, $username, $type, $action_str, $old_title, $old_icon, $old_position_str, $old_status_str, $old_valid_at, $old_link, $new_title, $new_icon, $new_position_str, $new_status_str, $new_valid_at, $new_link)
    {
        if ($action == 'created') {
            return self::send_add_log($record_id, $username, $type, $action_str, $new_title, $new_icon, $new_position_str, $new_status_str, $new_valid_at, $new_link);
        }
        if ($action == 'updated') {
            return self::send_update_log($record_id, $username, $type, $action_str, $old_title, $old_icon, $old_position_str, $old_status_str, $old_valid_at, $old_link, $old_title, $old_icon, $new_position_str, $new_status_str, $new_valid_at, $new_link);
        }
        return self::send_delete_log($record_id, $username, $type, $action_str, $old_title, $old_icon, $old_position_str, $old_status_str, $old_valid_at, $old_link);
    }

    private static function check_notify($action, $old, $new, $fields): bool
    {
        if ($action == 'created') {
            return true;
        }
        if ($action == 'deleted') {
            return true;
        }
        $is_notify = false;
        foreach ($fields as $field) {
            $old_value = $old[$field] ?? '';
            $new_value = $new[$field] ?? '';
            if ($old_value != $new_value) {
                $is_notify = true;
            }
        }
        return $is_notify;
    }

    public static function process_ads_log($ads_id, $record_id, $username, $action, $old, $new)
    {
        $listen_fields = ['status', 'url_config'];
        if (!self::check_notify($action, $old, $new, $listen_fields)) {
            return;
        }

        $type = '固定位广告 ID: #' . $ads_id;
        $action_str = self::ACTION_TIPS[$action];
        $old_title = $old['title'] ?? '';
        $new_title = $new['title'] ?? '';
        $old_icon = $old['img_url'] ?? '';
        $new_icon = $new['img_url'] ?? '';
        $old_status = $old['status'] ?? '';
        $old_status_str = AdsModel::STATUS[$old_status] ?? '';
        $new_status = $new['status'] ?? '';
        $new_status_str = AdsModel::STATUS[$new_status] ?? '';
        $old_st_at = $old['start_at'] ?? '';
        $old_sp_at = $old['end_at'] ?? '';
        $old_valid_at = $old_st_at . ' ~ ' . $old_sp_at;
        $new_st_at = $new['start_at'] ?? '';
        $new_sp_at = $new['end_at'] ?? '';
        $new_valid_at = $new_st_at . ' ~ ' . $new_sp_at;
        $old_link = $old['url_config'] ?? '';
        $new_link = $new['url_config'] ?? '';
        $old_position = $old['position'] ?? '';
        $new_position = $new['position'] ?? '';
        $old_position_str = AdsModel::POSITION[$old_position] ?? '';
        $new_position_str = AdsModel::POSITION[$new_position] ?? '';
        $ret = self::send_log($record_id, $action, $username, $type, $action_str, $old_title, $old_icon, $old_position_str, $old_status_str, $old_valid_at, $old_link, $new_title, $new_icon, $new_position_str, $new_status_str, $new_valid_at, $new_link);
    }

    public static function process_notice_log($ads_id, $record_id, $username, $action, $old, $new)
    {
        $listen_fields = ['status', 'url'];
        if (!self::check_notify($action, $old, $new, $listen_fields)) {
            return;
        }

        $type = '弹框广告 ID: #' . $ads_id;
        $action_str = self::ACTION_TIPS[$action];
        $old_title = $old['title'] ?? '';
        $new_title = $new['title'] ?? '';
        $old_icon = str_replace("__WAPIIMG__", TB_IMG_ADM_US, $old['img_url'] ?? '');
        $new_icon = str_replace("__WAPIIMG__", TB_IMG_ADM_US, $new['img_url'] ?? '');
        $old_status = $old['status'] ?? '';
        $old_status_str = NoticeModel::STATUS[$old_status] ?? '';
        $new_status = $new['status'] ?? '';
        $new_status_str = NoticeModel::STATUS[$new_status] ?? '';
        $old_st_at = $old['start_at'] ?? '';
        $old_sp_at = $old['end_at'] ?? '';
        $old_valid_at = $old_st_at . ' ~ ' . $old_sp_at;
        $new_st_at = $new['start_at'] ?? '';
        $new_sp_at = $new['end_at'] ?? '';
        $new_valid_at = $new_st_at . ' ~ ' . $new_sp_at;
        $old_link = $old['url'] ?? '';
        $new_link = $new['url'] ?? '';
        $old_position = $old['pos'] ?? '';
        $new_position = $new['pos'] ?? '';
        $old_position_str = NoticeModel::POS[$old_position] ?? '';
        $new_position_str = NoticeModel::POS[$new_position] ?? '';
        $ret = self::send_log($record_id, $action, $username, $type, $action_str, $old_title, $old_icon, $old_position_str, $old_status_str, $old_valid_at, $old_link, $new_title, $new_icon, $new_position_str, $new_status_str, $new_valid_at, $new_link);
    }

    public static function process_pc_ads_log($ads_id, $record_id, $username, $action, $old, $new)
    {
        $listen_fields = ['status', 'url_config'];
        if (!self::check_notify($action, $old, $new, $listen_fields)) {
            return;
        }

        $type = 'PC-固定位广告 ID: #' . $ads_id;
        $action_str = self::ACTION_TIPS[$action];
        $old_title = $old['title'] ?? '';
        $new_title = $new['title'] ?? '';
        $old_icon = $old['img_url'] ?? '';
        $new_icon = $new['img_url'] ?? '';
        $old_status = $old['status'] ?? '';
        $old_status_str = AdsModel::STATUS[$old_status] ?? '';
        $new_status = $new['status'] ?? '';
        $new_status_str = AdsModel::STATUS[$new_status] ?? '';
        $old_st_at = $old['start_at'] ?? '';
        $old_sp_at = $old['end_at'] ?? '';
        $old_valid_at = $old_st_at . ' ~ ' . $old_sp_at;
        $new_st_at = $new['start_at'] ?? '';
        $new_sp_at = $new['end_at'] ?? '';
        $new_valid_at = $new_st_at . ' ~ ' . $new_sp_at;
        $old_link = $old['url_config'] ?? '';
        $new_link = $new['url_config'] ?? '';
        $old_position = $old['position'] ?? '';
        $new_position = $new['position'] ?? '';
        $old_position_str = AdsModel::POSITION[$old_position] ?? '';
        $new_position_str = AdsModel::POSITION[$new_position] ?? '';
        $ret = self::send_log($record_id, $action, $username, $type, $action_str, $old_title, $old_icon, $old_position_str, $old_status_str, $old_valid_at, $old_link, $new_title, $new_icon, $new_position_str, $new_status_str, $new_valid_at, $new_link);
    }

    public static function process_pc_notice_log($ads_id, $record_id, $username, $action, $old, $new)
    {
        $listen_fields = ['status', 'url'];
        if (!self::check_notify($action, $old, $new, $listen_fields)) {
            return;
        }

        $type = 'PC-弹框广告 ID: #' . $ads_id;
        $action_str = self::ACTION_TIPS[$action];
        $old_title = $old['title'] ?? '';
        $new_title = $new['title'] ?? '';
        $old_icon = str_replace("__WAPIIMG__", TB_IMG_ADM_US, $old['img_url'] ?? '');
        $new_icon = str_replace("__WAPIIMG__", TB_IMG_ADM_US, $new['img_url'] ?? '');
        $old_status = $old['status'] ?? '';
        $old_status_str = NoticeModel::STATUS[$old_status] ?? '';
        $new_status = $new['status'] ?? '';
        $new_status_str = NoticeModel::STATUS[$new_status] ?? '';
        $old_st_at = $old['start_at'] ?? '';
        $old_sp_at = $old['end_at'] ?? '';
        $old_valid_at = $old_st_at . ' ~ ' . $old_sp_at;
        $new_st_at = $new['start_at'] ?? '';
        $new_sp_at = $new['end_at'] ?? '';
        $new_valid_at = $new_st_at . ' ~ ' . $new_sp_at;
        $old_link = $old['url'] ?? '';
        $new_link = $new['url'] ?? '';
        $old_position = $old['pos'] ?? '';
        $new_position = $new['pos'] ?? '';
        $old_position_str = NoticeModel::POS[$old_position] ?? '';
        $new_position_str = NoticeModel::POS[$new_position] ?? '';
        $ret = self::send_log($record_id, $action, $username, $type, $action_str, $old_title, $old_icon, $old_position_str, $old_status_str, $old_valid_at, $old_link, $new_title, $new_icon, $new_position_str, $new_status_str, $new_valid_at, $new_link);
    }

    public static function eventCall($table, $action, $model, $record_id, $username, $old, $new)
    {
        $tables = ['ads', 'notice', 'pc_ads', 'pc_notice'];
        if (!in_array($table, $tables)) {
            return;
        }
        $method = "process_{$table}_log";
        self::$method($model->id, $record_id, $username, $action, $old, $new);
        //jobs([self::class, $method], [$model->id, $record_id, $username, $action, $old, $new]);
    }
}