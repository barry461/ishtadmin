<?php
namespace commands;


use tools\Report;
use tools\RedisService;

class ReportCommand
{
    public $signature = 'queue:report';
    public $description = '上报集团';

    public function handle($argv)
    {
        daemonize('php:' . $this->signature);
        while (true) {
            $data = redis()->blPop(Report::$queueRedisKey, 10);

            if (!empty($data)) {
                $result = Report::seedQueue($data[1]);
                if (empty($result)) {
                    redis()->rPush(Report::$queueRedisKey, $data[1]);
                }
            }
        }
    }
}