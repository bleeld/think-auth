<?php
declare (strict_types = 1);
/**
 * +----------------------------------------------------------------------
 * | think-auth [thinkphp6]
 * +----------------------------------------------------------------------
 * | FILE: Auth.php
 * | AUTHOR: DreamLee
 * | EMAIL: 1755773846@qq.com
 * | QQ: 1755773846
 * | DATETIME: 2022/03/31 14:47
 * |-----------------------------------------
 * | 不积跬步,无以至千里；不积小流，无以成江海！
 * +----------------------------------------------------------------------
 * | Copyright (c) 2022 DreamLee All rights reserved.
 * +----------------------------------------------------------------------
 */
namespace think\auth\driver;

use think\facade\Db;
use think\facade\Cache;
use think\facade\Session;
use think\facade\Request;

use think\auth\base\Init;
/**
 * 权限认证类
 * 功能特性：
 * 1，是对规则进行认证，不是对节点进行认证。用户可以把节点当作规则名称实现对节点进行认证。
 *      $auth=new Auth();  $auth->check('规则名称','用户id')
 * 2，可以同时对多条规则进行认证，并设置多条规则的关系（or或者and）
 *      $auth=new Auth();  $auth->check('规则1,规则2','用户id','and')
 *      第三个参数为and时表示，用户需要同时具有规则1和规则2的权限。 当第三个参数为or时，表示用户值需要具备其中一个条件即可。默认为or
 * 3，一个用户可以属于多个用户组(auth_group_access表 定义了用户所属用户组)。我们需要设置每个用户组拥有哪些规则(auth_group 定义了用户组权限)
 *
 * 4，支持规则表达式。
 *      在auth_rule 表中定义一条规则时，如果type为1， condition字段就可以定义规则表达式。 如定义{score}>5  and {score}<100  表示用户的分数在5-100之间时这条规则才会通过。
 */
//数据库 请手动创建下sql
/*
------------------------------
-- think_auth_rule，规则表，
-- id:主键，name：规则唯一标识, title：规则中文名称 status 状态：为1正常，为0禁用，condition：规则表达式，为空表示存在就验证，不为空表示按照条件验证
------------------------------
 DROP TABLE IF EXISTS `think_auth_rule`;
CREATE TABLE `think_auth_rule` (  
    `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,  
    `name` char(80) NOT NULL DEFAULT '',  
    `title` char(20) NOT NULL DEFAULT '',  
    `status` tinyint(1) NOT NULL DEFAULT '1',  
    `condition` char(100) NOT NULL DEFAULT '',  
    PRIMARY KEY (`id`),  
    UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
------------------------------
-- think_auth_group 用户组表， 
-- id：主键， title:用户组中文名称， rules：用户组拥有的规则id， 多个规则","隔开，status 状态：为1正常，为0禁用
------------------------------
 DROP TABLE IF EXISTS `think_auth_group`;
CREATE TABLE `think_auth_group` ( 
    `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT, 
    `title` char(100) NOT NULL DEFAULT '', 
    `status` tinyint(1) NOT NULL DEFAULT '1', 
    `rules` char(80) NOT NULL DEFAULT '', 
    PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
------------------------------
-- think_auth_group_access 用户组明细表
-- uid:用户id，group_id：用户组id
------------------------------
DROP TABLE IF EXISTS `think_auth_group_access`;
CREATE TABLE `think_auth_group_access` (  
    `uid` mediumint(8) unsigned NOT NULL,  
    `group_id` mediumint(8) unsigned NOT NULL, 
    UNIQUE KEY `uid_group_id` (`uid`,`group_id`),  
    KEY `uid` (`uid`), 
    KEY `group_id` (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
*/
class Auth
{
    /**
     * var object 对象实例
     */
    protected static $instance;

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

    /**
     * 默认配置  -   存储驱动
     */
    protected $driver = 'session';

