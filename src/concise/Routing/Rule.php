<?php

namespace Concise\Routing;

class Rule
{
	protected $method;

	protected $path;

	protected $groupNumber;

	protected $handle;

	protected $middleware;

	protected $prefix;

	protected $namespace;

	protected $module;

	protected $name;


	public function __construct ($method = 'GET',$path = '',$groupNumber = -1,$handle = null,$middleware = [],$prefix = '',$namespace = '',$module = '',$name = '')
	{
		$this->method 	   = strtoupper($method);
		$this->path 	   = $path;
		$this->groupNumber = $groupNumber;
		$this->handle 	   = $handle;
		$this->middleware  = $middleware;
		$this->prefix      = $prefix;
		$this->namespace   = $namespace;
		$this->module      = $module;
		$this->name        = $name;
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