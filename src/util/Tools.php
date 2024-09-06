<?php
/*
 * @Author: DreamLee
 * @Date: 2024-09-06 17:05:08
 * @LastEditors: error: error: git config user.name & please set dead value or install git && error: git config user.email & please set dead value or install git & please set dead value or install git
 * @LastEditTime: 2024-09-06 17:56:47
 * @FilePath: \GitHub\think-auth\src\util\Tools.php
 * @Description: 这是默认设置,请设置`customMade`, 打开koroFileHeader查看配置 进行设置: https://github.com/OBKoro1/koro1FileHeader/wiki/%E9%85%8D%E7%BD%AE
 */
declare (strict_types = 1);
namespace think\auth\util;

use think\auth\base\Init;
use think\auth\driver\Auth;

class Tools extends Init
{


    public function __construct() {
        parent::__construct();
    }


    /**
     * 获取当前用户所在用户组以及用户组的所有父级节点
     * @param integer $uid 用户ID
     * @param integer $deep 依赖层级，0 表示依赖于父级用户组，1 表示依赖于所有的层级用户组（需要获取当前用户所在用户组的所有父级用户组，直到用户组的父级ID为0）
     * @return array       用户所属的用户组 array(
     *     array('uid'=>'用户id','group_id'=>'用户组id','title'=>'用户组名称','rules'=>'用户组拥有的规则id,多个,号隔开'),
     *     ...)
     */
    public function getUserAllRulesByGroupRelation(int $uid = null, int $deep = 0)
    {
        //  获取当前用户全部可用的用户组以及所有的系统用户组
        $userGroups = $this->getUserRoles($uid);
        $groups = $this->getAllRules(1);
        if(empty($userGroups) || empty($groups)){
            return [];
        }
        if($deep){
            //  获取当前用户的用户组的所有父级用户组按照父级用户组等级  -   方式一：筛选出当前用户组的所有用户组层级
            $ruleNodes = [];
            foreach ($userGroups as $g) {
                $node = $this->getParents($groups, $g['id']);
                $ruleNodes = array_merge($ruleNodes, $this->getMergeArrayField($node, 'rules'));
            }
            $ruleNodes = array_unique($ruleNodes);
        }else{
            //  获取当前用户的用户组的父级用户组    -   方式二：筛选出当前用户组的父级用户组
            $ruleNodes = [];
            foreach ($userGroups as $g) {
                $node = $this->getParent($groups, $g['id']);
                $ruleNodes = array_merge($ruleNodes, $this->getMergeArrayField($node, 'rules'));
            }
            //  获取当前用户所在的用户组，并将数据加入到其中
            $userGroupInfo = $this->getGroups($uid);
            $ruleNodes = array_merge($ruleNodes, $this->getMergeArrayField($userGroupInfo, 'rules'));
        }
        return array_unique($ruleNodes);
    }


	//传递一个子分类ID返回他的所有父级分类
	static function getParents($cate, $id) {
		$arr = [];
        //  判断用户组数据是否为空
        if(!empty($cate)){
            foreach ($cate as $v) {
                if ($v['id'] == $id) {
                    $arr[] = $v;
                    $arr   = array_merge($this->getParents($cate, $v['pid']), $arr);
                }
            }
        }
        return $arr;
	}


	//传递分类ID，返回他父级分类或顶级分类
	// $type 1为父级，2为顶级
	static function getParent($cate, $id, $type = 1) {
		$parent_info = [];
		$arrs        = $this->getParents($cate, $id);
		if (empty($arrs)) {
			return $parent_info;
		}
		$self = [];
		foreach ($arrs as $v) {
			if ($v['id'] == $id) {
				$self = $v;
				break;
			}
		}
		//父级/顶级 是自己，则直接返回
		if ($self['pid'] == 0) {
			return $parent_info; //空/null
		}
		foreach ($arrs as $v) {
			if ($type == 1) {
				if ($v['id'] == $self['pid']) {
					$parent_info = $v;
					break;
				}
			} else if ($type == 2) {
				//顶级
				if ($v['pid'] == 0) {
					$parent_info = $v;
					break;
				}
			}
		}
		return $parent_info;
	}


    /**
     * @method get_alias_manage_module 通过键值获取数组中对应的键名
     * @param array $data 被搜索的数组
     * @param string $value 需要查找的字段值
     * 
     * @return mixed 返回查找的结果
     */
    static function getManageMoudleAlias(array $data = null, string $value = null)
    {
		if (is_null($data)) { $data = empty(config('app.app_map')) ? ['admin'=>'admin'] : config('app.app_map'); }
		if (is_null($value)) { $value = 'admin'; }
		return is_null($value) ? false : @array_search($value, $data);
    }

    /**
     * 对数组中指定的字段进行提取合并
     * @param array $array 需要处理的数组
     * @param string $field 需要提取的字段名称
     * @return array 返回的信息
     */
    static function getMergeArrayField(array $array = null, string $field = null){
        if(!(is_array($array) && !empty($array) && !empty($field))){
            return [];
        }
        $rules = [];
        //  判断并提取字段值
        if(array_key_exists($field, $array)){   //  单数组
            $rules = explode(',', $array[$field]);
        } else {  //  多维数组
            $_rules = array_column($array, $field);
            foreach($_rules as $r){
                if(empty($rules)){
                    $rules = explode(',', $r);
                }else{
                    $__rules = explode(',', $r);
                    foreach($__rules as $__rv){
                        $rules[] = $__rv;
                    }
                }
            }
        }
        return array_values($rules);
    }


}

