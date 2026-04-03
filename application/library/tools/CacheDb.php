<?php

namespace tools;


use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Class CacheDb
 */
class CacheDb
{

    /**
     * @var \RedisCluster
     */
    private $driver;

    const TYPE_STRING = 1;
    const TYPE_HASH = 2;

    const SERIALIZER_NONE = 0;
    const SERIALIZER_PHP = 1;
    const SERIALIZER_JSON = 2;


    protected $expired = 600;
    /**
     * @var string 缓存的key
     */
    private $key;
    /**
     * @var int 使用的缓存类型
     */
    private $type = self::TYPE_STRING;
    /**
     * @var string|null 如果缓存是hash类型，需要的hashKey
     */
    private $hashKey;

    private $serializer = self::SERIALIZER_NONE;
    /**
     * @var bool 空数据是否也保存
     */
    public $saveEmpty = false;

    private $suffix = '';
    private $prefix = '';
    private $groupBy = '';
    private $search = [];
    private $replace = [];
    private $replaceSearch = [];


    /**
     * CacheDb constructor.
     *
     * @param $driver
     *
     */
    public function __construct($driver = null)
    {
        $this->driver = $driver;
    }

    /**
     * 事例一个对象
     *
     * @param $driver
     *
     * @return CacheDb
     */
    public static function make($driver): CacheDb
    {
        return new self($driver);
    }


    /**
     * 设置空数据是否也缓存
     *
     * @param bool $saveEmpty
     *
     * @return $this
     */
    public function setSaveEmpty(bool $saveEmpty): CacheDb
    {
        $this->saveEmpty = $saveEmpty;

        return $this;
    }


    /**
     * 设置过期时间
     * 参数：$expired 过期时间。
     *               可以接受一个回调函数，回调函数圆形 callback($data)
     *
     * @param int|callable $expired $expired 过期时间,如果是回调函数，会使用回调函数计算一个时间,回调函数接受一个参数，参数为
     *
     * @return $this
     */
    public function expired($expired): CacheDb
    {
        $this->expired = $expired;

        return $this;
    }

    /**
     * 选择压缩的字符串
     *
     * @param array|string $search 需要压缩的值，如果值的字符串长度小于等于10个，将没有压缩的意义
     *
     * @return $this
     */
    public function compress($search): CacheDb
    {
        $this->search = (array)$search;
        $this->replaceSearch = array_map(function ($str) {
            return str_replace('/', '\\/', addslashes($str));
        }, $this->search);
        foreach ($this->search as $k => $search) {
            $this->replace[] = '{%!A'.$k.'_^!%}';
        }

        return $this;
    }

    /**
     * 获取缓存key
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * 设置缓存的key
     *
     * @param $key
     *
     * @return $this
     */
    public function setKey($key): CacheDb
    {
        $this->key = $key;
        return $this;
    }


    /**
     * 设置或者获取 缓存类型
     *
     * @param $type
     * @param $hashKey
     *
     * @return $this
     */
    public function setType($type, $hashKey = null): CacheDb
    {
        if (!in_array($type, [self::TYPE_HASH, self::TYPE_STRING])) {
            throw new InvalidArgumentException('不支持的类型');
        }
        if ($type == self::TYPE_HASH) {
            if (empty($hashKey)) {
                throw new InvalidArgumentException('hash类型必须设置hashKey');
            }
            $this->hashKey = $hashKey;
        }
        $this->type = $type;

        return $this;
    }


    public function hash($hashKey): CacheDb
    {
        return $this->setType(self::TYPE_HASH, $hashKey);
    }

