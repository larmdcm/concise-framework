<?php

namespace Concise\Exception;

class FileNoExistsException extends Exception
{
	protected $file;

	public function __construct ($file,$message = '')
	{
		$this->file = $file;
		parent::__construct($message);
	}

	public function getFile ()
	{
		return $this->file;
	}
}