<?php

namespace helper;

/**
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 * nullable 允许值为null
 * call     自定义函数
 * required 不能为null,空字符串，空数组
 * regx     正则表达式
 * in       in_array
 * notin    not_in_array
 * numeric  必须是数字
 * max      如果判断了numeric，数字最大多少，没判断，内容长度多少
 * min      如果判断了numeric，数字最小多少，没判断，内容长度最少
 * enum   枚举，和in一样
 * json   数据能正常json解码
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 *
 * ```php
 *
 * $Validator = \helper\Validator::make($data, [
 *    'fuck.*' => ['required']                  //fuck数组
 *    'fuck.name' => ['required']               //fuck数组下面的name字段
 *    'title'  => 'required|max:100',           //title必传，最大长度不能大于100
 *    'type'   => 'required|numeric|max:40',    //type必传，最大值不能大于100
 *    'age'    => 'nullable|numeric|max:40',    //允许值为null
 *     // 正则示例 `正则表达式不需要写边界规则，程序会自动使用#补全`
 *    'year'   => ['required', ['regx' => '^\d{4}$']],              //不实用原子修饰符
 *    'year'   => ['required', ['regx' => ['^\d{4}$' , 'i'] ]],     //使用不区分大小写的原子修饰符
 * ]);
 * // 设置错误消息
 * $Validator->msgFail([
 *     'year'  => ['regx' => '年份格式错误'], // 在year的字段使用regx规则验证，错误时的消息
 * ]);
 * // 使用验证器获取结果，返回的结果会过滤其他值
 * //   如果有验证失败，抛出异常
 * $data = $Validator->resultOrFail();
 * // 使用验证器获取结果，返回的结果会过滤其他值
 * //   如果有验证失败，返回null
 * $data = $Validator->result();
 *
 * ```
 */
class Validator
{

    protected $data;
    protected $rules;
    protected $attributes;


    protected function __construct($data, $rules, $attributes = [])
    {
        $this->data = $data;
        $this->rules = $rules;
        $this->attributes = is_array($attributes) ? $attributes : [];
    }


    public static function make($data, $rules, $attributes = []): self
    {
        return new self($data, $rules, $attributes);
    }


    /**
     * 验证接口是否有错误，
     * @param null $error
     * @return bool 有错误返回true，没有返回false
     * @author xiongba
     * @date 2019-12-31 15:03:36
     */
    public function fail(&$error = null): bool
    {
        foreach ($this->rules as $key => $rule) {
            $value = $this->data_get($this->data, $key);
            list($result, $msg) = $this->_validateCallback($rule, $value, $this->messages[$key] ?? []);
            if ($result === false) {
                $name = $this->attributes[$key] ?? $key;
                $_value = is_array($value) ? json_encode($value) : $value;
                $error = str_replace(
                    ['{:attr}', '{:value}'],
                    [(string)$name, (string)$_value],
                    $msg);
                return true;
            }
        }
        return false;
    }

    protected function data_get($array, $key, $default = null)
    {
        if ($key === null) {
            return $array;
        }
        $key = is_array($key) ? $key : explode('.', $key);
        while (!is_null($seg = array_shift($key))) {
            if ($seg == '*') {
                return $array;
            } elseif (array_key_exists($seg, $array)) {
                $array = $array[$seg];
            } else {
                return $default;
            }
        }
        return $array;
    }

    protected function data_set(&$array, $key, $value)
    {
        $key = explode('.', $key, 2);
        if (isset($key[1]) && $key[1] != '*') {
            list($first_seg, $last_seg) = $key;
            if (!isset($array[$first_seg])) {
                $array[$first_seg] = [];
            }
            foreach ($array as &$inner) {
                $inner = data_set($inner, $last_seg, $value);
            }
        } else {
            $key = array_shift($key);
            $array[$key] = $value;
        }
        return $array;
    }

    protected $messages = [];

    public function msgFail(array $msgs): void
    {
        $this->messages = $msgs;
    }


    protected function parseRule($rule): array
    {
        $tmpAry = (array)$rule;
        $ruleArgs = [];
        foreach ($tmpAry as $valid => $item) {
            if (is_int($valid)) {
                if (is_string($item)) {
                    $item = explode('|', $item);
                }
                if (is_array($item)) {
                    $ruleArgs = array_merge($ruleArgs, $item);
                }
                continue;
            }
            $ruleArgs[$valid] = $item;
        }
        return $ruleArgs;
    }


