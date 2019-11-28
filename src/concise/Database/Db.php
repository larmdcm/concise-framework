<?php

namespace Concise\Database;

class Db
{
	/**
	 * Db配置参数
	 * @var array
	 */
	private static $config = [];

	/**
	 * 设置Db配置参数
	 * @param array $config 
	 * @return void
	 */
	public static function setConfig ($config)
	{
		self::$config = $config;
	}

	/**
	 * 获取实例
	 * @param array $config
	 * @return Query
	 */
	public static function getQuery ($config = [])
	{
		if (!empty($config)) {
			static::setConfig($config);
		}

		return Query::newQuery(static::$config);
	}

	/**
	 * 静态方法调用
	 * @param  string $method 
	 * @param  array $params 
	 * @return mixed     
	 */
	public static function __callStatic ($method,$params)
	{
		return call_user_func_array([static::getQuery(),$method],$params);
	}
}