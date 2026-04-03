<?php
namespace service;
use tools\CurlService;
use service\UserService;

/**
 *
 * @copyright
 * @todo 工单系统远程处理 ，调用逻辑控制
 * @todo  https://showdoc.hyys.info/web/#/5?page_id=3761  doc
 *
 *
 */


/**
 * Class AppFeedSystemService
 * @package service
 */
class AppFeedSystemService
{
    //https://aff.peach-av.com/index.php?m=feedsync&a=index
    const SIGN_KEY = '132f1537f85scxpcm59f7e318b9epatk';//签名key
    const ENCRYPT_KEY = 'e79465cfbb39ckcusimcuekd3b066atk';//加密key
    const API_APP = 'https://tickets.hyys.me/api/index/seed';//工单系统
    const SHOW_SUCCESS = 'success';
    const SHOW_FAIL = 'fail';

    /**
     * 定义要使用的加密类
     *
     * @return \LibCrypt
     */
    public function crypt()
    {
        $crypt = new \LibCrypt();
        $crypt->setKey(config('ticket.sign_key') , config('ticket.encrypt_key'));
        return $crypt;
    }

    /**
     *  定义工单通讯远程请求 (暂只对工单通讯系统有用)
     *
     * @param null $url
     * @param array $postData
     * @return bool
     */
    public function sendRemoteRequest($url = null, array $postData = []): bool
    {
        if (!$url) {
            $url = config('ticket.url');
        }
        try {
            $data = $this->crypt()->replyData($postData);
            $curl = new \tools\HttpCurl();
            $result = $curl->post($url, json_decode($data, true));
            if ($result == 'success') {
                return true;
            }
        } catch (\Exception $e) {
            trigger_error("sendRemoteRequestError: \r\n " .  $e->getMessage());
        }
        return false;
    }
}