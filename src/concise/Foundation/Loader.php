<?php

namespace Concise\Foundation;

use Concise\Exception\ClassNotException;

class Loader
{
	/**
	 * 加载类集合 
	 * @var array
	 */
	protected static $classMap   = [];

	/**
	 * 自动加载路径
	 * @var array
	 */
	protected static $paths      = [];

	/**
	 * 加载的命名空间
	 * @var array
	 */
	protected static $namespaces = ['App'];

	/**
	 * 别名注册
	 * @var array
	 */
	protected static $aliasClass;

	/**
	 * 添加自动路径
	 * @param mixed $path 
	 * @return bool
	 */
	public static function addPath ($path)
	{
		is_array($path) ? self::$paths = array_merge($path,self::$paths) : array_push(self::$paths,$path);

		return true;
	}
	/**
	 * 添加命名空间
	 * @param mixed $name 
	 * @return bool
	 */
	public static function addNamespace ($name)
	{
		is_array($name) ? self::$namespaces = array_merge($name,self::$namespaces) : array_push(self::$namespaces,$name);

		return true;
	}
	/**
	 * 注册自动加载
	 * @param mixed $path 
	 * @return void
	 */
	public static function register ($path = '')
	{
		!empty($path) && self::addPath($path);
		$aliasClass = require __DIR__ . '/Config/aliasClass.php';
		static::$aliasClass = $aliasClass;
		foreach (static::$aliasClass as $alias => $class) {
			class_alias($class,$alias);
		}
		return spl_autoload_register([__CLASS__,'autoload']);
	}

	/**
	 * 注册别名
	 * @param  mixed $alias     
	 * @param  string $className 
	 * @return bool          
	 */
	public static function reigsterAlias ($alias,$className = '')
	{
		if (is_array($alias)) {
			self::$aliasClass = array_merge($alias,self::$aliasClass);
		} else {
			self::$aliasClass[$alias] = $className;
		}
		return true;
	}
	/**
	 * 自动加载
	 * @param  string $class 
	 * @return void|boolean   
	 */
	public static function autoload ($class)
	{
		$className = str_replace("\\","/",$class);
		if (isset(self::$classMap[$className])) {
			return true;			
		}
		foreach (self::$paths as $v)
		{
			// 搜索在自动加载路径内的
			$names = [lcfirst($className),$className,ucfirst($className)];
			foreach ($names as $name)
			{
				$path = rtrim($v,'/') . '/' . $name . '.php';
				if (self::requireFile($path,$className)) {
					return true;
				}
			}
			// 搜索自动添加命名空间
			foreach (self::$namespaces as $namespace)
			{
				$classNameT = str_replace($namespace,"\\","/") . '/' . ltrim($className,'/');
				$names = [lcfirst($classNameT),$classNameT,ucfirst($classNameT)];
				foreach ($names as $name)
				{
					$path = rtrim($v,'/') . '/' . $name . '.php';
					if (self::requireFile($path,$className)) {
						return true;
					}
				}
			}
		}
	}
	/**
	 * 引入文件并缓存于内存中
	 * @param  string $path 
	 * @param  string $className 
	 * @return bool
	 */
	public static function requireFile ($path,$className)
	{
		if (is_file($path))
		{
			self::$classMap[$className] = $path;
			require $path;
			return true;
		}
		return false;
	}
}