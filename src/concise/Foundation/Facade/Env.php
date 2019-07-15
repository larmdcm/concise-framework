<?php

namespace Concise\Foundation\Facade;

use Concise\Facade\Facade;

class Env extends Facade
{
	public static function getFacadeAccessor ()
	{
		return "Env";
	}
}