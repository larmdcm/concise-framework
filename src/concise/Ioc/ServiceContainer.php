<?php

namespace Concise\Ioc;

use Concise\Exception\ServiceProviderException;

class ServiceContainer
{
	/**
	 * 服务配置
	 * @var array
	 */
	private $providers = [];

	/**
	 * 单例对象
	 * @var null
	 */
	private static $instance = null;
	
	// 构造方法初始化
	private function __construct (array $providers = []) {
		$this->providers = $providers;
	}

	private function __clone () {}

	// 构造单例对象
	public static function getInstance (array $providers = [])
	{
		if (is_null(self::$instance) || !self::$instance instanceof self) {
			self::$instance = new self($providers);
		}
		return self::$instance;
	}
	/**
	 * 获取服务提供对象
	 * @param  string $serviceName 
	 * @return object
	 */
	public function get ($serviceName)
	{
		try {
			return $this->build($serviceName);
		} catch (\Exception $e) {
			throw new ServiceProviderException($e->getMessage());
		}	
	}
	/**
	 * 建造服务提供对象
	 * @param  string $serviceName 
	 * @return object
	 */
	private function build ($serviceName)
	{
		if (!isset($this->providers[$serviceName])) {
			throw new ServiceProviderException("服务提供者:[ " . $serviceName . " ]未注册到服务容器中");
		}
		$service = $this->providers[$serviceName];

		if (!isset($service['class'])) {
			throw new ServiceProviderException("服务提供者:[ " . $serviceName . " ] class 未配置");
		}
		$params = isset($service['arguments']) ? $service['arguments'] : [];
		$serviceProvider = ServiceProviderBuilder::buildServiceProvider($this,$serviceName,$service['class'],$params);

		if (isset($service['singleton']) && $service['singleton']) {
			$this->providers[$serviceName]['class'] = $serviceProvider;
		}
		return $serviceProvider;
	}
	/**
	 * 设置服务提供对象属性
	 * @param string  $serviceName 
	 * @param string  $service     
	 * @param array  $arguments   
	 * @param boolean $singleton   
	 * @return object 
	 */
	public function set ($serviceName,$service,$arguments = [],$singleton = false)
	{
		$params = empty($arguments) ? $this->providers[$serviceName]['arguments'] : $arguments;
		$this->providers[$serviceName] = ['class' => $service,'arguments' => $params,'singleton' => $singleton];
		return $this;
	}

	/**
	 * 获取服务提供者是否注册到容器
	 * @param  string $serviceName 
	 * @return bool              
	 */
	public function exists ($serviceName)
	{
		return isset($this->providers[$serviceName]);
	}
}