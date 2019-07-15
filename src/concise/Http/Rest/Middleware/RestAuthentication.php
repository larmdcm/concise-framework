<?php

namespace Concise\Http\Rest\Middleware;

use Concise\Http\Request;
use Concise\Http\Rest\Rest;
use Concise\Http\Rest\Exception\AuthenticationOfErrorException;

class RestAuthentication
{
	/**
	 * 鉴权处理
	 * @param  Request       $request 
	 * @param  \Closure|null $next    
	 * @return mixed           
	 */
	public function handle (Request $request,\Closure $next = null)
	{	
		if (Rest::auth()->check() === false) {
			throw new AuthenticationOfErrorException(Rest::auth()->getError(),Rest::auth()->getErrorCode());
		}
		return $next($request);
	}
}