    /**
     * 缓存处理
     *
     * @param $data
     *
     * @return mixed
     */
    public function setCache($data)
    {
        $expired = $this->expired;
        if (is_callable($expired)) {
            $expired = call_user_func($expired, $data);
        }
        if ($expired <= 1) {
            return $data;
        }
        $this->validatorKey();
        $data = $this->callSerializer($data);
        $key = $this->generateKeyname();
        switch ($this->type) {
            case self::TYPE_STRING:
                $this->driver->set($key, $data, $expired);
                break;
            case self::TYPE_HASH:
                $this->driver->hSet($key, $this->hashKey, $data);
                $this->driver->expire($key, $expired);
                break;
            default:
                throw new \InvalidArgumentException('缓存类型错误');
        }
        if ($this->groupBy) {
            $this->driver->sAdd(self::__GROUP_KEY.$this->groupBy, $key);
        }
        if ($this->_chinese) {
            try {
                if (class_exists('\CacheKeysModel')) {
                    \CacheKeysModel::adder($this->_chinese, $this);
                }
            } catch (\Throwable $e) {
            }
        }

        return $data;
    }


    /**
     * 执行查询
     *
     * @param callable $fetchFn 查询的回调方法
     * @param array $args 回调方法的参数
     * @param bool $refreshCache 是否刷新缓存
     *
     * @return mixed
     */
    public function fetch(
        callable $fetchFn,
        array $args = [],
        bool $refreshCache = false,
        $num = 1
    ) {
        if (!$refreshCache) {
            $data = $this->getRawCache();
            if ($data !== false) {
                return $this->callUnSerializer($data);
            }
        }
        // 避免缓存穿透
//        $lockName = $this->generateKeyname().':snx';
//        if ($this->driver->setnx($lockName, 1)) {
//            $this->driver->expire($lockName, 10);
//        } elseif ($num < 20) {
//            usleep(20000);
//            return $this->fetch($fetchFn, $args, false, ++$num);
//        }

        $args[] = $this;
        $data = call_user_func_array($fetchFn, $args);

        //$this->driver->del($lockName);
        if ($this->saveEmpty || !$this->isEmpty($data)) {
            $this->setCache($data);
        }
        return $data;
    }

    /**
     * 判断数据是否是空的
     * $data === null || $data === false 返回真
     * count($data) == 0 返回真
     * $data->count() == 0 返回真
     * $data->isEmpty() === true 返回真
     *
     * @param $data
     * @return bool
     */
    private function isEmpty($data): bool
    {
        if ($data === null || $data === false) {
            return true;
        }
        if (is_array($data) && empty($data)) {
            return true;
        }
        if (is_object($data)) {
            if ($data instanceof \Countable){
                return $data->count() == 0;
            }
            if (method_exists($data, 'isEmpty') && $data->isEmpty()) {
                return true;
            }
        }
        return false;
    }



    private function callSerializer($data)
    {
        if ($this->serializer === self::SERIALIZER_PHP) {
            return $this->replace(serialize($data));
        } elseif ($this->serializer === self::SERIALIZER_JSON) {
            return $this->replace(json_encode($data));
        } else {
            return $this->replace($data);
        }
    }

    private function replace($data)
    {
        $data = str_replace($this->replaceSearch, $this->replace, $data);
        if (!function_exists('gzcompress')) {
            return $data;
        }
        if (isset($data[20480])) {
            return pack('nnn', 122, 222, 231).gzcompress($data);
        }
        return pack('nnn', 1, 0, 0).$data;
    }

    private function unreplace($data)
    {
        $data = str_replace($this->replace, $this->search, $data);
        if (!function_exists('gzuncompress')) {
            return $data;
        }
        $verify = unpack('na/nb/nc', $data);
        $buffer = substr($data, 6);
        if ($verify['a'] == 122 && $verify['b'] == 222 && $verify['c'] == 231) {
            return gzuncompress($buffer);
        } elseif ($verify['a'] == 1 && $verify['b'] == 0 && $verify['c'] == 0) {
            return $buffer;
        } else {
            return $data;
        }
    }

    private function callUnSerializer($data)
    {
        $cals = [
            self::SERIALIZER_PHP => function ($d) {
                return @unserialize($d);
            },
            self::SERIALIZER_JSON => function ($d) {
                return @json_decode($d, 1);
            },
        ];

        if (isset($cals[$this->serializer])) {
            return call_user_func(
                $cals[$this->serializer],
                $this->unreplace($data)
            );
        }

        return $this->unreplace($data);
    }

