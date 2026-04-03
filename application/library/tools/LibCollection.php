<?php

namespace tools;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class LibCollection extends \Illuminate\Database\Eloquent\Collection
    //implements \Serializable
{

    /**
     * @param array|static $idx
     * @param string $key_index
     * @return self|static|object
     */
    public function sortByIdx($idx, string $key_index = 'id')
    {
        $object = new static();
        $ary = $this->keyBy($key_index);
        foreach ($idx as $id) {
            if (isset($ary[$id])) {
                $object->push($ary[$id]);
            }
        }
        return $object;
    }

    /**
     * 使用字段比交集
     * @param static|array $items
     * @param ?mixed $selfColumn
     * @param string|null $itemColumn
     * @return self|static|object
     */
    public function intersectUseColumn($items, $selfColumn, ?string $itemColumn = null)
    {
        $items = new Collection($items);
        if (null !== $itemColumn) {
            $items = $items->pluck($itemColumn);
        }
        if ($selfColumn === null) {
            $newItems = new Collection($this->items);
            return new static($newItems->intersect($items));
        }
        $newThis = new static();
        foreach ($this as $item) {
            if ($item instanceof Model){
                $temp = $item->getAttributes();
                $ary = array_merge($item->toArray() , $temp);
            }else{
                $ary = $this->getArrayableItems($item);
            }
            if (isset($ary[$selfColumn]) && $items->contains($ary[$selfColumn])) {
                $newThis->push($item);
            }
        }
        return $newThis;
    }



    public function serialize(): string
    {
        if (empty($this->items)) {
            return json_encode([]);
        }
        /** @var \BaseModel $item */
        $item = current($this->items);
        $class = get_class($item);
        $fields = array_keys($item->getAttributes());
        $values = [];
        $header = [];
        $vValue = [];
        $joinModels = [];
        foreach ($this->items as $k => $item) {
            $vValue[$k] = [];
            $values[] = array_values($item->getAttributes());
            $relations = $item->getRelations();
            $joinModels[] = serialize($item->getJoinModels());
            foreach ($relations as $name => $relation) {
                if (!isset($header[$name])) {
                    $header[$name] = [null, null];
                }
                if ($relation) {
                    $header[$name][0] = get_class($relation);
                }
                if ($relation instanceof Model) {
                    $relation = $relation->getAttributes();
                    $header[$name][1] = array_keys($relation);
                }
                if ($relation === null) {
                    $vValue[$k][$name] = null;
                } else {
                    $vValue[$k][$name] = is_array($relation) ? array_values($relation) : serialize($relation);
                }
            }
        }

        return json_encode([$class, $fields, $values, $header, $vValue, $joinModels], JSON_UNESCAPED_UNICODE);
    }

    public function unserialize($data)
    {
        $decode = json_decode($data, true);
        if (empty($decode)) {
            return $this;
        }
        list($class, $fields, $values, $header, $vValue, $joinModels) = $decode;
        /** @var \BaseModel $object */
        $object = $class::make();
        $object->exists = true;
        $items = [];
        foreach ($values as $_k => $value) {
            $item = clone $object;
            $item->setRawAttributes(array_combine($fields, $value), true);
            if (!empty($joinModels[$_k])) {
                $models = unserialize($joinModels[$_k]);
                if (!empty($models)) {
                    $item->joinModel(...$models);
                }
            }
            foreach ($header as $name => $_values) {
                $relation = $vValue[$_k][$name];
                if (is_array($relation)) {
                    list($_class, $_fields) = $_values;
                    if (!is_array($_fields)) {
                        $_fields = $_values;
                        $_class = false;
                    }
                    $relation = array_combine($_fields, $relation);
                    if ($_class) {
                        $_object = $_class::make();
                        $_object->setRawAttributes($relation, true);
                        $_object->exists = true;
                        $relation = $_object;
                    }
                } elseif (is_string($relation)) {
                    $relation = unserialize($relation);
                }
                $item->setRelation($name, $relation);
            }
            $items[] = $item;
        }
        $data = $object->newCollection($items);
        $this->items = $data->items;
        unset($data->items, $data);
    }

}