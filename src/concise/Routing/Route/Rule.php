<?php

namespace Concise\Routing\Route;

class Rule
{
	protected $method;

	protected $rule;

	protected $groupNumber;

	protected $handle;

	protected $middleware;

	protected $prefix;

	protected $namespace;

	protected $module;

	protected $doc;


	public function __construct ($method = 'GET',$rule = '',$groupNumber = -1,$handle = null,$middleware = [],$prefix = '',$namespace = '',$module = '',$doc = '')
	{
		$this->method 	   = strtoupper($method);
		$this->rule 	   = $rule;
		$this->groupNumber = $groupNumber;
		$this->handle 	   = $handle;
		$this->middleware  = $middleware;
		$this->prefix      = $prefix;
		$this->namespace   = $namespace;
		$this->module      = $module;
		$this->doc         = $doc;
	}

	public function __get ($key) 
	{
		return $this->$key;
	}
	public function __set ($key,$value) 
	{
		$this->$key = $value;
	}
}