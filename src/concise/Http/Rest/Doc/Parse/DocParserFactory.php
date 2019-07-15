<?php

namespace  Concise\Http\Rest\Doc\Parse;

class DocParserFactory
{
	protected static $instance;

	public static function __callStatic ($method,$params)
	{
		if (!is_object(static::$instance)) {
			static::$instance = new DocParser();
		}
		return call_user_func_array([static::$instance,$method], $params);
	}
}