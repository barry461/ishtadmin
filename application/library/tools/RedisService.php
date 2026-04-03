<?php

namespace tools;

use Closure;
use Redis;
use RedisCluster;
use RedisClusterException;
use RedisException;
use Yaf\Registry;

use function PHPUnit\Framework\callback;

/**
 * Class RedisService
 * @package tools
 * @mixin \Redis
 */
class RedisService
{

    private static $instance = null;
    private $hosts = null;
    private $prefix = '';
    private $isConnect = false;
    /**
     * @var RedisCluster
     */
    private $client;

    public function __construct(array $hosts, $prefix)
    {
        $this->hosts = $hosts;
        $this->prefix = $prefix;
    }


    public static function instance()
    {
        $config = Registry::get('redis');
        if (self::$instance === null) {
            $host = $config->get('host');
            $hosts = explode(',', $host);
            $prefix = $config->get('prefix');
            self::$instance = new self($hosts, $prefix);
        }
        return self::$instance;
    }

    public static function __callStatic($name, $arguments)
    {
        return call_user_func_array([self::instance(), $name], $arguments);
    }

    public function rPush($key, $value)
    {
        if ($value[0] == ':' && strlen($value) < 10) {
            trigger_log(debug_backtrace(), 5);
        }
        return $this->getRedis()->rPush($key, $value);
    }

    public function incrByTtl($key , $value , $ttl){
        try {
            $redis = $this->getRedis();
            $val = $redis->incrBy($key , $value);
            if ($val <= 3){
                $redis->expire($key , $ttl);
            }
            return $val;
        } catch (\Throwable $e) {
            return 0;
        }
    }


    /**
     * @return RedisCluster|Redis
     * @throws RedisClusterException
     * @throws RedisException
     * @date 2019-11-28 11:03:16
     */
    public function getRedis()
    {
        if (!$this->isConnect) {
            if (empty($this->hosts)) {
                throw new \RedisException('redis配置不正确');
            }
            if (count($this->hosts) > 1) {
                $this->client = new \RedisCluster(null, $this->hosts);
                $this->client->setOption(\RedisCluster::OPT_SLAVE_FAILOVER, \RedisCluster::FAILOVER_DISTRIBUTE);
            } else {
                $this->client = $this->getSingleRedis($this->hosts[0]);
            }
            $this->client->setOption(\Redis::OPT_PREFIX, $this->prefix);
            //$this->client->auth('foobared');
            $this->isConnect = true;
        }
        return $this->client;
    }


    /**
     * 链接单例redis
     * @param $hostString
     * @return Redis
     * @throws RedisException
     * @date 2019-12-12 16:42:55
     */
    protected function getSingleRedis($hostString): Redis
    {
        $client = new \Redis();
        $ary = explode(':', $hostString);
        if (!$client->connect(...$ary)) {
            throw new \RedisException("Redis:{{{$hostString}}}链接失败");
        }
        return $client;
    }


    /**
     * @param $name
     * @param $arguments
     * @return mixed
     * @date 2019-12-12 16:38:53
     */
    public function __call($name, $arguments)
    {
        return $this->getRedis()->{$name}(...$arguments);
    }

    public function scanRaw(&$iterator, $pattern = null, $count = 0)
    {
        try {
            $redis = $this->getRedis();
            return $redis->scan($iterator, $pattern, $count);
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function scan(&$iterator, $pattern = null, $count = 0): array
    {
        try {
            $redis = $this->getRedis();
            $prefix = $redis->getOption(2);
            $len = strlen($prefix);
            if (strpos($pattern, $prefix) !== 0) {
                $pattern = $prefix . $pattern;
            }
            $keys = $this->scanRaw($iterator , $pattern , $count);
            return array_map(function ($v) use ($len){  return substr($v , $len); } , $keys);
        } catch (\Throwable $e) {
            return [];
        }
    }


    public function close(){

        if ($this->isConnect) {
            try {
                $redis = $this->getRedis();
                if ($redis instanceof \RedisCluster) {
                    $redis->close();
                } elseif ($redis instanceof \Redis) {
                    $redis->close();
                }
                unset($this->client);
            } catch (\Throwable $e) {
            }
            $this->client = null;
            $this->isConnect = false;
        }
    }


    public function __destruct()
    {
        $this->close();
    }

    public function getWithSerialize($key)
    {
        $data = $this->getRedis()->get($key);
        if (is_null($data)) {
            return false;
        }
        return unserialize($data);
    }

    public function setWithSerialize($key, $val, $time = false)
    {
        $this->getRedis()->set($key, serialize($val));
        if ($time) {
            $this->getRedis()->expire($key, $time);
        }
        return true;
    }


    public function scanAll($pattern): array
    {
        try {
            $it = NULL;
            $keys = [];
            do {
                $list = redis()->scan($it, $pattern, 10000);
                if (isset($list[0])) {
                    $keys = array_merge($keys, $list);
                }
                if (empty($it)) {
                    break;
                }
            } while (is_array($list));
            return $keys;
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function bulkDel($pattern)
    {
        return ;
        $keys = $this->scanAll($pattern);
        if (!empty($keys)){
            $this->del(...$keys);
        }
    }

    /**
     * 深度缓存，用二级缓存防止某一key在失效的大量db查询
     * @param string $key
     * @param Closure|LibClosure|mixed $closure
     * @param int $ttl
     * @return mixed|null
     */
    public function getx(string $key, $closure,int $ttl)
    {
        try {
            $redis = $this->getRedis();
        } catch (\Throwable $e) {
            trigger_log($e);
            return null;
        }
        $data = $redis->get($key);
        if (empty($data)) {
            $key1 = $key . ':deep';
            $lockKey = $key . ':lock';
            if ($this->setnx($lockKey, 1)) {
                $redis->expire($lockKey, 300);
                $data = lib_value($closure);
                $redis->set($key, $data1 = serialize($data), $ttl);
                $redis->set($key1, $data1, $ttl + 1800);
                $redis->del($lockKey);
                return $data;
            } elseif (!$redis->exists($key1)) {
                return lib_value($closure);
            } else {
                $data = $redis->get($key1);
            }
        }
        return unserialize($data);
    }

    public function setnxttl($key, $value, int $ttl): bool
    {
        $redis = $this->getRedis();
        if ($redis->setnx($key, $value)) {
            $redis->expire($key, $ttl);
            return true;
        }
        return false;
    }


    public function connect()
    {
        return true;
    }


    public function auth()
    {
        return true;
    }

    public function lock($lockName , Closure $param , $ttl = 60)
    {
        try {
            if (!redis()->setnx($lockName , 1)){
                return false;
            }
            redis()->expire($lockName , $ttl);
            $result = $param();
            redis()->del($lockName);
            return  $result;
        }catch (\Throwable $e){
            throw new \RuntimeException($e->getMessage() , $e->getCode());
        }
    }

    public function sChunk($key, $size, Closure $cb)
    {
        try {
            $iterator = null;
            $redis = $this->getRedis();
            do {
                $rows = $redis->sScan($key, $iterator, null, $size);
                if ($cb($rows) === false) {
                    return;
                }
            } while (!empty($iterator));
        }catch (\Throwable $e){

        }
    }

    public function sAddTtl($key, $member, $ttl)
    {
        try {
            $redis = $this->getRedis();
            $result = $redis->sAdd($key , $member);
            if ($result !== 0 && $redis->ttl($key) == -1){
                $redis->expire($key , $ttl);
            }
            return $result;
        } catch (\Throwable $e) {
            trigger_log($e);
            return false;
        }
    }

}