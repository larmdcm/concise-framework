<?php

namespace Concise\Foundation\Facade;

use Concise\Facade\Facade;

class Route extends Facade
{
	public static function getFacadeAccessor ()
	{
		return "Route";
	}
}