<?php

namespace Mirages;


use Utils;

class Config
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function get($key, $default = null)
    {
        if (strpos($key, '__') !== false) {
            list($base, $suffix) = explode('__', $key, 2);
            $value = $this->data[$base] ?? null;
            if ($suffix === 'isTrue') {
                return Utils::isTrue($value);
            } elseif ($suffix === 'isFalse') {
                return Utils::isFalse($value);
            } else {
                return $value;
            }
        }
        return $this->data[$key] ?? $default;
    }

    public function all()
    {
        return $this->data;
    }

    public function set($key, $value)
    {
        $this->data[$key] = $value;
    }

    public function export(): array
    {
        return $this->data;
    }
}
