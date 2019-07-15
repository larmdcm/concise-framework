<?php

namespace Concise\Http\Rest;

use Concise\Http\Rest\Auth\Auth;
use Concise\Http\Rest\RateLimit\RateLimit;

class Repository
{
	use RestMethod;

	/**
	 * 获取鉴权对象
	 * @return object
	 */
	public function auth ()
	{
		return Auth::getInstacne();
	}

	/**
	 * 获取限流检测对象
	 * @return object
	 */
	public function rateLimit ()
	{
		return RateLimit::getInstacne();
	}
}