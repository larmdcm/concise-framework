<?php

namespace Concise\Config\Drive;

use Concise\Config\Repository;

class Xml extends Repository
{
	/**
	 * 解析配置
	 * @param  string $path 
	 * @return mixed
	 */
	public function parse (string $path) : array
	{
        $content = is_file($path) ? simplexml_load_file($path) : simplexml_load_string($path);
        
        $result = (array) $content;
        foreach ($result as $key => $val) {
            if (is_object($val)) {
                $result[$key] = (array) $val;
            }
        }
        return $result;
	}
}