<?php
namespace service;

use MessageModel;

class MessageService{

    public function getContent($message){
        $contents = [];
        $content = [];
        switch($message['type']){
            case MessageModel::TYPE_UNLOCK:
                $content['color'] = MessageModel::COLOR_GRAY;
                $content['value'] = '解锁了你的资源';
                $contents[] =$content;
            break;
            case MessageModel::TYPE_CG_COMMENT:
                $content['color'] = MessageModel::COLOR_GRAY;
                $content['value'] = '有人回复了您的评论';
                $contents[] =$content;
            break;
            case MessageModel::TYPE_CONFIRM:
                $content['color'] = MessageModel::COLOR_GRAY;
                $content['value'] = '验证了你的资源为';
                $contents[] =$content;
                if($message['is_real'] == MessageModel::INFO_FAKE){
                    $content['color'] = MessageModel::COLOR_RED;
                    $content['value'] = '虚假信息（骗子）';
                    $contents[] = $content;
                }else{
                    $content['color'] = MessageModel::COLOR_PURPLE;
                    $content['value'] = '真实信息（获得铜钱*'.MessageModel::INFO_REAL_COIN.'）';
                    $contents[] = $content;
                }
            break;
        }
        return $contents;
    }
}