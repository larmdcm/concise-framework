<?php

namespace Concise\Exception;

class SwooleExitException extends \RuntimeException
{
	public function __construct ($message = '',$code = 0)
	{
		parent::__construct($message,$code);
	}
}