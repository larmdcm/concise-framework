<?php

namespace Concise\Ioc;

use Concise\Exception\ServiceProviderException;

class ServiceProviderBuilder 
{

	/**
	 * 绑定创建服务
	 * @param  object $serviceContainer 
	 * @param  string $serviceName      
	 * @param  string|object $serviceClass     
	 * @param  array $arguments        
	 * @return object                 
	 */
	public static function buildServiceProvider ($serviceContainer,$serviceName,$serviceClass,array $arguments = [])
	{
		// 闭包函数执行返回
		if ($serviceContainer instanceof \Closure) {
			return $serviceContainer();
		}
		// 对象直接返回
		if (is_object($serviceClass)) {
			return $serviceClass;
		}
		try {
		   $reflection = new \ReflectionClass($serviceClass);
		   array_walk($arguments, function (&$param) use ($serviceContainer) {
		   		if (strpos($param, "@") !== false) {
		   			$param = $serviceContainer->get(ltrim($param,'@'));
		   		}
		   });
		   return $reflection->newInstanceArgs($arguments);
		} catch (\Exception $e) {
			throw new ServiceProviderException("服务提供者:[ " . $serviceName . ' ]实例化失败,请检查服务配置!');
		}
	}
}