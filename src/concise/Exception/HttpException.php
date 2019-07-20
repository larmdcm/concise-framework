<?php

namespace Concise\Exception;

class HttpException extends \RuntimeException
{
	protected $statusCode;

	protected $headers;

	public function __construct ($statusCode,$message = '',$headers = [],$code = 0)
	{
		$this->statusCode = $statusCode;
		
		$this->headers = $headers;
		parent::__construct($message,$code);
	}

	public function getStatusCode ()
	{
		return $this->statusCode;
	}

	public function getHeaders ()
	{
		return $this->headers;
	}

	public function __toString ()
	{
		return sprintf("%s:[%s]: %s",__CLASS__,$this->code,$this->message);
	}
}