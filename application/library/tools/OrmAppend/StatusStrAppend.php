<?php

namespace tools\OrmAppend;

/**
 * Trait UpdatedStrAppend
 * @property-read string $status_str
 *
 */
trait StatusStrAppend
{

    protected function getStatusStrAttribute()
    {
        return static::STATUS[$this->attributes['status'] ?? -1] ?? '未知';
    }

}