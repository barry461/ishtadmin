<?php

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $app_id
 * @property string $info_id
 * @property string $created_at
 * @property string $updated_at
 */
class InfoSyncLogModel extends BaseModel
{
    protected $table = 'info_sync_log';

    protected $fillable = [
        'id',
        'app_id',
        'info_id',
        'created_at',
        'updated_at'
    ];

    protected $primaryKey = 'id';
}
