<?php

namespace helper;


use Exception;

class Util
{

    public static function frequencyCall($identify, $max = 3, $ttl = 3, $backtrace = null)
    {
        if ($backtrace === null) {
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        }
        if (!isset($backtrace[1])) {
            return true;
        }
        if (!isset($backtrace[1]['file']) || !isset($backtrace[1]['line'])){
            if (isset($backtrace[0]['file']) && isset($backtrace[0]['line'])){
                array_unshift($backtrace , []);
            }
        }
        $identify = md5($backtrace[1]['file'] . ':' . $backtrace[1]['line'] . ':' . $identify);
        return self::frequency($identify, $max, $ttl);
    }


    public static function frequency($identify, $max = 1, $ttl = 1)
    {
        $redis = redis();
        $key = ':frequency:' . $identify;
        $incr = $redis->incr($key);
        if ($incr == 1) {
            $redis->expire($key, $ttl);
        }
        if ($incr > $max) {
            return false;
        }
        return true;
    }

    /**
     * @param $identify
     * @param int $limit
     * @param int $ttl
     * @param string $msg
     * @throws \Throwable
     *
     */
    public static function PanicFrequency($identify,int $limit = 2,int $ttl = 5 ,string $msg = '操作太频繁')
    {
        if (!self::frequencyCall($identify, $limit, $ttl, debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2))) {
            throw new Exception($msg, 422);
        }
    }

}