<?php

namespace Concise\Http;

use Concise\Foundation\Config;
use Concise\Foundation\App;

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
	 * middleware service provider
	 * @var MiddlewareServiceProvider
	 */
	protected $service;

	/**
	 * 初始化
	 * @return void
	 */
	public function __construct ()
	{
		$this->service = App::$serviceContainer->get('middlewareService');
	}

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
		$middlewareGroups = $this->service->getMiddlewareGroup($middleware);
		if (!is_null($middlewareGroups)) {
			array_walk($middlewareGroups, function ($middleware) {
				$this->add($middleware);
			});
			return;
		}
		$routeMiddleware = $this->service->getRouteMiddleware($middleware);
		$middleware = is_null($routeMiddleware) ? $this->build($middleware) : $this->build($routeMiddleware);

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
		$middlewares = explode("@",$middleware);

		$middleware  = $middlewares[0];
		$method      = count($middlewares) > 1 ? $middlewares[1] : 'handle';

		if (!class_exists($middleware)) {
			$namespaces = array_filter($this->service->getNamespace(),function ($namespace) use ($middleware) {
				return class_exists(sprintf("\\%s\\%s",$namespace,$middleware));
			});
			if (empty($namespaces)) {
				return null;
			}
			$middleware = sprintf("\\%s\\%s",$namespaces[0],$middleware);
		}
		return [new $middleware,$method];
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