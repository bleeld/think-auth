<?php

return [
    'api'   =>  [
        //认证数据库组
        // 权限开关
        'auth_on'                            =>  1,
        // 认证方式，1为实时认证；2为登录认证。
        'auth_type'                          =>  1,
        // 用户组数据表名   用户组数据不带前缀表名
        'auth_group'                         =>  'auth_group',
        // 用户-用户组关系表
        'auth_group_access'                  =>  'auth_group_access',
        // 权限规则表
        'auth_rule'                          =>  'auth_rule',
        // 用户信息表
        'auth_user'                          =>  'user',
        // 用户信息存贮介质，这里仅支持cache/session
        'auth_driver'                        => 'cache', 
        // 用户表ID字段名
        'auth_pk'							=>  'id',
        // 用户操作日志表
        'auth_log'                           =>  'auth_log',
        //  过期时间，注意，如果使用了JWT验证，请务必保证此项设置与jwt.php配置文件中的token_expire值相同
        'expire'                             =>  '7200', 
        //  session/cache 前缀，如果使用了jwt插件，请务必保持一致
        'prefix'                             =>  'api_jwt_token_',
        //  是否需要刷新登陆状态，  如果使用了JWT插件，请启用下列配置项
        'auth_refresh_on'                    =>  1,
        //  【免验证】免验证模块
        'batch_no_auth_module'               =>  ['api','index'],
        //  【免验证】免验证控制器
        'batch_no_auth_controller'           =>  ['Utils'],
        //  【免验证】免验证方法
        'batch_no_auth_action'               =>  [],
        //  【免验证】免验证具体方法
        'no_auth_method'                     =>  [],

        //  【免登录】当前网站下，不需要登陆的模块
        'batch_no_login_module'              =>  [],
        //  【免登录】当前网站下，不需要登陆的控制器
        'batch_no_login_controller'          =>  ['Utils'],
        //  【免登录】当前网站下，不需要登陆的方法
        'batch_no_login_action'              =>  [],
        //  【免登录】当前网站下，不需要登陆的批量方法
        'no_login_method'                    =>  [],

        // 权限规则是否同个应用中统一，开启统一，即针对同一应用saas多站点，规则表都使用website=0的
        'auth_rule_unified'                  =>	false,
        //  默认配置
        'auth_default_name'                  =>  'admin',
    ],

    //  后台
	'other' =>  [
         // 权限开关
         'auth_on'                           =>  1,
         // 认证方式，1为实时认证；2为登录认证。
         'auth_type'                         =>	1,
         // 用户组数据表名
         'auth_group'                        =>	'auth_group',
         // 用户-用户组关系表
         'auth_group_access'                 =>	'auth_group_access',
         // 权限规则表
         'auth_rule'                         =>	'auth_rule',
         // 用户信息表
         'auth_user'                         =>	'user',
         // 用户信息存贮介质，这里仅支持cache/session
        'auth_driver'                        => 'cache', 
         //	用户扩展信息表	
         'auth_pk'							=>	'id',
         //	登录验证规则别名
         'auth_name'                         =>	'dreamlee',
         'auth_user_extend'                  =>	'user_extend',
         // 用户表ID字段名
         'auth_user_extend'                  =>	'user_extend',
         //  未登录跳转地址
         'url'                               =>  '/index/login',//为空，没登陆时返回106json，否则填写登陆页的路由/index/login
         //  过期时间，注意，如果使用了JWT验证，请务必保证此项设置与jwt.php配置文件中的token_expire值相同
         'expire'                            =>  '7200', 
         //  【免验证】批量免验证模块
         'batch_no_auth_module'              =>  [],
         //  【免验证】批量免验证控制器
         'batch_no_auth_controller'          =>  ['Skills'],
         //  【免验证】批量免验证方法
         'batch_no_auth_action'              =>  ['getNotice', 'getNoticeDetail', 'upload'],
         //  【免验证】免验证具体方法
         'no_auth_method'                    =>  ['admin/Index/index'],        

         //  【免登录】当前网站下，不需要登陆的批量模块
         'batch_no_login_module'             =>  [],
         //  【免登录】当前网站下，不需要登陆的批量控制器
         'batch_no_login_controller'         =>  [],
         //  【免登录】当前网站下，不需要登陆的批量方法
         'batch_no_login_action'             =>  [], 
         //  【免登录】当前网站下，不需要登陆的批量方法
         'no_login_method'                   =>  [], 

         // 权限规则是否同个应用中统一，开启统一，即针对同一应用saas多站点，规则表都使用website=0的
         'auth_rule_unified'                 =>	false,

         //  默认配置
         'auth_default_name'                 =>  'admin',
     ],

];