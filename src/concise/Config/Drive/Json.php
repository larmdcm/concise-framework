<?php

namespace Concise\Config\Drive;

use Concise\Config\Repository;

class Json extends Repository
{
	/**
	 * 解析配置
	 * @param  string $path 
	 * @return mixed
	 */
	public function parse (string $path) : array
	{
		if (is_file($path)) {
            $path = file_get_contents($path);
        }
        $result = json_decode($path, true);
        return $result;
	}
}