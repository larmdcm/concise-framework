<?php

namespace Concise\Exception;

class TokenMismatchException extends HttpException
{
	public function __construct ($message = 'Token verification error',$statusCode = 500)
	{
		parent::__construct($statusCode,$message);
	}
}