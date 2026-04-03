<?php


namespace tools;


class mp4Upload
{
    public static function accept(array $data, $action = 'onLineMv')
    {
        $data['sign'] = CommonService::sign($data);
        if (in_array(ini_get('yaf.environ'), ['develop', 'test'])) {
            $data['notifyUrl'] = config('mp4.notify_url').'api.php/notify/' . $action;
        } else {
            $data['notifyUrl'] = config('mp4.notify_url').'api.php/notify/' . $action;
        }
//        $result = HttpCurl::post(config('mp4.accept'), $data);
//        $mp4accept = 'http://examine-new.xmyy8.co/queue.php';
        $mp4accept = 'http://examine.xmyy8.co/queue.php';
        $result = HttpCurl::post($mp4accept, $data);
        error_log('发起视频请求 - 审核:' .$result.', '. $mp4accept.', '.var_export($data,true) . PHP_EOL, 3, APP_PATH . '/storage/logs/log.log');
        if ($result != 'success') {
            trigger_error('审核失败-----' . print_r($result, true));
            return false;
        }
        return true;
    }

    public static function destroy(array $data)
    {
        $result = HttpCurl::post(config('mp4.destroy'), $data);
        if ($result == 'success') {
            return false;
        }
        return true;
    }
}
