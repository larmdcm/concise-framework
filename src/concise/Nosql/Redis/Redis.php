<?php

namespace Concise\Nosql\Redis;

use Concise\Foundation\Config;

class Redis
{
	/**
	 * redis instance
	 * @var object
	 */
	protected $redis;

	/**
	 * 默认配置
	 * @var array
	 */
	protected $config = [
		'host' 	   => '127.0.0.1',
		'port' 	   => '',
		'password' => '',
		'select'   => 0
	];

	/**
	 * 构造方法初始化
	 * @return void
	 */
	public function __construct ()
	{
		if (!extension_loaded('redis')) {
            throw new \BadFunctionCallException('not support: redis');
        }
        $config = Config::scope('redis')->get();
		$this->config = array_merge($this->config,is_null($config) ? [] : $config);
		$this->redis  = new \Redis();
		$this->redis->connect($this->config['host'],$this->config['port'],$this->config['time_out']);

		if (!empty($this->config['password'])) {
			$this->redis->auth($this->config['password']);
		}

		if ($this->config['select'] != 0) {
			$this->redis->select($this->config['select']);
		}
	}

	/**
	 * 设置
	 * @param string  $key   
	 * @param string  $value 
	 * @param integer $time  
	 * @return mixed
	 */
	public function set ($key = '',$value = '',$time = 0)
	{
		if (empty($key)) {
            return false;
        }
        if (is_array($value)) {
            $value = json_encode($value);
        } 
        if ($time == 0) {
            return $this->redis->set($key, $value);
        }
        return $this->redis->setex($key, $time, $value);
	}
	/**
	 * 无方法时调用
	 * @param  string $method 
	 * @param  array $params 
	 * @return  mixed
	 */
	public function __call ($method,$params)
	{
		if (method_exists($this->redis, $method)) {
			return call_user_func_array([$this->redis,$method],$params);
		}
		throw new \Exception(__CLASS__ . '->' . $method . " method not exists!");
	}
}