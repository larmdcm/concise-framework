<?php

namespace Concise\Http\Rest\RateLimit\Drive;

class Redis
{
	/**
	 * redis save prefix
	 * @var string
	 */
	CONST PREFIX  = 'concise:api_rate_limit:';

	/**
	 * redis instacne
	 * @var object
	 */
	protected $redis;

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
	 * 限流检测
	 * @param  string $identity 
	 * @param  integer $identity 
	 * @param  integer $limit
	 * @return bool
	 */
	public function check ($identity,$time,$limit)
	{
		$key = self::PREFIX . $identity;

		if ($this->redis->exists($key)) {
			
			$this->redis->incr($key);
			$count = $this->redis->get($key);

			if ($count > $limit) {
				return false;
			}
			return true;
		}

		return $this->redis->setex($key,$time,1);
	}
}