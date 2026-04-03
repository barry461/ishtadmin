<?php

namespace commands;


use Illuminate\Database\DetectsLostConnections;

class JobsCommand
{
    use DetectsLostConnections;
    public $signature = 'listener:jobs';
    public $description = '监听异步任务';

    public function pidFile(): string
    {
        return sys_get_temp_dir() . '/' . str_replace([':', '/'], '_', __FILE__) . '.pid';
    }

    public function handle($argv)
    {
        if ($argv == 'stop') {
            $this->stop();
        } elseif ($argv == 'restart') {
            $this->restart();
        } else {
            $this->start();
        }
    }

    public function stop()
    {
        $pidfile = $this->pidFile();
        if (!file_exists($pidfile)) {
            echo("进程已停止或没有启动\r\n");
            return ;
        }
        $pid = file_get_contents($pidfile);
        if (empty($pid)){
            echo("进程异常，请尝试手动停止\r\n");
            return ;
        }
        if (posix_kill($pid, SIGINT) === false) {
            unlink($pidfile);
            echo("进程已停止或没有启动\r\n");
            return;
        }
        echo("已成功发送[stop]信号到进程: $pid\r\n");
        echo("等待进程{$pid}停止\r\n");
        $i = 0;
        while ($i++ < 100) {
            sleep(1);
            if (!file_exists($pidfile)) {
                break;
            }
        }
        echo("已经成功处理[stop]信号\r\n");
    }

    public function restart()
    {
        $this->stop();
        $this->start();
    }

    private function processPidFile()
    {
        if (file_exists($this->pidFile())) {
            exit("进程已经启动了。请勿重复启动\r\n");
        } else {
            file_put_contents($this->pidFile(), posix_getppid());
        }
    }

    private function checkPidFile(){
        $file = $this->pidFile();
        if (!file_exists($file) || filesize($file) === 0){
            file_put_contents($this->pidFile(), posix_getppid());
        }
    }

    public function start($num = 6)
    {
        if (file_exists($this->pidFile())) {
            exit("进程已经启动了。请勿重复启动\r\n");
        }
        $processId = posix_getppid();
        echo("正在尝试启动进程，当前进程id：{$processId}\r\n");
        $this->setProcessTitle("[Main]");
        $title = cli_get_process_title();
        daemonize($title);
        $processId = posix_getppid();
        echo("master-pid: {$processId}\r\n");
        $this->processPidFile();
        $this->setProcessTitle("[Master]");
        $queue = 'jobs:work:queue';
        while (true) {
            $this->monitor(1, function () {
                $this->setProcessTitle("[crontab Master]");
                $this->crontabProcess();
            });
            $this->monitor($num, function () use ($queue) {
                $this->setProcessTitle("[queue worker]");
                $this->queueProcess($queue);
            });
            $this->monitor(1, function () use ($queue) {
                $this->setProcessTitle("[delay worker]");
                $this->delayProcess($queue);
            });
            pcntl_signal_dispatch();
            usleep(500000);
            $this->checkPidFile();
        }
    }

