<?php

namespace Concise\Routing\Handle;

use Concise\Routing\Handle;
use Concise\Http\Request;
use Concise\Exception\HttpException;
use Concise\Exception\ClassNotException;
use Concise\Foundation\Config;
use Concise\Container\Container;

class CurrecyHandle extends Handle
{
	public function exec (Request $request)
	{
		list($handle,$className,$module) = $this->buildClass();

		$request->module($module);
		
		$request->controller(substr($handle[0], 0,strlen($handle[0]) - 10));

		$methodName = $handle[1];

		if (!class_exists($className)) {
			throw new ClassNotException("{$className} Class Not Exists",$className);
		}
		
		return Container::set($className,$className)->invokeMethod($className,$methodName,$request->param());
	}

	/**
	 * build class name
	 * @return array              
	 */
	public function buildClass ()
	{
		$handler 	 = $this->handler;
		$rule 		 = $this->rule;
		$groupParams = $this->groupParams;
		if (strpos($handler, "@") === false) {
			throw new HttpException(500,"Route Handle Parse Error");
		}

		$handle = explode('@', $handler);
		$module = '';

		if ($groupParams['module'] !== '') {
			$module = "\\" . $groupParams['module'];
		}
		
		if ($rule->module !== '') {
			$module = "\\" . $rule->module;
		}

		$namespace = '';
		if ($groupParams['namespace'] !== '') {
			$namespace = $groupParams['namespace'];
		}
		if ($rule->namespace !== '') {
			$namespace = $rule->namespace;
		}
		if (empty($namespace)) {
			$namespace = Config::get('app_namespace','App') . "\\Controller";
		}

		$className = sprintf("%s%s\\%s",$namespace,$module,$handle[0]);
		
		return [$handle,$className,$module];
	}
}