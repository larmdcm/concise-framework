<?php

namespace Concise\Exception;

use Concise\Http\Response;

class HttpResponseException extends \RuntimeException
{

	/**
	 * response
	 * @var Response
	 */
	protected $response;

	public function __construct (Response $response)
	{
		$this->response = $response;
	}

	/**
	 * 获取响应对象
	 * @return Response;
	 */
	public function getResponse ()
	{
		return $this->response;
	}
}