    protected function monitor($num , \Closure $startCommand){
        static $pidData = [];
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS , 2);
        $id = $trace[0]['line'];
        if (!isset($pidData[$id])){
            $pidData[$id] = [];
        }
        if ($num < 1){
            return;
        }
        $pidAry = $pidData[$id];
        for ($i = 0; $i < $num; $i++) {
            if (isset($pidAry[$i])) {
                $res = pcntl_waitpid($pidAry[$i], $status, WNOHANG);
                if ($res == -1 || $res > 0) {
                    unset($pidAry[$i]);
                }
            }
            if (!isset($pidAry[$i])){
                $processId = $this->fork(function () use ($startCommand) {
                    $this->registerSignal(0);
                    call_user_func($startCommand);
                });
                if (!empty($processId)) {
                    $pidAry[$i] = $processId;
                }
            }
        }
        $pidData[$id] = $pidAry;
        $allPid = [];
        foreach ($pidData as $pidAry){
            $allPid = array_merge($allPid , $pidAry);
        }
        $this->registerSignal($allPid);
    }

    protected function registerSignal($pid)
    {
        pcntl_signal(SIGINT, function ($signo) use ($pid) {
            if ($signo == SIGINT) {
                if ($pid) {
                    $pids = (array)$pid;
                    foreach ($pids as $pid) {
                        posix_kill($pid, SIGINT);
                    }
                    unlink($this->pidFile());
                }
                exit(0);
            }
        });
    }



    protected function fork(\Closure $subProcess)
    {
        $pid = pcntl_fork();
        if ($pid < 0) {
            return false;
        } elseif ($pid > 0) {
            return $pid;
        }
        $subProcess();
        exit(0);
    }

    protected function setProcessTitle($subtitle){
        $title = "php " . join(' ' , $_SERVER['argv']);
        cli_set_process_title("$title $subtitle");
    }

    protected function queueProcess($queue ){
        $rowBuffer = false;
        $i = 1;
        $this->loop(function () use (&$i, &$rowBuffer, $queue) {
            try {
                $buffer = $rowBuffer = redis()->lPop($queue);
                if ($buffer === false) {
                    usleep(1024 * ($i >= 1000 ? 1000 : $i *= 10));
                    return;
                }
                if (!is_string($buffer)){
                    trigger_log("buffffff -> " . var_export($buffer , 1));
                    return;
                }
                list($buffer) = json_decode($buffer , true);
                $i = 1;
                $ary = @unserialize($buffer);
                if (empty($ary)) {
                    trigger_log("queue:jobs:" . $buffer);
                    return;
                }
                list($process, $args) = $ary;
                if (is_callable($process)) {
                    call_user_func_array($process, $args);
                }
            } catch (\Throwable $e) {
                if ($this->causedByLostConnection($e)) {
                    if (is_string($rowBuffer)) {
                        redis()->rPush($queue, $rowBuffer);
                    }
                    trigger_log("Jobs: 数据链接丢失，进程退出");
                    exit(0);
                }
                trigger_log($e->getMessage());
                if (isset($buffer)) {
                    trigger_log($buffer);
                }
            }
        });
    }

    protected function delayProcess($queue)
    {
        $delay = $queue.':delay';
        $procKey = $queue.':proc';
        $this->loop(function () use ($queue, $delay, $procKey) {
            try {
                $processAry = redis()->zRangeByScore($delay, 0, time());
                foreach ($processAry as $item) {
                    redis()->zRem($delay, $item);
                    if (is_numeric($item)) {
                        $item = redis()->hGet($procKey, $item);
                        if (empty($item)) {
                            continue;
                        }
                        redis()->hDel($procKey, $item);
                    }
                    redis()->lPush($queue, $item);
                }
                usleep(400000);
            } catch (\Throwable $e) {
            }
        });
    }

    private function printCrontabTable($tasks){
        $max = 0;
        foreach ($tasks as $task) {
            list($crontab) = $task;
            $max = max($max , strlen($crontab));
        }
        $max += 1;
        foreach ($tasks as $task) {
            list($crontab, , $name) = $task;
            echo $crontab , str_repeat(' ' , $max-strlen($crontab)) , ' ' , $name ,"\r\n";
        }
    }

    protected function crontabProcess(){
        $user = get_current_user();
        if (empty($user)){
            $user = posix_getuid();
        }
        $tasks = array_merge(
            $this->scanCrontabDir(),
            $this->scanCrontabFile()
        );
        global $STDOUT;
        fclose(STDOUT);
        $STDOUT = fopen(APP_PATH.'/storage/logs/cron-log.log', 'a+b');
        $tpl = "%s CROND[%d]: (%s) RUN (jobs will be run at task.)\n";
        echo sprintf($tpl, date('M j H:i:s'), getmypid(), $user);
        $this->printCrontabTable($tasks);
        $subProcess = [];
        $this->loop(function () use ($tasks, $user , &$subProcess) {
            foreach ($subProcess as $k => $process) {
                $res = pcntl_waitpid($process, $status, WNOHANG);
                if ($res == -1 || $res > 0) {
                    unset($subProcess[$k]);
                }
            }
            usleep(500000);
            if (date('s') != 1) {
                return;
            }
            foreach ($tasks as $task) {
                list($crontab, $call, $name) = $task;
                if (!$this->matchCrontab(time(), $crontab)) {
                    continue;
                }
                $subProcess[] = $this->fork(function () use ($call, $name, $user) {
                    $this->setProcessTitle("[crond worker]");
                    $tpl = "%s CROND[%d]: (%s) CMD ($name)\n";
                    echo sprintf($tpl, date('M j H:i:s'), getmypid(), $user);
                    usleep(100000);
                    call_user_func($call);
                });
            }
            sleep(2);
        });
    }


    protected function loop(callable $callable){
        while (true){
            pcntl_signal_dispatch();
            call_user_func($callable);
        }
    }

    protected function findCrontab($str){
        if (empty($str)){
            return  null;
        }
        if (!preg_match("#@crontab(?:[^\(]*)\(([^\)]+)\)\s*#" , $str , $arr)){
            return null;
        }
        list(  , $v) = $arr;
        $v = trim($v);
        $v = \preg_replace('/([\#\t\'"]+)/m', '', $v);
        $v = \preg_replace('/(\s{2,})/m', ' ', $v);
        $v = str_replace(["\/", "//"] ,'/' , $v);
        return str_replace(["\r", "\n"] ,'' , $v);
    }

    protected function scanCrontabDir(): array
    {
        $dir = APP_PATH .'/application/library/crontab/*.php';
        $ary = glob($dir);
        $crontab = [];
        foreach ($ary as $file) {
            $code = file_get_contents($file);
            $tokens = token_get_all($code);
            foreach ($tokens as $token) {
                if (is_array($token) && $token[0] == T_DOC_COMMENT) {
                    $tick = $this->findCrontab($token[1]);
                    $class = "\\crontab\\"  . substr(basename($file) ,0 , -4);
                    if (class_exists($class)){
                        $baseClazz = class_basename($class);
                        $crontab[] = [$tick , [new $class , 'main'] , "{$baseClazz}::main"];
                    }
                    break;
                }
            }
        }
        return $crontab;
    }

    protected function scanCrontabFile(): array
    {
        $file = APP_PATH .'/application/modules/Script/controllers/Cron.php';
        $crontab = [];
        if (file_exists($file)){
            require $file;
            $clazz = \CronController::class;
            if (!class_exists($clazz)){
                return [];
            }
            $ref = new \ReflectionClass($clazz);
            $methods = $ref->getMethods(\ReflectionMethod::IS_PUBLIC);
            $object = $ref->newInstanceWithoutConstructor();
            foreach ($methods as $method){
                $name = $method->getName();
                $tick = $this->findCrontab($method->getDocComment());
                if ($name == 'init'){
                    $method->setAccessible(true);
                    $method->invoke($object);
                }elseif ($tick){
                    $crontab[] = [$tick , [$object , $name ],$clazz . "::" . $name];
                }
            }
        }
        return $crontab;
    }

    protected function matchCrontab($t1, $crontab)
    {
        $t1 = is_numeric($t1) ? $t1 : strtotime($t1);
        $time = explode(' ', date('i G j n w', $t1));
        $crontab = explode(' ', $crontab);
        foreach ($crontab as $k => &$v) {
            $time[$k] = intval($time[$k]);
            $v = explode(',', $v);
            foreach ($v as &$v1) {
                $v1 = preg_replace(
                    ['/^\*$/', '/^\d+$/', '/^(\d+)\-(\d+)$/', '/^\*\/(\d+)$/', ],
                    [ 'true', $time[$k].'===\0', '(\1<='.$time[$k].' and '.$time[$k].'<=\2)', $time[$k].'%\1===0', ],
                    $v1
                );
            }
            $v = '('.implode(' or ', $v).')';
        }
        $crontab = implode(' and ', $crontab);
        return eval('return '.$crontab.';');
    }


}