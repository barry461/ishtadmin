<?php

namespace service;

use OptionsModel;

class OptionService
{


    public function get(string $name)
    {
        $row = OptionsModel::where('name', $name)->first();
        if (!$row) return null;

        return $this->decodeValue($row['value']);
    }


    public function set(string $name, $value): bool
    {
        $encoded = $this->encodeValue($value);

        $exists = OptionsModel::where('name', $name)->first();
        if (!$exists) {
            return OptionsModel::insert(['name' => $name, 'value' => $encoded]);
        }
        $current = is_array($exists) ? ($exists['value'] ?? '') : $exists->value;
        if ((string)$current === (string)$encoded) {
            return true;
        }
        return OptionsModel::where('name', $name)->update(['value' => $encoded]);
    }


    public function delete(string $name): bool
    {
        return OptionsModel::where('name', $name)->delete();
    }


    public function getSubKey(string $name, string $key)
    {
        $data = $this->get($name);
        return is_array($data) ? ($data[$key] ?? null) : null;
    }


    public function setSubKey(string $name, string $key, $value): bool
    {
        $data = $this->get($name);
        if (!is_array($data)) {
            // 如果是原始值，转为数组
            $data = [];
        }
        $data[$key] = $value;
        return $this->set($name, $data);
    }


    public function deleteSubKey(string $name, string $key): bool
    {
        $data = $this->get($name);
        if (!is_array($data)) return false;

        unset($data[$key]);
        return $this->set($name, $data);
    }


    protected function encodeValue($value)
    {
        if (is_array($value)) {
            return serialize($value);
        }
        return (string)$value;
    }

    protected function decodeValue($value)
    {
        if ($this->isSerialized($value)) {
            // 经过修复再试一次
            $value = self::maybe_fix_serialized_string($value);
            return @unserialize($value);
        }
        if ($this->isJson($value)) {
            return json_decode($value, true);
        }
        return $value;
    }

    protected function isSerialized($value): bool
    {
        return is_string($value) && preg_match('/^a:\d+:{.*}$/s', $value);
    }

    protected function isJson($value): bool
    {
        if (!is_string($value)) return false;
        json_decode($value);
        return json_last_error() === JSON_ERROR_NONE;
    }

    public static function maybe_fix_serialized_string($data)
    {
        return preg_replace_callback(
            '!s:(\d+):"(.*?)";!s',
            function ($m) {
                // 重新按字节计算长度
                $len = strlen($m[2]);
                return 's:' . $len . ':"' . $m[2] . '";';
            },
            $data
        );
    }
}
