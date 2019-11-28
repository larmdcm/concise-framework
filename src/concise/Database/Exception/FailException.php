<?php

namespace Concise\Database\Exception;

class FailException extends SQLException
{
	public function __construct ($message = 'Failed to query one',$code = 0)
	{
		parent::__construct($message,$code);
	}
}