<?php

namespace Concise\Http\Rest;

use Concise\Container\Container;

class Rest
{
	protected static $instance;

	public static function getInstacne ()
	{
		if (!is_object(static::$instance)) {
			static::$instance = Container::exists('rest') ? Container::get('rest') : (new \Concise\Http\Rest\Repository());
		}
		return static::$instance;
	}

	public function __call ($method,$params)
	{
		return call_user_func_array([static::getInstacne(),$method], $params);
	}

	public static function __callStatic ($method,$params)
	{
		return call_user_func_array([static::getInstacne(),$method], $params);
	}
	
}