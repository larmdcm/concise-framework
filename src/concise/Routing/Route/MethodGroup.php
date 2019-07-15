<?php

namespace Concise\Routing\Route;
use Concise\Foundation\Arr;

class MethodGroup
{
	/**
	 * 请求方法
	 * @var array
	 */
	protected $methods = [];

	/**
	 * 最新请求列表
	 * @var object
	 */
	protected $newest = [];

	public function __construct ()
	{
		$this->methods['ANY'] = [];
	}
	
	public function attach (Rule $rule)
	{
		$this->newest = [];
		$methods = explode('|',$rule->method);
		foreach ($methods as $method)
		{
			if (!isset($this->methods[$method])) {
				$this->methods[$method] = [];
			}
			$this->methods[$method][] = $rule;
			$this->newest[] = $rule;
		}
		return $this;

	}
	
	public function get (string $name)
	{
		return isset($this->methods[$name]) ? array_merge($this->methods[$name],$this->methods['ANY']) : [];
	}
	
	public function params ($params)
	{
		foreach ($this->newest as $index => $item)
		{
			foreach ($params as $k => $v)
			{
				$this->newest[$index]->$k = $v;	
			}
		}
		return $this;
	}

	public function update ()
	{
		foreach ($this->newest as $item)
		{
			$index = count($this->methods[$item->method]) - 1;
			$this->methods[$item->method][$index] = $item;
		}
		$this->newest = [];
		return $this;
	}

	public function after ()
	{
		$this->methods = [];
		$this->methods['ANY'] = [];
		$this->newest  = [];
	}

	public function getMethods ()
	{
		return $this->methods;
	}
}