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
	 * 序列方法
	 * @var array
	 */
	protected static $serialize = ['serialize','unserialize','concise_serialize',17];

	/**
	 * 获取缓存key值
	 * @param  string $name 
	 * @param  string $default
	 * @return mixed 
	 */
	abstract public function get ($name,$default = '');
	
	/**
	 * 值自增
	 * @param  string $name 
	 * @param  integer $step 
	 * @return mixed 
	 */
	abstract public function incr ($name,$step = 1);
	
	/**
	 * 值自减
	 * @param  string $name 
	 * @param integer $step
	 * @return mixed 
	 */
	abstract public function decr ($name,$step = 1);
	
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

	/**
	 * 序列化
	 * @param  mixed $data 
	 * @return mixed
	 */
	protected function serialize ($data)
	{
		if (is_scalar($data)) {
			return $data;
		}
		$serialize = self::$serialize[0];
		return self::$serialize[2] . $serialize($data);
	}

	/**
	 * 反序列化
	 * @param  string $data 
	 * @return mixed
	 */
	protected function unserialize ($data)
	{
		if (strpos($data,self::$serialize[2]) === 0) {
			$unserialize = self::$serialize[1];
			return $unserialize(substr($data,self::$serialize[3]));
		}
		return $data;
	}


	/**
	 * 注册序列方法
	 * @param  mixed $serialize   
	 * @param  mixed $unserialize 
	 * @param  string $prefix      
	 * @return void         
	 */
	public function registerSerialize ($serialize,$unserialize,$prefix = 'concise_serialize')
	{
		self::$serialize = [$serialize,$unserialize,$prefix,strlen($prefix)];
	}
}