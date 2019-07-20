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
	 * 当前方法指向
	 * @var integer
	 */
	protected $currentMethodIndex = [];

	public function __construct ()
	{
		$this->methods['ANY'] = [];
		$this->initMethodIndex();
	}
	
	public function attach (Rule $rule)
	{
		$method = strtoupper($rule->method);

		if (!isset($this->methods[$method])) {
			$this->methods[$method] = [];
		}
		$this->methods[$method][] = $rule;
		$this->currentMethodIndex[$method]++;
		return $this;

	}
	
	public function get (string $name)
	{
		return isset($this->methods[$name]) ? array_merge($this->methods[$name],$this->methods['ANY']) : [];
	}
	
	public function after ()
	{
		$this->methods = [];
		$this->methods['ANY'] = [];
		$this->initMethodIndex();
	}


	public function getMehtodCurrentIndex ($method)
	{
		return $this->currentMethodIndex[$method];
	}

	public function setRuleParams ($method,$params)
	{
		$index = $this->getMehtodCurrentIndex($method);

		foreach ($params as $k => $v)
		{
			$this->methods[$method][$index]->$k = $v;	
		}
		return $this;
	}

	public function getCurrentRule ($method)
	{
		$index = $this->getMehtodCurrentIndex($method);
		return $this->methods[$method][$index];
	}

	protected function initMethodIndex ()
	{
		$this->currentMethodIndex = [
			'ANY'     => -1,
			'GET'     => -1,
			'POST' 	  => -1,
			'DELETE'  => -1,
			'OPTIONS' => -1,
			'PATCH'   => -1,
			'PUT'     => -1
		];
	}
}