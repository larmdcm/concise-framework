<?php

namespace Concise\Http\Middleware;

use Concise\Http\Request;
use Concise\Exception\TokenMismatchException;

class VerifyCsrfToken
{
	/**
	 * 忽略过滤的url
	 * @var array
	 */
	protected $except = [

	];

	/**
	 * token key
	 * @var string
	 */
	protected $token = '__token';

	/**
	 * 处理
	 * @param  Request       $request 
	 * @param  \Closure|null $next    
	 * @return mixed       
	 */
	public function handle (Request $request,\Closure $next = null)
	{
		if ($this->matchExcept($request) || $this->matchToken($request)) {
			return $next($request);
		}
		throw new TokenMismatchException;
	}

	/**
	 * 验证token
	 * @param  Request $request 
	 * @return bool   
	 */
	protected function matchToken ($request)
	{
		if (!$request->isPost() && !$request->isAjax()) {
			return true;
		}
		$token = $request->param($this->token,'');
		if (empty($token) || !session()->has($this->token)) {
			return false;
		}
		$csrfToken = session($this->token);
		session()->delete($this->token);
		return $csrfToken === $token;
	}

	/**
	 * 验证是否过滤路由
	 * @param Request $request 
	 * @return mixed
	 */
	protected function matchExcept ($request)
	{
		if (!$request->isPost() && !$request->isAjax()) {
			return true;
		}

		$path = '/' . ltrim( $request->path(),'/');

		foreach ($this->except as $except) {
			$except = '/' . ltrim($except,'/');
			if ($except === $path) {
				return true;
			}
			if (strpos($except,"*") !== false) {
				$excepts = explode("*",$except);
				$except  = $excepts[0];
				$len     = strlen($except);
				if (substr($except,0,$len) === substr($path,0,$len)) {
					return true;
				}
			}
		}
		return false;
	}
}