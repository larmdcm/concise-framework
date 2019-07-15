<?php

namespace Concise\Cache\Drive;

use Concise\Cache\CacheAbstract;
use Concise\Foundation\Facade\Env;
use Concise\Foundation\Arr;

class Redis extends CacheAbstract
{
	/**
	 * 参数
	 * @var array
	 */
	protected $options = [
		'host'       => '127.0.0.1',
        'port'       => 6379,
        'password'   => '',
        'select'     => 0,
        'timeout'    => 0,
        'persistent' => false,
        'serialize'  => true,
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

        $this->handler = new \Redis;

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
	 * @return mixed 
	 */
	public function get ($name)
	{
	}

	/**
	 * 值自增
	 * @param  string $name 
	 * @return mixed 
	 */
	public function incr ($name)
	{
	}

	/**
	 * 值自减
	 * @param  string $name 
	 * @return mixed 
	 */
	public function decr ($name)
	{
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
		$name = $this->getCacheKey();
	}

	/**
	 * 获取缓存值是否存在
	 * @param  string  $name 
	 * @return boolean       
	 */
	public function has ($name)
	{
	}

	/**
	 * 删除key值
	 * @param  string $name 
	 * @return mixed
	 */
	public function delete ($name)
	{
	}

	/**
	 * 清除全部
	 * @param string $name 
	 * @return mixed
	 */
	public function clear($name)
	{
	}
}