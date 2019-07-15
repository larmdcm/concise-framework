<?php

namespace Concise\Curl;

use Concise\Exception\ClassNotException;

class Request
{
	public static function create ($method,...$params)
	{
		return static::getRequestInstance($method,$params);
	}

	public static function getRequestInstance ($method,$params)
	{
		$className = "Concise\Curl\Request\\" . ucfirst(strtolower($method));
		if (!class_exists($className)) {
			throw new ClassNotException($method . " Request Method Class Not Exists",$className);
		}
		return (new \ReflectionClass($className))->newInstanceArgs($params);;
	}

	public function __call ($method,$params)
	{
		return static::getRequestInstance($method,$params);
	}
	
	public static function __callStatic ($method,$params)
	{
		return static::getRequestInstance($method,$params);
	}
}