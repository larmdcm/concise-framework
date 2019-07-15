<?php

namespace Concise\Exception;

class ConsoleException extends \RuntimeException
{
	protected $command;

	public function __construct ($message = '',$command = '')
	{
		parent::__construct($message);
		$this->command = $command;
	}
	/**
	 * 获取执行命令
	 * @return string
	 */
	public function getCommand ()
	{
		return $this->command;
	}
	public function __toString ()
	{
		return sprintf("%s:[%s]: %s->%s",__CLASS__,$this->code,$this->message,$this->command);
	}
}