<?php

namespace Concise\Http\Rest\RateLimit;

use Concise\Foundation\Config;
use Concise\Container\Container;

class RateLimit
{
	/**
	 * instance
	 * @var Auth
	 */
	protected static $instance;
	
	/**
	 * handler instance
	 * @var object
	 */
	protected $handler;

	/**
	 * 默认配置
	 * @var array
	 */
	protected $config = [];

	/**
	 * 初始化
	 * @return void
	 */
	public function __construct ()
	{

		$this->config = [
			// 选择驱动
			'drive'       => 'redis',
			// 时间
			'time'        => 60,
			// 次数
			'limit'       => 600,
			// 错误消息
			'error_msg'   => "your have too many request",
			// 错误码
			'error_code'  => 401,
			// 连接选项
			'connect_options' => []
		];


		$config = Config::scope('api')->get('rate_limit',[]);
		$this->config = array_merge($this->config,$config);

		$drive = ucfirst(empty($this->config['drive']) ? 'redis' : $this->config['drive']);
		$className = "Concise\\Http\\Rest\\RateLimit\\Drive\\" . ucfirst($drive);

		if (!class_exists($className)) {
			throw new \RuntimeException("Api Redis Drive not exists!");
		}
		$this->handler = new $className(isset($this->config['connect_options']) ? $this->config['connect_options'] : []);
	}

	/**
	 * 获取单例对象
	 * @return mixed
	 */
	public static function getInstacne ()
	{
		if (is_null(static::$instance)) {
			static::$instance = new static();
		}
		return static::$instance;
	}


	/**
	 * 限流检测
	 * @param  string $identity 
	 * @param  integer $identity 
	 * @param  integer $limit
	 * @return bool
	 */
	public function check ($name,$time = null,$limit = null)
	{
		if (is_null($time) ) {
			$time = $this->config['time'];
		}
		
		if (is_null($limit) ) {
			$limit = $this->config['limit'];
		}

		return $this->handler->check($name,$time,$limit);
	}

	/**
	 * 返回错误码
	 * @return integer
	 */
	public function getErrorCode ()
	{
		return $this->config['error_code'];
	}

	/**
	 * 错误消息
	 * @return string
	 */
	public function getError ()
	{
		return $this->config['error_msg'];
	}
}