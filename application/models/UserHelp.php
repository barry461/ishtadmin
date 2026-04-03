<?php


class UserHelpModel extends BaseModel
{
    protected $table = 'user_help';

    const REDIS_USER_HELP_LIST = 'user:help:list';
    const REDIS_USER_HELP_GW_LIST = 'gw:user:help:list';

    const REDIS_USER_HELP_DETAIL = 'user:help:detail:';

    const USER_HELP_TYPE = [
        1 => '热点问题',
        2 => '加载缓存',
        3 => '个人账户',
        4 => '资源内容',
        5 => '分享推广',
        6 => '其他问题'
    ];
    protected $fillable = [
        'question',
        'answer',
        'status',
        'type',
        'views',
        'created_at',
        'updated_at'
    ];

    const STATUS_NO = 0;
    const STATUS_YES = 1;
    const STATUS = [
      self::STATUS_NO => '否',
      self::STATUS_YES => '是',
    ];

}