<?php

namespace tools\OrmAppend;

/**
 * Trait UpdatedStrAppend
 *
 * @property-read string $updated_str
 * @property-read string $update_str
 * @property-read string $updated_format
 * @property-read string $update_format
 *
 */
trait UpdatedStrAppend
{


    public function getUpdatedStrAttribute()
    {
        return date('Y-m-d H:i:s', $this->attributes['updated_at'] ?? 0);
    }

    public function getUpdateStrAttribute()
    {
        return date('Y-m-d H:i:s', $this->attributes['update_at'] ?? 0);
    }

    public function getUpdatedFormatAttribute(): string
    {
        $timestamps = $this->attributes['updated_at'] ?? time();

        return time_format($timestamps);
    }

    public function getUpdateFormatAttribute(): string
    {
        $timestamps = $this->attributes['update_at'] ?? time();

        return time_format($timestamps);
    }

}