<?php

return [
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
    'auth_driver'                        => 'session', 
    //	用户扩展信息表	
    'auth_pk'							=>	'id',

    //  【免验证】批量免验证模块
    'batch_no_auth_module'              =>  [],
    //  【免验证】批量免验证控制器
    'batch_no_auth_controller'          =>  ['Skills'],
    //  【免验证】批量免验证方法
    'batch_no_auth_action'              =>  ['getNotice', 'getNoticeDetail', 'upload'],
    //  【免验证】免验证具体方法
    'no_auth_method'                    =>  ['admin/Index/index'],        

    
    //  未登录跳转地址
    'url'                               =>  '/index/login',//为空，没登陆时返回106json，否则填写登陆页的路由/index/login
    //  【免登录】当前网站下，不需要登陆的批量模块
    'batch_no_login_module'             =>  [],
    //  【免登录】当前网站下，不需要登陆的批量控制器
    'batch_no_login_controller'         =>  [],
    //  【免登录】当前网站下，不需要登陆的批量方法
    'batch_no_login_action'             =>  [], 
    //  【免登录】当前网站下，不需要登陆的批量方法
    'no_login_method'                   =>  [], 

    //  默认配置超管的ID
    'auth_super_id'                     =>  1,
];