    protected function parseRuleWithArgs($rule): array
    {
        $ruleArgs = $this->parseRule($rule);
        $ruleResult = [];

        foreach ($ruleArgs as $valid => $item) {
            if (!is_int($valid)) {
                $params = (array)$item;
            } elseif (false !== strpos($item, ':')) {
                list($valid, $params) = explode(':', $item, 2);
                $params = explode(',', $params);
            } else {
                $valid = $item;
                $params = [];
            }
            $ruleResult[$valid] = $params;
        }
        return $ruleResult;
    }


    protected function _validateCallback($rule, $value, $message): array
    {
        $ruleData = $this->parseRuleWithArgs($rule);
        $nullable = isset($ruleData['nullable']);
        if ($nullable && $value === null) {
            return [true, null];
        } elseif ($value === null) {
            return [false, "{:attr}的值没有设置"];
        } elseif (count($ruleData) === 0) {
            return [false, "{:attr}验证器没有配置规则"];
        }
        $validate = new _ValidatorFunc();
        foreach ($ruleData as $valid => $params) {
            array_unshift($params, $value);
            if (method_exists($validate, $valid)) {
                $fn = function () use ($valid, $validate) {
                    return call_user_func_array([$validate, $valid], func_get_args());
                };
            } elseif (is_callable($valid)) {
                $fn = $valid;
            } else {
                return [false, "{:attr}不支持{{$valid}}验证"];
            }
            $v = call_user_func_array($fn, $params);
            if ($v !== null) {
                $v = $message[$valid] ?? $v;
                return [false, $v];
            }
        }
        return [true, null];
    }

    public function result(): ?array
    {
        try {
            return $this->resultOrFail();
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * @throws \Exception
     */
    public function resultOrFail(): array
    {
        if ($this->fail($msg)) {
            throw new \Exception($msg);
        }
        $data = [];
        foreach ($this->rules as $key => $rule) {
            $value = $this->data_get($this->data, $key);
            $value = is_string($value) ? htmlspecialchars($value) : $value;
            $this->data_set($data, $key, $value);
        }
        return $data;
    }

}


class _ValidatorFunc
{
    protected $isNumeric = false;

    public function nullable()
    {
    }


    public function call($val, $callback)
    {
        if (false === $callback($val)) {
            return '{:attr}属性验证错误';
        }
    }

    public function list($value){
        if (!array_is_list($value)){
            return '{:attr}的格式不是一个list';
        }
    }

    public function required($value)
    {
        if (is_string($value)) {
            $value = trim($value);
        }
        if ($value === null) {
            return '{:attr}属性必传';
        } elseif (is_string($value) && strlen($value) == 0) {
            return '{:attr}属性不能为空字符串';
        } elseif (is_array($value) && count($value) == 0) {
            return '{:attr}属性不能为空数组';
        }
    }

    public function regx($value, $rule, $qualify = null)
    {
        $rule = str_replace("#", '\#', $rule);
        $pattern = "#{$rule}#" . ($qualify ? $qualify : '');
        if (!preg_match($pattern, $value)) {
            return '{:attr}regx错误';
        }
    }

    public function in($value, $data)
    {
        if (!in_array($value, $data)) {
            return sprintf('{:attr}的值可选(%s)', join(',', $data));
        }
    }

    public function notin($value, $data)
    {
        if (in_array($value, $data)) {
            return sprintf('{:attr}的值不能是(%s)', join(',', $data));
        }
    }


    public function numeric($value)
    {
        if (!is_numeric($value)) {
            return '{:attr}只能是数字';
        }
        $this->isNumeric = true;
    }

    public function integer($value){
        return $this->numeric($value);
    }

    public function max($value, $size)
    {
        if ($this->isNumeric) {
            if ($value > $size) {
                return '{:attr}的值最大为' . $size;
            }
        } else {
            if (mb_strlen($value) > $size) {
                return '{:attr}内容的长度不能大于' . $size;
            }
        }
    }

    public function min($value, $size)
    {
        if ($this->isNumeric) {
            if ($value < $size) {
                return '{:attr}的值最小为' . $size;
            }
        } else {
            if (mb_strlen($value) < $size) {
                return '{:attr}内容的长度不能小于' . $size;
            }
        }
    }


    public function enum()
    {
        $ary = func_get_args();
        $value = array_shift($ary);
        if (!in_array($value, $ary)) {
            return '{:attr}值只能为:' . join(',', $ary);
        }
    }

    public function json($value)
    {
        json_decode($value, true);
        if (json_last_error() != JSON_ERROR_NONE) {
            return '{:attr}值只能是json';
        }
    }


}