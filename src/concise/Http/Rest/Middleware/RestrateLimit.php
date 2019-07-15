<?php

namespace Concise\Http\Rest\Middleware;

use Concise\Http\Request;
use Concise\Http\Rest\Rest;
use Concise\Http\Rest\Exception\RateLimitOfErrorException;

class RestrateLimit
{
	/**
	 * 频率限定
	 * @param  Request       $request 
	 * @param  \Closure|null $next    
	 * @return mixed           
	 */
	public function handle (Request $request,\Closure $next = null)
	{	
		list($time,$limit) = $this->getRateLimit();
		if (Rest::rateLimit()->check($this->getIdentityValue(),$time,$limit) === false) {
			throw new RateLimitOfErrorException(Rest::rateLimit()->getError(),Rest::rateLimit()->getErrorCode());
		}
		return $next($request);
	}

	/**
	 * 访问频率限制
	 * @return array
	 */
	public function getRateLimit ()
	{
		return [60,100];
	}

	/**
	 * 获取身份鉴权值
	 * @return string
	 */
	public function getIdentityValue ()
	{
		return Rest::auth()->getAccessToken();
	}
}