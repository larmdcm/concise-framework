<?php

namespace Concise\Http\Rest\Exception;

use Concise\Exception\HttpResponseException;
use Concise\Http\Response;
use Concise\Http\Rest\Rest;

class BadRequestException extends HttpResponseException
{
	public function __construct ($message = '')
	{
		parent::__construct(Rest::fail([],400,$message));
	}
}