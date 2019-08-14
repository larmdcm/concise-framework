<?php

namespace Concise\Foundation\Provider;

class MiddlewareServiceProvider
{
	protected $namespace = [
		
	];

	private $defaultNamespace = [
		'Concise\Http\Middleware',
		'Concise\Http\Rest\Middleware',
	];

	protected $middlewareGroups = [
		//
	];

	protected $routeMiddleware = [
		//
	];

	public function getNamespace ()
	{
		return array_merge($this->namespace,$this->defaultNamespace);
	}

	public function getMiddlewareGroup ($name)
	{
		return isset($this->middlewareGroups[$name]) ? is_array($this->middlewareGroups[$name]) ? $this->middlewareGroups[$name] : [$this->middlewareGroups[$name]] : null;
	}

	public function getRouteMiddleware ($name)
	{
		return isset($this->routeMiddleware[$name]) ? $this->routeMiddleware[$name] : null;
	}
}