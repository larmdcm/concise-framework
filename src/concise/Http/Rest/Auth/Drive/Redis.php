<?php

namespace Concise\Http\Rest\Auth\Drive;

use Concise\Nosql\Redis\Redis as ConciseRedis;

class Redis
{
	/**
	 * redis save prefix
	 * @var string
	 */
	CONST PREFIX = 'concise:api:';

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
	 * 设置值
	 * @param mixed  $ident   
	 * @param string  $accessToken   
	 * @param mixed  $value 
	 * @param integer $time  
	 */
	public function set ($ident,$accessToken,$value,$time = 0)
	{
		// 生成请求标识key
		// 标识key(ident = access_token)
		$identKey = self::PREFIX . $ident;
		// access_token key(ident = access_token)
		$accessTokenKey = self::PREFIX . $accessToken;

		if ($this->has($ident)) {
			// 已经存在则删除
			$this->delete($ident);
		}
		
		$result = $this->redis->setex($identKey,$time,$accessToken);

		if ($result) {
			// 生成后根据标识key值设置存在的请求信息(用户信息)
			if (is_object($value) || is_array($value)) {
				$value = json_encode($value);
			}
			return $this->redis->setex($accessTokenKey,$time,$value);
		}
		return $result;
	}
	/**
	 * 获取设置的标识值
	 * @param  string $accessToken 
	 * @return mixed
	 */
	public function get ($accessToken)
	{
		$accessTokenKey = self::PREFIX . $accessToken;

		// 判断是否过期
		if (!$this->redis->exists($accessTokenKey)) {
			return [];
		}
		// 获取返回值
		return json_decode($this->redis->get($accessTokenKey),true);
	}
	/**
	 * 获取设置的标识是否存在
	 * @param  mixed $ident 
	 * @return bool 
	 */
	public function has ($ident)
	{
		$identKey = self::PREFIX . $ident;
		return $this->redis->exists($identKey);
	}
	/**
	 * 删除标识值
	 * @param  mixed $ident 
	 * @return bool
	 */
	public function delete ($ident)
	{
		$identKey       = self::PREFIX . $ident;
		$accessToken    = $this->redis->get($identKey);
		$result 		= $this->redis->del($identKey);
		
		if ($result) {
			$accessTokenKey = self::PREFIX . $accessToken;
			return $this->redis->del($accessTokenKey);
		}
		return $result;
	}
}