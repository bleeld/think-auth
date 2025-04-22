<?php
declare (strict_types = 1);
namespace think\util;


class Tools
{


    /**
     * 将数组转化为平面
     * @param array||object $array 需要转化的数组/对象
     * @param int $pid 需要设置的当前节点的父级节点，默认为0
	 * @param string $symbol 需要设置的当前节点的符号，默认为|---
     * @param int $level 需要设置定的当前节点的级别，默认为1
	 * @return array 返回转化后的数组
     */
	public static function array_to_liner ($array=null, ?int $pid=0, $symbol='|---', $level=1) {
		$arr = array();
		if(is_object($array)) {
			$array = $array->toArray();
		}
		foreach($array as $k =>$v){
			if($v['pid'] == $pid){
				$v['level'] = $level;
				$v['html'] = str_repeat($symbol, $level-1);
				$arr[] = $v;
				unset($array[$k]);
				$arr = array_merge($arr, array_to_liner($array, $symbol, $v['id'], $level+1));
			}
		}
		return $arr;
	}

}