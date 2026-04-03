<?php

class MyArrayObject implements IteratorAggregate, ArrayAccess, Serializable, Countable
{

    protected $storage = [];

    public function __construct($storage)
    {
        $this->storage = is_array($storage) ? $storage : [];
    }


    public function getIterator()
    {
        foreach ($this->storage as $k => $v) {
            yield $k => $v;
        }
    }

    public function __get($name)
    {
        return $this->offsetGet($name);
    }

    public function __set($name, $value)
    {
        $this->offsetSet($name , $value);
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset,$this->storage);
    }

    public function offsetGet($offset)
    {
        return $this->offsetExists($offset) ? $this->storage[$offset] : null;
    }

    public function offsetSet($offset, $value)
    {
        $this->storage[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->storage[$offset]);
    }

    public function serialize()
    {
        return serialize($this->storage);
    }

    public function unserialize($data)
    {
        $this->storage = unserialize($data);
    }

    public function count()
    {
        return count($this->storage);
    }
}