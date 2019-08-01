<?php

namespace Concise\Cache;

use Concise\Foundation\Config;

class Cache
{
	/**
	 * 实例
	 * @var object
	 */
	protected static $instacne;
	
	/**
	 * 缓存驱动
	 * @var object
	 */
	protected $handler;

	
	/**
	 * 缓存参数
	 * @var array
	 */
	protected $options = [
		'drive'        => 'File',
		'prefix'       => '',
		'expire_time'  => 0,
	];

	/**
	 * 构造方法初始化
	 * @return void
	 */
	public function __construct ()
	{
		$this->options = array_merge($this->options,Config::scope('cache')->get() ? Config::scope('cache')->get() : []);
		$class = "\Concise\Cache\Drive\\" . ucfirst(empty($this->options['drive']) ? 'File' : $this->options['drive']);
		$this->handler = new $class($this->options);
	}

	/**
	 * 获取对象实例
	 * @return object
	 */
	public static function getInstance ()
	{
		if (is_null(static::$instacne)) {
			static::$instacne = new static();
		}
		return static::$instacne;
	}

	/**
	 * 方法调用
	 * @param  string $method 
	 * @param  array $params 
	 * @return mixed         
	 */
	public function __call ($method,$params)
	{
		$instance = static::getInstance()->handler;
		if (method_exists($instance, $method)) {
			return call_user_func_array([$instance,$method],$params);
		}
		throw new \BadMethodCallException('method not exists:' . __CLASS__ . '->' . $method);
	}

	/**
	 * 静态方法调用
	 * @param  string $method 
	 * @param  array $params 
	 * @return mixed         
	 */
	public static function __callStatic ($method,$params)
	{
		$instance = static::getInstance()->handler;
		if (method_exists($instance, $method)) {
			return call_user_func_array([$instance,$method],$params);
		}
		throw new \BadMethodCallException('method not exists:' . __CLASS__ . '->' . $method);
	}
}