    protected function getRawCache()
    {
        $this->validatorKey();
        $key = $this->generateKeyname();
        switch ($this->type) {
            case self::TYPE_STRING:
                return $this->driver->get($key);
            case self::TYPE_HASH:
                return $this->driver->hGet($key, $this->hashKey);
            default:
                throw new \InvalidArgumentException('缓存类型错误');
        }
    }

    /**
     * @return mixed
     */
    public function getCache()
    {
        $data = $this->getRawCache();
        if ($data !== false) {
            return $this->callUnSerializer($data);
        }
        return null;
    }


    /**
     * 清除缓存
     * @return $this
     */
    public function clearCached(): CacheDb
    {
        $this->validatorKey();
        $key = $this->generateKeyname();
        switch ($this->type) {
            case self::TYPE_STRING:
                $this->driver->del($key);
                break;
            case self::TYPE_HASH:
                $this->driver->hDel($key, $this->hashKey);
                break;
            default:
                throw new \InvalidArgumentException('缓存类型错误');
        }

        return $this;
    }


    /**
     * 验证key
     *
     * @date 2019-12-13 17:26:15
     */
    private function validatorKey()
    {
        if (empty($this->key)) {
            throw new \InvalidArgumentException('请设置缓存key');
        }
        if ($this->type == self::TYPE_HASH && empty($this->hashKey)) {
            throw new \InvalidArgumentException('hash缓存请设置hasKey');
        }
    }

    /**
     * @return int
     */
    public function getSerializer(): int
    {
        return $this->serializer;
    }

    /**
     * @param int $serializer
     *
     * @return $this
     * @date 2019-12-13 17:47:01
     */
    public function serializer(int $serializer): CacheDb
    {
        $this->serializer = $serializer;

        return $this;
    }

    public function serializerPHP(): CacheDb
    {
        return $this->serializer(self::SERIALIZER_PHP);
    }

    public function serializerJSON(): CacheDb
    {
        return $this->serializer(self::SERIALIZER_JSON);
    }

    /**
     * @param string $suffix
     *
     * @return CacheDb
     */
    public function suffix(string $suffix): CacheDb
    {
        $this->suffix = $suffix;

        return $this;
    }

    public function prefix(string $s): CacheDb
    {
        $this->prefix = $s;
        return $this;
    }

    public function generateKeyname(): string
    {
        return $this->prefix.$this->key.$this->suffix;
    }

    /**
     * @param $cb
     * @param int $expired
     *
     * @return mixed|array
     */
    public function fetchJson($cb, int $expired = 3600)
    {
        return $this->serializerJSON()
            ->setSaveEmpty(true)
            ->expired($expired)
            ->fetch($cb);
    }

    /**
     * @param $cb
     * @param int $expired
     *
     * @return mixed|Collection
     */
    public function fetchPhp($cb, int $expired = 3600)
    {
        return $this
            ->setSaveEmpty(true)
            ->serializerPHP()
            ->expired($expired)
            ->fetch($cb);
    }

    /**
     * 将缓存进行分组
     *
     * @param string $group
     *
     * @return $this
     *
     * ```php
     * cached('page:' . $page)->group('group1')->fetch(function(){return 111;});
     * ```
     */
    public function group(string $group): CacheDb
    {
        $this->groupBy = $group;
        return $this;
    }

    protected $_chinese;

    /**
     * 使用中文分组，清理之后在后台清理
     * @param string $chineseGroup
     *
     * @return $this
     */
    public function chinese(string $chineseGroup): CacheDb
    {
        $this->_chinese = $chineseGroup;
        return $this;
    }

    /**
     * 清理分组的缓存
     *
     * @param ...$groups
     *
     * ```php
     * cached('')->clearGroup('group1' ,'group2')
     * ```
     */
    public function clearGroup(...$groups)
    {
        if (empty($this->key)) {
            $this->key = '';
        }
        $groups[] = $this->key;
        foreach ($groups as $group) {
            if (empty($group)) {
                continue;
            }
            $ary = $this->driver->sMembers(self::__GROUP_KEY . $group);
            foreach ($ary as $key) {
                $this->driver->expire($key , 3);
            }
            $this->driver->del(self::__GROUP_KEY . $group);
        }
    }

    const __GROUP_KEY = '_kg_';

}