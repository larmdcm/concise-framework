<?php

namespace Concise\Exception;

class ClassNotException extends Exception
{
	protected $class;
	/**
	 * 构造方法初始化
	 * @param string $message 
	 * @param string $class   
	 */
	public function __construct ($message = '',$class = '')
	{
		$this->class = $class;
		parent::__construct($message);
	}
	/**
	 * 获取类名
	 * @return string
	 */
	public function getClass ()
	{
		return $this->class;
	}
	public function __toString ()
	{
		return sprintf("%s:[%s]: %s->%s",__CLASS__,$this->code,$this->message,$this->class);
	}
}