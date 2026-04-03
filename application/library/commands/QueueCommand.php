<?php
namespace commands;


use tools\Channel;
use tools\RedisService;

class QueueCommand
{
    public $signature = 'queue:channel';
    public $description = '上报数据';

    public function handle($argv)
    {
        while (true) {
            $data = redis()->blPop(Channel::$queueRedisKey, 10);
            if (!empty($data)) {
                $result = Channel::seedQueue($data[1]);
                if (empty($result)) {
                    redis()->rPush(Channel::$queueRedisKey, $data[1]);
                }
            }else{
                usleep(50000);
            }
        }
    }
}