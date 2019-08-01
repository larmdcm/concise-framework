<?php

namespace Concise\Foundation\Facade;

use Concise\Facade\Facade;

class Session extends Facade
{
	public static function getFacadeAccessor ()
	{
		return "Session";
	}
} 