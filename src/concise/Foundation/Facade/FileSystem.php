<?php

namespace Concise\Foundation\Facade;

use Concise\Facade\Facade;

class FileSystem extends Facade
{
	public static function getFacadeAccessor ()
	{
		return "Concise\File\FileSystem";
	}
}