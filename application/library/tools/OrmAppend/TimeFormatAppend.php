<?php

namespace tools\OrmAppend;


/**
 *
 * @property-read string $time_format
 *
 */
trait TimeFormatAppend
{


    public function getTimeFormatAttribute()
    {
        $timestamps = $this->attributes['created_at'] ?? time();
        return time_format($timestamps);
    }


}