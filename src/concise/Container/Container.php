<?php

namespace Concise\Container;

use Concise\Ioc\Ioc;

class Container
{
	/**
	 * 绑定列表
	 * @var array
	 */
	protected $bindings = [];

	/**
	 * 类实例列表
	 * @var array
	 */
	protected $instances = [];

	/**
	 * 单例对象
	 * @var null
	 */
	protected static $instance;

	/**
	 * 获取单例对象
	 * @access public
	 * @return object
	 */
	public static function getInstance ()
	{
		if (is_null(static::$instance)) {
			static::$instance = new static();
		}
		return static::$instance;
	}
	/**
	 * 绑定
	 * @access public
	 * @param  string $alias  
	 * @param  mixed $concreate 
	 * @return object
	 */
	public function bind ($alias,$concreate = null)
	{
		if (is_array($alias)) {
			$this->bindings = array_merge($alias,$this->bindings);
		} elseif (is_object($concreate)) {
			$this->instances[$alias] = $concreate;
		} else  {
			$this->bindings[$alias] = $concreate;
		}
		return $this;
	}

	/**
	 * 获取别名是否已注入
	 * @param  string  $alias 
	 * @return boolean        
	 */
	public function has ($alias)
	{
		return array_key_exists($alias,$this->bindings) || array_key_exists($alias,$this->instances);
	}
	/**
	 * 删除注入的类实例
	 * @param  string $alias 
	 * @return bool
	 */
	public function delete ($alias)
	{
		if (array_key_exists($alias,$this->instances)) {
			unset($this->instances[$alias]);
		}
		return true;
	}

	/**
	 * 清除全部
	 * @return bool
	 */
	public function clear ()
	{
		$this->bindings  = [];
		$this->instances = [];
		return true;
	}

	/**
	 * 创建类实例
	 * @access public
	 * @param  string $alias 
	 * @param  array $params 
	 * @param  bool $singleton 
	 * @return mixed         
	 */
	public function make ($alias,$params = [],$singleton = true)
	{
		if (!array_key_exists($alias, $this->bindings) && !array_key_exists($alias,$this->instances)) {
			throw new \RuntimeException("{$alias} does not exist in the container");
		}
		if (array_key_exists($alias, $this->instances)) {
			return $this->instances[$alias];
		}
		if (is_callable($this->bindings[$alias])) {
			$object = call_user_func_array($this->bindings[$alias],Ioc::getFuncParams($this->bindings[$alias],$params));
		} else {
			$object = Ioc::getInstance($this->bindings[$alias],$params);
		}
		if ($singleton) {
			$this->instances[$alias] = $object;
		}
		return $object;
	}
	/**
	 * 获取类实例
	 * @param  string  $alias     
	 * @param  array  $params    
	 * @param  boolean $singleton 
	 * @return mixed         
	 */
	public static function get ($alias,$params = [],$singleton = true)
	{
		return static::getInstance()->make($alias,$params,$singleton);
	}

	/**
	 * 设置类实例
	 * @param mixed $alias     
	 * @param mixed $concreate 
	 * @return object
	 */
	public static function set ($alias,$concreate = null)
	{
		return static::getInstance()->bind($alias,$concreate);
	}

	/**
	 * 获取别名是否已注入
	 * @param  string  $alias 
	 * @return boolean        
	 */
	public static function exists ($alias)
	{
		return static::getInstance()->has($alias);
	}

	/**
	 * 删除注入的类实例
	 * @param  string $alias 
	 * @return bool
	 */
	public static function remove ($alias)
	{
		return static::getInstance()->delete($alias);
	}

	/**
	 * 清除全部
	 * @return bool
	 */
	public static function flush ()
	{
		return static::getInstance()->clear();
	}
}