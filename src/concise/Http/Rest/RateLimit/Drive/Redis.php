<?php

namespace Concise\Http\Rest\RateLimit\Drive;

use Concise\Nosql\Redis\Redis as ConciseRedis;

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
	 * 初始化
	 * @return void
	 */
	public function __construct ()
	{
		$this->redis = new ConciseRedis();
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