<?php

namespace Concise\Foundation\Facade;

use Concise\Facade\Facade;

class Router extends Facade
{
	public static function getFacadeAccessor ()
	{
		return "Router";
	}
}