    /**
     * 类架构函数
     * Auth constructor.
     */
    public function __construct($isSeparate=false)
    {
        //可设置配置项 auth, 此配置项为数组。
        if($isSeparate){    //  是否多端分离权限配置
            $module     = app('http')->getName();   //应用名
            if ( $module=='api' ) {
                //  获取API配置
                $config = config('auth.api');
            }else{
                //  获取API配置
                $config = config('auth.other');
            }
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

    /**
     * 初始化
     * access public
     * @param array $options 参数
     * return \think\Request
     */
    public static function instance($options = null)
    {
        if (is_null(self::$instance)) {
            self::$instance = new static($options);
        }
        return self::$instance;
    }

    /**
     * 检查权限
     * @param $name string|array  需要验证的规则列表,支持逗号分隔的权限规则或索引数组
     * @param $uid  int           认证用户的id
     * @param int $type 认证类型
     * @param string $mode 执行check的模式
     * @param string $relation 如果为 'or' 表示满足任一条规则即通过验证;如果为 'and'则表示需满足所有规则才能通过验证
     * @param int $authRelationWay 权限获取是否依赖父级用户组权限，0 表示不依赖父级用户组， 1 表示依赖父级用户组
     * @return bool               通过验证返回true;失败返回false
     */
    public function check($name, $uid, $type = null, $mode = 'url', $relation = 'or', $authRelationWay = 0)
    {
        if (!$this->config['auth_on']) {
            return true;
        }

        //  判断是否存在登录类型
        if (!$type) { $type = $this->config['auth_type']; }

        // 获取用户需要验证的所有有效规则列表
        $authList = $this->getAuthList($uid, $type, $authRelationWay);
        if (is_string($name)) {
            $name = strtolower($name);
            if (strpos($name, ',') !== false) {
                $name = explode(',', $name);
            } else {
                $name = [$name];
            }
        }
        $list = []; //保存验证通过的规则名
        if ('url' == $mode) {
            $REQUEST = unserialize(strtolower(serialize(Request::param())));
        }

        if (!empty($name)) {
            //  如果是后端需要获取后端的模块别名
            $manageModule = $this->getManageMoudleAlias();
            $name = array_map(function($item) use ($manageModule) {
                // 在这里执行你想要的操作   -   置换别名
                return preg_replace('/\b'.$manageModule.'(?=\/)/i', 'admin', $item);
            }, $name);
        }

        foreach ($authList as $auth) {
            $query = preg_replace('/^.+\?/U', '', $auth);
            if ('url' == $mode && $query != $auth) {
                parse_str($query, $param); //解析规则中的param
                $intersect = array_intersect_assoc($REQUEST, $param);
                $auth = preg_replace('/\?.*$/U', '', $auth);
                if (in_array($auth, $name) && $intersect == $param) {
                    //如果节点相符且url参数满足
                    $list[] = $auth;
                }
            } else {
                if (in_array($auth, $name)) {
                    $list[] = $auth;
                }
            }
        }
        if ('or' == $relation && !empty($list)) {
            return true;
        }
        $diff = array_diff($name, $list);
        if ('and' == $relation && empty($diff)) {
            return true;
        }
        return false;
    }

    /**
     * 根据用户id获取用户组,返回值为数组
     * @param  $uid int     用户id
     * return array       用户所属的用户组 array(
     *     array('uid'=>'用户id','group_id'=>'用户组id','title'=>'用户组名称','rules'=>'用户组拥有的规则id,多个,号隔开'),
     *     ...)
     */
    public function getGroups(int $uid = null)
    {
        $groups = [];
        if(!$uid){
            return $groups;
        }
        // 转换表名
        $auth_group_access = $this->config['auth_group_access'];
        $auth_group = $this->config['auth_group'];
        // 执行查询
        $groups = Db::view($auth_group_access, 'uid,group_id')
            ->view($auth_group, 'title,rules', "{$auth_group_access}.group_id={$auth_group}.id", 'LEFT')
            ->where("{$auth_group_access}.uid='{$uid}' and {$auth_group}.status='9'")
            ->select()->toArray();
        return $groups;
    }


    /**
     * 获得权限列表
     * @param integer $uid 用户id
     * @param integer $type
     * @param integer $authRelationWay 获取权限节点的方式 0：仅仅获取当前用户所在的当前用户组， 1：以及用户组子父级关系，获取包含权限所有节点（适用于分组继承权限）
     * return array
     */
    protected function getAuthList($uid, $type, $authRelationWay = 0)
    {
        static $_authList = []; //保存用户验证通过的权限列表
        $t = implode(',', (array)$type);
        if (isset($_authList[$uid . $t])) {
            return $_authList[$uid . $t];
        }
        //返回cache/session中结果
        if(strtolower($this->driver) == "cache") {
            if (2 == $this->config['auth_type'] && Cache::has('_auth_list_' . $uid . $t)) {
                return Cache::get('_auth_list_' . $uid . $t);
            }
        }else if(strtolower($this->driver) == "session"){
            if (2 == $this->config['auth_type'] && Session::has('_auth_list_' . $uid . $t)) {
                return Session::get('_auth_list_' . $uid . $t);  
            }
        }
        //  根据authRelationWay分型获取权限节点
        if($authRelationWay) {
            //  合并父级权限获取总权限，默认只合并用户所在用户组及该用户组的直系父级用户组权限
            $ids = $this->getUserAllRulesByGroupRelation($uid, 0);
        }else{
            //读取用户所属用户组
            $groups = $this->getGroups($uid);
            $ids = []; //保存用户所属用户组设置的所有权限规则id
            foreach ($groups as $g) {
                $ids = array_merge($ids, explode(',', trim($g['rules'], ',')));
            }
            $ids = array_unique($ids);
        }
        if (empty($ids)) {
            $_authList[$uid . $t] = [];
            return [];
        }
        $map = [
            ['type', '=', $type],
            ['id', 'in', $ids],
            ['status', '=', 9],
        ];
        //读取用户组所有权限规则
        $rules = Db::name($this->config['auth_rule'])->where($map)->field('condition,name')->select();
        //循环规则，判断结果。
        $authList = []; //
        foreach ($rules as $rule) {
            if (!empty($rule['condition'])) {
                //根据condition进行验证
                $user = $this->getUserInfo($uid); //获取用户信息,一维数组
                $command = preg_replace('/\{(\w*?)\}/', '$user[\'\\1\']', $rule['condition']);
                //  @(eval('$condition=(' . $command . ');'));
                $condition = '$condition=(' . $command . ');';
                @eval($condition);
                $condition && $authList[] = strtolower($rule['name']);
                if ($condition) {
                    $authList[] = strtolower($rule['name']);
                }
            } else {
                //只要存在就记录
                $authList[] = strtolower($rule['name']);
            }
        }
        $_authList[$uid . $t] = $authList;
        if (2 == $this->config['auth_type']) {
            //规则列表结果保存到cache/session
            if(strtolower($this->driver) == "cache") {
                Cache::set('_auth_list_' . $uid . $t, $authList);                
            }else if(strtolower($this->driver) == "session"){
                Session::set('_auth_list_' . $uid . $t, $authList);  
            }
        }
        return array_unique($authList);
    }




}