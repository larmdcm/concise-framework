<?php

namespace Concise\Foundation;

use Concise\Container\Container;
use Concise\Http\Response;
use Concise\Error\Error;
use Concise\Foundation\Facade\Route;
use Concise\Exception\HttpException;
use Concise\Exception\HttpResponseException;
use Concise\Exception\ValidatorErrorException;
use Concise\View\View;
use Concise\Ioc\ServiceContainer;
use Concise\Foundation\Config;
use Concise\Env;

class App
{	
	/**
	 * 版本号
	 */
	const VERSION = 'dev-master';

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
	 * 运行目录路径
	 * @var string
	 */
	public $runPath;
	
	/**
	 * 根目录路径
	 * @var string
	 */
	public $rootPath;

	/**
	 * 环境变量对象
	 * @var object
	 */
	public $env;

	/**
	 * 是否初始化
	 * @var boolean
	 */
	protected $isInit;

	/**
	 * 初始化
	 * @return void
	 */
	public function initialize ($runPath = '')
	{

		$this->runPath  = realpath($runPath);
		$this->rootPath = dirname($this->runPath);

		$envs = [
			'base_path'    => $this->rootPath,
			'app_path'     => $this->rootPath . '/app',
			'config_path'  => $this->rootPath . '/config',
			'route_path'   => $this->rootPath . '/route',
			'runtime_path' => $this->rootPath . '/runtime',
			'view_path'    => $this->rootPath . '/views'
		];
		$this->env = Container::get('env');

		array_walk($envs,function ($value,$key) {
			if (is_null($this->env->get($key))) {
				$this->env->set($key,$value);
			}
		});

		$envFile = $this->rootPath . DIRECTORY_SEPARATOR . '.env';
		if (is_file($envFile)) {
			$this->env->load($envFile);
		}

		static::$container = Container::getInstance();

		static::$serviceContainer = ServiceContainer::getInstance($this->getServiceContainerConfig());

		static::$debug = static::$container->make('config')->get('app_debug',false);

		if (is_null(static::$mod)) {
			static::$mod   = request()->isCli() ? 'cli' : 'web';
		}
		// 配置config路径
		Config::setConfigPath(Env::get('config_path'));
		// 初始化日期组件
		Container::get('date',['dateTimeZone' => Config::get('date_time_zone')]);

		$this->isInit = true;

		return $this;
	}

	/**
	 * 绑定路由
	 * @param  $name string 名称
	 * @return mixed
	 */
	public function buildRoute ($name = 'mapRoute')
	{
		if ($this->getServiceContainer()->exists('mapCsrfToken')) {
			$this->getServiceContainer('mapCsrfToken')->map();
		}
		if ($this->getServiceContainer()->exists($name)) {
			return $this->getServiceContainer($name)->map();
		}
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
		is_null($this->isInit) && $this->initialize();
		$this->buildRoute();
		
		try {
			$result = Route::dispatch();
			
			if (is_object($result) && $result instanceof Response) {
				return $result;
			}

			if (is_object($result) && $result instanceof View) {
				$result = $result->fetch();
			}
		} catch (HttpException $e) {
			throw $e;
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
	public function getServiceContainer ($service = null)
	{
		if (is_null($service)) {
			return static::$serviceContainer;
		}
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