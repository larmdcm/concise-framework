<?php

namespace Concise\Database\Concern;

trait Attribute
{
	/**
	 * 设置属性
	 * @param string $name  
	 * @param mixed $value 
	 * @return void
	 */
	protected function setAttr ($name,$value)
	{
		$this->data[$name] = $this->convertAttrbiuteAsMethod('set',$name,$value);
	}

	/**
	 * 获取属性
	 * @param  string $name 
	 * @return mixed       
	 */
	protected function getAttr ($name)
	{
		$relationModel = $this->getRelationModelData($name);
		if ($relationModel !== false) {
			return $relationModel;
		}
		return $this->convertAttrbiuteAsMethod('get',$name,$this->data[$name]);
	}

	/**
	 * 转换属性方法
	 * @param  string $type  
	 * @param  string $name  
	 * @param  mixed $value 
	 * @return mixed
	 */
	protected function convertAttrbiuteAsMethod ($type,$name,$value)
	{
		$method = sprintf('%s%s',$type,$this->convertAttrbiuteAsMethodName($name));
		return method_exists($this, $method) ? call_user_func([$this,$method],$value) : $value;
	}

	/**
	 * 转换属性方法名称
	 * @param  string $name 
	 * @return string       
	 */
	protected function convertAttrbiuteAsMethodName ($name)
	{
		return ucfirst($this->camelize($name));
	}

	/**
	 * 获取关联模型数据
	 * @param  string $name 
	 * @return mixed       
	 */
	private function getRelationModelData ($name)
	{
		if (!method_exists($this,$name)) {
			return false;
		}
		$relationModel = call_user_func([$this,$name]);
		return is_object($relationModel) && method_exists($relationModel,'getRelationModelData') 
				? call_user_func([$relationModel,'getRelationModelData']) : false;
	}
}