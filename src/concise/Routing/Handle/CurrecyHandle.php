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
		list($handle,$className,$module) = static::buildClass($this->handler,$this->rule,$this->groupParams);

		$request->module($module);
		
		$request->controller(substr($handle[0], 0,strlen($handle[0]) - 10));

		$methodName = $handle[1];

		return Container::set($className,$className)->invokeMethod($className,$methodName,$request->param());
	}

	/**
	 * build class name
	 * @param  string $handler  
	 * @param  object $rule        
	 * @param  array $groupParams 
	 * @return array              
	 */
	public static function buildClass ($handler,$rule,$groupParams)
	{
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
		if (!class_exists($className)) {
			throw new ClassNotException("{$className} Class Not Exists",$className);
		}

		return [$handle,$className,$module];
	}
}