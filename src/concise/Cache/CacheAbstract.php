<?php

namespace Concise\Cache;

abstract class CacheAbstract
{	

	/**
	 * 缓存驱动
	 * @var null
	 */
	protected $handler = null;
	/**
	 * 缓存参数
	 * @var array
	 */
	protected $options = [];

	/**
	 * 获取缓存key值
	 * @param  string $name 
	 * @return mixed 
	 */
	abstract public function get ($name);
	
	/**
	 * 值自增
	 * @param  string $name 
	 * @return mixed 
	 */
	abstract public function incr ($name);
	
	/**
	 * 值自减
	 * @param  string $name 
	 * @return mixed 
	 */
	abstract public function decr ($name);
	
	/**
	 * 设置key值
	 * @param string $name  
	 * @param mixed $value 
	 * @param mixed $time 
	 * @return mixed
	 */
	abstract public function set ($name,$value,$time = null);
	
	/**
	 * 获取缓存值是否存在
	 * @param  string  $name 
	 * @return boolean       
	 */
	abstract public function has ($name);
	
	/**
	 * 删除key值
	 * @param  string $name 
	 * @return mixed
	 */
	abstract public function delete ($name);

	/**
	 * 清除全部
	 * @param  string $name 
	 * @return mixed
	 */
	abstract public function clear($name);

	/**
	 * build key
	 * @param  string $key 
	 * @return string
	 */
	protected function getCacheKey ($key)
	{
		return sprintf("%s%s",isset($this->options['prefix']) ? $this->options['prefix']: '',$key);
	}
}