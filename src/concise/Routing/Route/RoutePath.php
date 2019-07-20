<?php
namespace Concise\Routing\Route;

class RoutePath
{
	protected $path;

	protected $vars;

	protected $optVars;

	protected $routePath;

	public function vars ($vars)
	{
		$this->vars = $vars;
		return $this;
	}

	public function path ($path)
	{
		$this->path = $path;
		return $this;
	}

	public function optVars ($optVars)
	{	
		$this->optVars = $optVars;
		return $this;
	}

	public function routePath ($routePath)
	{
		$this->routePath = $routePath;
		return $this;
	}

	public function parse () : array
	{
		$vars    	= $this->vars;
		$optVars 	= $this->optVars;
		$count   	= count($vars);
		$paths   	= explode('/', trim($this->path,'/'));
		$routePaths = explode('/', trim($this->routePath,'/'));
		$countRoutePaths = count($routePaths);
		
		if (count($paths) < $countRoutePaths) {
			return ['path' => '','params' => []];
		}

		$routePaths = [];

		for ($i = 0; $i < $countRoutePaths; $i++)
		{
			$routePaths[] = $paths[0];
			array_splice($paths, 0,1);
		}

		if (count($paths) < $count) {
			return ['path' => '','params' => []];
		}

		$params = [];

		foreach ($paths as $v)
		{
			$params[] = $v;
		}
		$path   = implode('/', $routePaths);
		return ['path' => substr($path,0,1) === "/" ? $path : "/" . $path,'params' => $params];
	}
}