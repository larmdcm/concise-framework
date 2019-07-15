<?php

namespace Concise\Log;

use Concise\Container\Container;

class Log
{
	protected static $instance;

	public static function __callStatic ($method,$params)
	{
		if (!is_object(static::$instance)) {
			static::$instance = Container::exists('log') ? Container::get('log') : (new \Concise\Log\Repository());
		}
		return call_user_func_array([static::$instance,$method], $params);
	}
}