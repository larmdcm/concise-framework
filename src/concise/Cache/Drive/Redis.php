<?php

namespace Concise\Cache\Drive;

use Concise\Cache\CacheAbstract;
use Concise\Foundation\Facade\Env;
use Concise\Foundation\Arr;

class Redis extends CacheAbstract
{
	/**
	 * 配置选项
	 * @var array
	 */
	protected $options = [
		'host'       => '127.0.0.1',
        'port'       => 6379,
        'password'   => '',
        'select'     => 0,
        'timeout'    => 0,
        'persistent' => false
	];

	/**
	 * 初始化
	 * @param array $options 参数
	 * @return void
	 */
	public function __construct ($options = [])
	{
		if (!extension_loaded('redis')) {
            throw new \BadFunctionCallException('not support: redis');
        }
		
        if (!empty($options)) {
       		$this->options = array_merge($this->options,$options);
        }

        $this->handler = new \Redis();

        if ($this->options['persistent']) {
            $this->handler->pconnect($this->options['host'], $this->options['port'], $this->options['timeout'], 'persistent_id_' . $this->options['select']);
        } else {
            $this->handler->connect($this->options['host'], $this->options['port'], $this->options['timeout']);
        }

        if ('' != $this->options['password']) {
            $this->handler->auth($this->options['password']);
        }

        if (0 != $this->options['select']) {
            $this->handler->select($this->options['select']);
        }
	}


	/**
	 * 获取缓存key值
	 * @param  string $name 
	 * @param  string $default 
	 * @return mixed 
	 */
	public function get ($name,$default = '')
	{
		$key   = $this->getCacheKey($name);
		$value = $this->handler->get($key);

		if (is_null($value) || $value === false) {
		 	return $default;
		}
		return $this->unserialize($value);
	}

	/**
	 * 值自增
	 * @param  string $name 
 	 * @param  integer $step
	 * @return mixed 
	 */
	public function incr ($name,$step = 1)
	{
		return $this->handler->incrby($this->getCacheKey($name),$step);
	}

	/**
	 * 值自减
	 * @param  string $name 
 	 * @param  integer $step
	 * @return mixed 
	 */
	public function decr ($name,$step = 1)
	{
		return $this->handler->decrby($this->getCacheKey($name),$step);
	}

	/**
	 * 设置key值
	 * @param string $name  
	 * @param mixed $value 
	 * @param mixed $time 
	 * @return mixed
	 */
	public function set ($name,$value,$time = null) 
	{
		$key = $this->getCacheKey($name);

		if (is_null($time)) {
			$time = $this->options['expire_time'];
		}
		if (!is_scalar($value)) {
			$value = $this->serialize($value);
		}
		return $time ? $this->handler->setex($key,$time,$value) : $this->handler->set($key,$value);
	}

	/**
	 * 获取缓存值是否存在
	 * @param  string  $name 
	 * @return boolean       
	 */
	public function has ($name)
	{
		return $this->handler->exists($this->getCacheKey($name));
	}

	/**
	 * 删除key值
	 * @param  string $name 
	 * @return mixed
	 */
	public function delete ($name)
	{
		return $this->handler->delete($this->getCacheKey($name));
	}

	/**
	 * 清除全部
	 * @param string $name 
	 * @return mixed
	 */
	public function clear($name)
	{
		return $this->handler->flushDB();
	}
}