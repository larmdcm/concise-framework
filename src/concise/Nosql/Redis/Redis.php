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
	 * 默认配置选项
	 * @var array
	 */
	protected $options = [
		'host'       => '127.0.0.1',
        'port'       => 6379,
        'password'   => '',
        'select'     => 0,
        'timeout'    => 0,
        'persistent' => fals
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
        $options = Config::scope('redis')->get('',[]);
		$this->options = array_merge($this->options,is_null($options) ? [] : $options);
		
		$this->redis = new \Redis();

        if ($this->options['persistent']) {
            $this->redis->pconnect($this->options['host'], $this->options['port'], $this->options['timeout'], 'persistent_id_' . $this->options['select']);
        } else {
            $this->redis->connect($this->options['host'], $this->options['port'], $this->options['timeout']);
        }

        if ('' != $this->options['password']) {
            $this->redis->auth($this->options['password']);
        }

        if (0 != $this->options['select']) {
            $this->redis->select($this->options['select']);
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