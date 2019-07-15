<?php

namespace Concise\Exception;

class TemplateNotFoundException extends \RuntimeException
{
	public $template;

	public function __construct ($message = '',$template = '',$code = 0)
	{
		parent::__construct($message,$code);
		$this->template = $template;
	}

	public function getTemplate ()
	{
		return $this->template;
	}

	public function __toString ()
	{
		return sprintf("%s:[%s]: %s->%s",__CLASS__,$this->code,$this->message,$this->template);
	}
}