<?php

/**
 * html静态化 缓存插件配置
 * 注意事项:
 * 依赖: `yac`, `nginx的x-accel-redirect`
 * 除了php自带的类，不要引入任何第三方类
 * string[] force_list 强制缓存， 忽略白名单，忽略用户态
 * string[] whitelist 白名单, 不走缓存
 * callback user_bypass 用户态模式, 用户登陆的时候。不应该进行缓存
 */

return [
    'ttl'          => 300,
    'max_ttl'      => 600,
    'enable'       => true,
    'enable_query' => true,
    'force_list'   => [],
    'whitelist'    => [
        '/404/',
        '/feed'  // 跳过feed页面的HTML缓存，确保410状态码正常返回
    ],
    'user_bypass'  => function () {
        return false;
    },
];