<?php

/**
 * class 权限表
 *
 * @property int $id
 * @property string $role_id
 * @property string $role_name
 * @property string $role_action_ids
 *
 * @mixin \Eloquent
 */
class RoleModel extends BaseModel
{
    protected $table = "role";

    protected $primaryKey = 'role_id';

    protected $fillable = [
        'role_id',
        'role_name',
        'role_action_ids'
    ];

    public $timestamps = false;
}
