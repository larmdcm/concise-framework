<?php

namespace Concise\Foundation\Facade;

use Concise\Facade\Facade;

class Cookie extends Facade
{
	public static function getFacadeAccessor ()
	{
		return "Cookie";
	}
} 