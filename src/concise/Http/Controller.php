<?php

namespace Concise\Http;

use Concise\Http\Rest\Rest;

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
			return call_user_func_array([Rest::getInstacne(),$method],$args);
		}
		throw new \RuntimeException(__CLASS__ . "->" . $method . ' is not exists!');
	}
}