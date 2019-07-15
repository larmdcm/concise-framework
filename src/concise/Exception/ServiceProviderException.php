<?php

namespace Concise\Exception;

class ServiceProviderException extends \RuntimeException
{
	public function __construct ($message = '',$code = 0)
	{
		parent::__construct($message,$code);
	}
	
	public function __toString ()
	{
		return sprintf("%s:[%s]: %s",__CLASS__,$this->code,$this->message);
	}
}