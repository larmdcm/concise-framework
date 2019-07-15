<?php

namespace Concise\Facade;

use Concise\Container\Container;
use Concise\Ioc\Ioc;

abstract class Facade
{
	/**
	 * 类实例列表
	 * @var array
	 */
	protected static $classInstance = [];

	/**
	 * Get Facdes Instance
	 * @return object
	 */
	public static function getInstance ()
	{
		$class = static::getFacadeAccessor();
		if (is_object($class)) {
			return $class;
		}
		if (isset(static::$classInstance[$class]) && is_object(static::$classInstance[$class])) {
			return static::$classInstance[$class];
		}
		$classInstance = Container::exists(lcfirst($class)) ? Container::get(lcfirst($class)) : null;
		if (!is_null($classInstance)) {
			return $classInstance;
		}
		$instance = Ioc::getInstance($class);
		static::$classInstance[$class] = $instance;
		return $instance;
	}	
	/**
	 * Get FacadeAccessor Name 
	 * @return string     
	 */
	public static function getFacadeAccessor ()
	{
		throw new \RuntimeException('Facade does not implement getFacadeAccessor method.');
	}
	/**
	 * 静态方法调用
	 * @param  string $method 
	 * @param  array $params 
	 * @return mixed         
	 */
	public static function __callStatic ($method,$params)
	{
		$instance = static::getInstance();
		if (method_exists($instance, $method)) {
			return call_user_func_array([$instance,$method],$params);
		}
		if (method_exists($instance,'__call')) {
			return call_user_func_array([$instance,$method],$params);
		}
		throw new \BadMethodCallException('method not exists:' . __CLASS__ . '->' . $method);
	}
}