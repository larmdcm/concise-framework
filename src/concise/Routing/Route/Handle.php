<?php

namespace Concise\Routing\Route;

use Concise\Http\Middleware;
use Concise\Http\Request;

class Handle
{
	protected $rule;

	protected $route;

	protected $handler;

	protected $groupParams;


	public function __construct ($rule,$route)
	{
		$this->rule      = $rule;
		$this->route   	 = $route;
		$this->handler   = $rule->handle;

		if ($this->rule->groupNumber != -1) {
			$this->groupParams = $this->route->group->getParams($this->rule->groupNumber);
		} else {
			$this->groupParams = $this->route->group->getDefaultParams();
		}
	}


	public static function make ($rule,$route)
	{
		$class = is_callable($rule->handle) ? "\Concise\Routing\Route\Handle\CallbackHandle" : "\Concise\Routing\Route\Handle\CurrecyHandle";

		return class_exists($class) ? new $class($rule,$route) : new static($rule,$route);
	}

	public function prev ()
	{
		$middlewares = $this->middleware();

		try {
			$response = (new Middleware())->import($middlewares)->next(function (Request $request) {
				 return $this->exec($request); 
			})->dispatch($this->route->request);
		} catch (\InvalidArgumentException $e) {
			throw $e;
		}
		return $response;
	}

	public function middleware ()
	{
		if (is_array($this->groupParams['middleware'])) {
			$groupMiddleware = $this->groupParams['middleware'];
		} else {
			$groupMiddleware = !empty($this->groupParams['middleware']) ? [$this->groupParams['middleware']] : [];
		}
		return array_merge($this->rule->middleware,$groupMiddleware);
	}

	public function exec (Request $request)
	{
		throw new \RuntimeException("Route Not Found");
	}
}