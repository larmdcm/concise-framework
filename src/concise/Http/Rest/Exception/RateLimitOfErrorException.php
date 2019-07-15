<?php

namespace Concise\Http\Rest\Exception;

use Concise\Exception\HttpResponseException;
use Concise\Http\Response;
use Concise\Http\Rest\Rest;

class RateLimitOfErrorException extends HttpResponseException
{
	public function __construct ($message = '',$errorCode = 401)
	{
		parent::__construct(Rest::authError([],$errorCode,$message));
	}
}