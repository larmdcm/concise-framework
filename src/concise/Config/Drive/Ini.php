<?php

namespace Concise\Config\Drive;

use Concise\Config\Repository;

class Ini extends Repository
{
	/**
	 * 解析配置
	 * @param  string $path 
	 * @return mixed
	 */
	public function parse (string $path) : array
	{
		return is_file($path) ? parse_ini_file($path, true) : parse_ini_string($path, true);
	}
}