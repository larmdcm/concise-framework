<?php

namespace Concise\Config;

use Concise\Exception\ClassNotException;
use Concise\Exception\ErrorException;

class Config
{
	/**
	 * 解析
	 * @var array
	 */
	protected $parses = [];

	/**
	 * 当前解析方法
	 * @var string
	 */
	protected $parseMethod = "default";

	/**
	 * 解析数据缓存
	 * @var boolean
	 */
	protected $parseCache  = true;

	/**
	 * 配置路径
	 * @var string
	 */
	protected $configPath;
	
	/**
	 * 初始化
	 * @param string $configPath 
	 * @return void
	 */
	public function __construct (string $configPath = '')
	{
		$this->configPath = dirname(realpath($configPath)) . DIRECTORY_SEPARATOR . 'config';
	}

	/**
	 * 配置配置路径
	 * @param string $configPath 
	 * @return object
	 */
	public function setConfigPath (string $configPath)
	{
		$this->configPath = $configPath;
		return $this;
	}

	/**
	 * 获取配置路径
	 * @return string
	 */
	public function getConfigPath ()
	{
		return $this->configPath;
	}

	/**
	 * 设置当前作用域
	 * @param  string $scope 
	 * @return mixed
	 */
	public function scope ($scope = 'app')
	{
		return $this->$scope;
	}

	/**
	 * 获取当前调用
	 * @access public
	 * @param  string $scope 
	 * @return object
	 */
	public function __get (string $scope)
	{
		$saveScopeKey = $this->parseMethod . '@' . $scope;

		if (isset($this->parses[$saveScopeKey]))
		{
			return $this->parses[$saveScopeKey];
		}
		$className = $this->parseMethod == 'default' ? '\Concise\Config\Repository' : sprintf("\Concise\Config\Drive\%s",ucfirst($this->parseMethod));
		if (!class_exists($className))
		{
			throw new \ClassNotException("Class Not Exists:" . $className);
		}
		$basePath = $this->configPath;
		$ext      = $this->parseMethod == 'default' ? 'php' : $this->parseMethod;
		$instance = new $className($basePath . '/' . $scope . '.' . $ext);
		if ($this->parseCache) {
			$this->parses[$saveScopeKey] = $instance;
		}
		$this->parseMethod = "default";
		return $instance;
	}
	/**
	 * 调用
	 * @access public
	 * @param  string $method 
	 * @param  array $params 
	 * @return object
	 */
	public function __call ($method,$params)
	{
		$drives = ['default','json','xml','ini'];
		if (!in_array($method,$drives)) {
			if (method_exists($this->default, $method)) {
				return call_user_func_array([$this->default()->app,$method],$params);
			}
			throw new \RuntimeException(__CLASS__ ."->" . $method . " method not exists");
		}
		$this->parseMethod = $method;
		$this->parseCache  = count($params) > 0 ? $params[0] : true;
		return $this;
	}
}