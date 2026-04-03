<?php

namespace tools;

/**
 *
 * 将链试调用转换成 \Closure
 *  试验性代码，自行使用
 *
 * ```php
 * $libClosure = LibClosure::new(MvModel::query())->get();
 * var_dump($libClosure) ; //  output => LibClosure
 * echo $libClosure() ; // 可以成功返回
 *
 * $libClosure->__closure() ; //返回 \Closure
 * lib_value($libClosure) ; // 直接使用可以返回运行结果
 *
 * ```
 *
 *
 */
class LibClosure
{
    private $call = [];
    private $object;

    /**
     * @param object $object
     */
    public function __construct($object)
    {
        $this->object = $object;
    }

    public static function new($object): LibClosure
    {
        return new LibClosure($object);
    }

    public function __call($name, $arguments)
    {
        $this->call[] = ['__call', $name, $arguments];
        return $this;
    }

    public function __get($name)
    {
        $this->call[] = ['__get', $name, null];
        return $this;
    }

    public function __invoke(...$args)
    {
        return call_user_func($this->__closure(), ...$args);
    }

    public function __closure(): \Closure
    {
        return function () {
            $return = $this->object;
            foreach ($this->call as $call) {
                list($method, $name, $args) = $call;
                if ($method === '__call') {
                    $return = call_user_func_array([$return, $name], $args);
                } elseif ($method === '__get') {
                    $return = $return->{$name};
                }
            }
            return $return;
        };
    }


}