<?php

namespace Concise\Http\Response;

use Concise\Http\Response;
use Concise\Foundation\Config;

class Json extends Response
{
	protected $contentType = 'application/json';

	/**
	 * 输出json格式数据
	 * @param  array $data 
	 * @return mixed
	 */
	public function output ($data)
	{
		try {
			$data = json_encode($data,JSON_UNESCAPED_UNICODE);
			if (false === $data) {
				throw new \InvalidArgumentException(json_last_error_msg());
			}
			return Config::get('json_format',false) ? json_encode(json_decode($data),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT) : $data;
		} catch (\Exception $e) {
			throw $e;
		}
	}
}