<?php

namespace Concise\Routing\Route;

use Concise\Foundation\Config;

class Group
{
	protected $defaultParams = [
		'namespace'   => 'App',
		'module'      => '',
		'middleware'  => '',
		'prefix'      => ''
	];

	protected $groupNumber = -1;

	protected $params = [];

	protected $isOpen = false;

	public function __construct ()
	{
		$this->defaultParams['namespace'] = Config::get('app_namespace','App');
	}

	public function create ($params)
	{
		$this->isOpen = true;
		$this->params[++$this->groupNumber] = array_merge($this->defaultParams,$params);
		return $this;
	}
	
	public function after ($callback)
	{
		is_callable($callback) && $callback();
		$this->isOpen = false;
	}

	public function getParams ($groupNumber)
	{
		return $this->params[$groupNumber];
	}

	public function getGroupNumber ()
	{
		return $this->isOpen ? $this->groupNumber : -1;
	}

	public function getDefaultParams ()
	{
		return $this->defaultParams;
	}
}