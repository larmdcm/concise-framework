<?php

namespace Concise\Http;

use Concise\Foundation\Config;

class Middleware
{
	/**
	 * queue
	 * @var array
	 */
	protected $queue;

	/**
	 * next 
	 * @var Closure
	 */
	protected $next;

	/**
	 * import
	 * @param  mixed $middleware 
	 * @return mixed
	 */
	public function import ($middleware = [])
	{
		if (is_array($middleware)) {
			foreach ($middleware as $v) {
				$this->add($v);
			}
		} else {
			$this->add($v);
		}
		return $this;
	}
	/**
	 * add
	 * @param mixed $middleware 
	 * @return mixed
	 */
	public function add ($middleware)
	{	
		if (is_null($middleware)) {
			return ;
		}
		$middleware = $this->build($middleware);
		if ($middleware) {
			$this->queue[] = $middleware;
		}
	}
	/**
	 * 获取all
	 * @return array
	 */
	public function all ()
	{
		return $this->queue;
	}

	/**
	 * 绑定middleware
	 * @param  mixed $middleware 
	 * @return mixed   
	 */
	public function build ($middleware)
	{
		if (empty($middleware)) {
			return null;
		}
		$middleware  = explode(':', $middleware);
		$classNames  =  ["\\" . Config::get('app_namespace','App') . "\\Middleware\\" . ucfirst($middleware[0]),
						"\\Concise\\Http\\Middleware\\" . ucfirst($middleware[0]),
						"\\Concise\\Http\\Rest\\Middleware\\" . ucfirst($middleware[0])];

		$method = count($middleware) > 1 ? $middleware[1] : 'handle';
		foreach ($classNames as $className) {
			if (class_exists($className)) {
				return [new $className,$method];
			}
		}
		if (class_exists($middleware[0])) {
			return [new $middleware[0],$method];
		}
		return null;		
	}

	public function next ($next)
	{
		$this->next = $next;
		return $this;
	}

	public function resolve ()
	{
		return function (Request $request) {

			if (empty($this->queue)) {
				$next = $this->next;
				return $next($request);
			}
			$middleware = array_shift($this->queue);

			if (null === $middleware) {
	            throw new \InvalidArgumentException('The queue was exhausted, with no response returned');
	        }

	        $response = call_user_func_array($middleware,[$request,$this->resolve()]);

	        return $response;
		};

	}

	public function dispatch (Request $request)
	{
		return call_user_func($this->resolve(),$request);
	}
}