<?php

namespace Concise\Http;

class Controller
{
	/**
	 * call
	 * @param  string $method 
	 * @param  array $args   
	 * @return mixed
	 */
	public function __call ($method,$args)
	{
		if (method_exists(Rest::getInstacne(), $method)) {
			return call_user_func_array([\Concise\Http\Rest\Rest::getInstacne(),$method],$args);
		}
		throw new \RuntimeException(__CLASS__ . "->" . $method . ' is not exists!');
	}
}