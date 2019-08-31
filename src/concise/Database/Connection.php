<?php

namespace Concise\Database;

class Connection
{
	/**
	 * 对象实例
	 * @var object
	 */
	private static $instance;

	/**
	 * 连接配置
	 * @var array
	 */
	protected static $config = [
		'host' 	   => '127.0.0.1',
		'port'     => 3306,
		'username' => '',
		'password' => '',
		'name'     => '',
		'charset'  => '',
		'prefix'   => ''
	];

	/**
	 * 初始化
	 * @param array $config
	 */
	public function __construct ($config = [])
	{
		self::$config = array_merge(self::$config,$config);
	}

	/**
	 * 获取对象单例
	 * @param array $config
	 * @return object
	 */
	public static function getInstance ($config = [])
	{
		if (!static::$instance) {
			static::$instance = new static($config);
		}
		return static::$instance;
	}
}