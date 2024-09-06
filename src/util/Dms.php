<?php
/*
 * @Author: DreamLee
 * @Date: 2024-09-06 17:20:08
 * @LastEditors: error: error: git config user.name & please set dead value or install git && error: git config user.email & please set dead value or install git & please set dead value or install git
 * @LastEditTime: 2024-09-06 17:57:30
 * @FilePath: \GitHub\think-auth\src\util\Dms.php
 * @Description: 这是默认设置,请设置`customMade`, 打开koroFileHeader查看配置 进行设置: https://github.com/OBKoro1/koro1FileHeader/wiki/%E9%85%8D%E7%BD%AE
 */
declare (strict_types = 1);
namespace think\auth\util;


use think\auth\base\Init;
use think\facade\Db;

class Dms extends Init
{

    public function __construct() {
        parent::__construct();
    }


    /**
     * 获得用户资料,根据自己的情况读取数据库 
     */
    function getUserInfo($uid)
    {
        static $userinfo = [];
        $user = Db::name($this->config['auth_user']);
        // 获取用户表主键
        $_pk = is_string($user->getPk()) ? $user->getPk() : $this->config['auth_pk'];
        if (!isset($userinfo[$uid])) {
            $userinfo[$uid] = $user->where($_pk, $uid)->find();
        }
        return $userinfo[$uid];
    }

    //根据uid获取角色名称
    //根据uid获取角色名称
    function getRole($uid){
        try{
            $auth_group_access =  Db::name($this->config['auth_group_access'])->where('uid',$uid)->find();
            $title =   Db::name($this->config['auth_group'])->where('id',$auth_group_access['group_id'])->value('title');
            return $title;
        }catch (\Exception $e){
            return '此用户未授予角色';
        }
    }

    //根据uid获取角色名称
    //根据uid获取角色名称
    /**
     * 根据用户UID获取该用户所有角色
     * @param integer $uid 用户ID
     * @param integer $type 想要获取到的字段信息类型， 0 全部字段， 1 id字段
     * @return array 用户组的ID信息
     */
    function getUserRoles($uid = null,  int $type = 1){
        try{
            if($type){
                return Db::view($this->config['auth_group_access'], [])
                ->view($this->config['auth_group'], ['id'], "{$this->config['auth_group_access']}.group_id={$this->config['auth_group']}.id", 'LEFT')
                ->where("{$this->config['auth_group_access']}.uid='{$uid}' and {$this->config['auth_group']}.status=9")
                ->select();
            }else{
                return Db::view($this->config['auth_group_access'], 'group_id as id')
                ->view($this->config['auth_group'], 'title,rules', "{$this->config['auth_group_access']}.group_id={$this->config['auth_group']}.id", 'LEFT')
                ->where("{$this->config['auth_group_access']}.uid='{$uid}' and {$this->config['auth_group']}.status=9")
                ->select();
            }
        }catch (\Exception $e){
            return '此用户未授予角色';
        }
    }



	//传递一个子分类ID返回他的所有父级分类
    /**
     * 获取所有用户组信息
     * @param integer $type, 用户组状态类型， 0 全部，1 正常
     */
	function getAllRules($type=0) {
        // 转换表名
        $auth_group = $this->config['auth_group'];
        //  获取所有用户组
        if($type){
            return Db::view($auth_group, ['id', 'pid', 'title', 'rules', 'sort'])->where("{$auth_group}.status=9")->select();
        }else{
            return Db::view($auth_group, ['id', 'pid', 'title', 'rules', 'sort'])->select();
        }
	}



}