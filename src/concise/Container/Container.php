<?php

namespace Concise\Container;

use Concise\Ioc\Ioc;
use Closure;

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
		return isset($this->bindings[$alias]) || isset($this->instances[$alias]);
	}
	/**
	 * 删除注入的类实例
	 * @param  string $alias 
	 * @return bool
	 */
	public function delete ($alias)
	{
		if (isset($this->instances[$alias])) {
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
		if (is_bool($params)) {
			$singleton = $params;
			$params    = [];
		}

		if ($alias instanceof Closure) {
			return call_user_func_array($alias,Ioc::getFuncParams($alias,$params));
		}
		
		if (isset($this->instances[$alias]) && $singleton) {
			return $this->instances[$alias];
		}

		
		if (!isset($this->bindings[$alias])) {
			$instance = Ioc::getInstance($alias,$params);
			if ($singleton) {
				$this->instances[$alias] = $object;
			}
			return $instance;
		}

		$concreate = $this->bindings[$alias];

		if ($concreate instanceof Closure) {
			$object = call_user_func_array($concreate,Ioc::getFuncParams($concreate,$params));
		} else {
			$object = Ioc::getInstance($concreate,$params);
			if ($singleton) {
				$this->instances[$alias] = $object;
			}
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

	/**
	 * 执行类方法
	 * @param  string $alias     
	 * @param  string  $method    
	 * @param  array  $arguments 
	 * @param  boolean $singleton 
	 * @return mixed         
	 */
	public function invokeMethod ($alias,$method = '',$arguments = [],$singleton = false)
	{
		if (is_bool($arguments)) {
			$singleton = $arguments;
			$arguments = [];
		}

		$instance =	$this->make($alias,[],$singleton);

		if (!method_exists($instance, $method)) {
			throw new \RuntimeException(get_class($instance) . "->{$method} {$method} Method Not Exists");
		}

		return call_user_func_array([$instance,$method],Ioc::getMethodParams(get_class($instance),$method,$arguments));
	}
}