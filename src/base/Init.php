<?php
declare (strict_types = 1);
namespace think\auth\base;

class Init
{

    //默认配置
    protected $config = [
        'auth_on'           => 1, // 权限开关
        'auth_type'         => 1, // 认证方式，1为实时认证；2为登录认证。
        'auth_group'        => 'auth_group', // 用户组数据不带前缀表名
        'auth_group_access' => 'auth_group_access', // 用户-用户组关系不带前缀表名
        'auth_rule'         => 'auth_rule', // 权限规则不带前缀表名
        'auth_user'         => 'user', // 用户信息表不带前缀表名,主键自增字段为id
        'auth_driver'       => 'session', // 用户信息存贮介质
        'auth_pk'           => 'id',// 用户表ID字段名
    ];

    //默认配置  -   存储驱动
    protected $driver = 'session';

    //  类架构函数  Auth constructor.
    public function __construct($isSeparate=false)
    {
        //可设置配置项 auth, 此配置项为数组。
        if($isSeparate){    //  是否多端分离权限配置
            $module     = app('http')->getName();   //应用名
            $config = ( $module=='api' ) ? config('auth.api') : config('auth.other');
        }else{  //  单一/统一权限配置
            $config = config('auth');
        }
        if ($config) {
            $this->config = array_merge($this->config, $config);
        }
        if ($this->config['auth_driver']) {
            $this->driver = $this->config['auth_driver'];
        }
    }

}