<?php

namespace tools\OrmAppend;



/**
 * Trait CreatedStrAppend
 * @property-read string $created_str
 * @property-read string $created_format
 *
 */
trait CreatedStrAppend
{


    public function getCreatedStrAttribute()
    {
        return date('Y-m-d H:i:s', $this->attributes['created_at'] ?? 0);
    }


    public function getCreatedFormatAttribute()
    {
        $timestamps = $this->attributes['created_at'] ?? time();
        return time_format($timestamps);
    }


}