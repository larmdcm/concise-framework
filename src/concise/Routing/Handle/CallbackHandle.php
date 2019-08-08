<?php

namespace Concise\Routing\Handle;

use Concise\Routing\Handle;
use Concise\Http\Request;
use Concise\Container\Container;

class CallbackHandle extends Handle
{
	public function exec (Request $request)
	{
		return Container::get($this->handler,$this->route->routeParams,false);
	}
}