<?php

namespace Concise\Foundation;

use Concise\Container\Container;

class Config
{
	protected static $instance;

	public static function __callStatic ($method,$params)
	{
		if (!is_object(static::$instance)) {
			static::$instance = Container::exists('config') ? Container::get('config') : (new \Concise\Config\Config(__DIR__ . '/Config'));
		}
		return call_user_func_array([static::$instance,$method], $params);
	}
}