<?php

namespace Concise\Routing\Route\Handle;

use Concise\Routing\Route\Handle;
use Concise\Http\Request;
use Concise\Ioc\Ioc;

class CallbackHandle extends Handle
{
	public function exec (Request $request)
	{
		return call_user_func_array($this->handler,Ioc::getFuncParams($this->handler,$this->route->routeParams));
	}
}