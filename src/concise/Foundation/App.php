<?php

namespace Concise\Foundation;

use Concise\Container\Container;
use Concise\Http\Response;
use Concise\Error\Error;
use Concise\Http\Request;
use Router;
use Concise\Exception\HttpException;
use Concise\Exception\HttpResponseException;
use Concise\Exception\ValidatorErrorException;
use Concise\View\View;
use Concise\Ioc\ServiceContainer;

class App
{	
	/**
	 * 版本号
	 */
	const VERSION = '1.0.0';

	/**
	 * 容器对象
	 * @var object
	 */
	public static $container;

	/**
	 * 服务提供者
	 * @var object
	 */
	public static $serviceContainer;

	/**
	 * 是否为调试模式
	 * @var bool
	 */
	public static $debug;

	/**
	 * 运行模式
	 * @var string
	 */
	public static $mod;


	/**
	 * 初始化
	 * @return void
	 */
	public function __construct (Request $request)
	{
		static::$container = Container::getInstance();

		static::$serviceContainer = ServiceContainer::getInstance($this->getServiceContainerConfig());

		static::$debug = static::$container->make('config')->get('app_debug',false);

		if (is_null(static::$mod)) {
			static::$mod   = $request->isCli() ? 'cli' : 'web';
		}
	}

	/**
	 * 绑定路由
	 * @return void
	 */
	public function buildRoute ()
	{
		$routeFile = static::$container->get('env')->get('route_path') . '/route.php';

		if (!is_file($routeFile)) {
			throw new \RuntimeException("route file not exists");
		}

		include $routeFile;
	}

	/**
	 * 运行
	 * @return mixed
	 */
	public function run ()
	{
		$this->buildRoute();
		
		try {
			$result = Router::dispatch();
			
			if (is_object($result) && $result instanceof Response) {
				return $result;
			}

			if (is_object($result) && $result instanceof View) {
				$result = $result->fetch();
			}
		} catch (HttpException $e) {
			Error::responseHttpError($e);
		} catch (HttpResponseException $e) {
			return $e->getResponse();
		} catch (ValidatorErrorException $e) {
			return $e->end();
		}
		$returnType = is_object($result) || is_array($result) ? Config::get('return_type','json') : '';
		return Response::create($result,$returnType,200,[]);
	}

	/**
	 * 获取服务容器已经绑定的对象
	 * @param  string  $name      
	 * @param  array  $params    
	 * @param  boolean $singleton 
	 * @return object             
	 */
	public function getContainer ($name,$params = [],$singleton = true)
	{
		return static::$container->make($name,$params,$singleton);
	}
	/**
	 * 获取服务提供者提供的对象
	 * @param  string $service 
	 * @return object          
	 */
	public function getServiceContainer ($service)
	{
		return static::$serviceContainer->get($service);
	}

	/**
	 * 获取服务提供者默认配置
	 * @return array
	 */
	protected function getServiceContainerConfig ()
	{
		$default = require __DIR__ . '/Config/provider.php';
		return array_merge($default,Config::get('provider',[]));
	}
}