<?php

namespace Concise\Server;

use Concise\Container\Container;
use Concise\Foundation\Config;
use Concise\Foundation\Facade\Env;
use Swoole\Process;


abstract class Server
{
	/**
	 *  server object
	 * @var object
	 */
	protected $server;

	/**
	 * swoole 配置
	 * @var array
	 */
	protected static $swooleConfig = [];

	/**
	 * 事件列表
	 * @var array
	 */
	public $events = [];

	/**
	 * 是否守护进程
	 * @var bool
	 */
	protected $daemon;

	/**
	 * 是否热重启
	 * @var bool
	 */
	protected $heatRestart;


	/**
	 * get
	 * @param  string $key 
	 * @return mixed     
	 */
	public function __get ($key) 
	{
		return $this->$key;
	}

	/**
	 * set
	 * @param string $key   
	 * @param mixed $value 
	 * @return void
	 */
	public function __set ($key,$value) 
	{
		$this->$key = $value;
	}

	/**
	 * 初始化配置
	 * @return void
	 */
	public function init ()
	{
		if (!extension_loaded('swoole')) {
            throw new \BadFunctionCallException('not support: swoole');
        }
		// 配置初始化
		self::$swooleConfig = array_merge(self::$swooleConfig,Config::scope('swoole')->get() ? Config::scope('swoole')->get() : []);
	}
	/**
	 * 启动
	 * @return void
	 */
	abstract public function start();

	/**
	 * 事件绑定
	 * @param  string $event    
	 * @param  mixed $callback 
	 * @return object           
	 */
	public function on ($event,$callback)
	{
		if (strtolower(substr($event, 0,2)) !== 'on') {
			$event =  'on' . $event;
		}
		$this->events[strtolower($event)] = $callback;
		return $this;
	}

	/**
	 * 通知事件
	 * @param  string $event 
	 * @param  array $params 
	 * @return mixed
	 */
	public function notifyEvent ($event,$params)
	{
		if (strtolower(substr($event, 0,2)) !== 'on') {
			$event =  'on' . $event;
		}
		$event = strtolower($event);
		if (isset($this->events[$event])) {
			return call_user_func_array($this->events[$event],$params);
		}
		return $this;
	}

	/**
	 * 设置或获取heatRestart
	 * @param  string $heatRestart 
	 * @return mixed
	 */
	public function heatRestart ($heatRestart = '')
	{
		if (empty($heatRestart) && !is_bool($heatRestart)) return $this->heatRestart;
		$this->heatRestart = $heatRestart;
		return $this;
	}

	/**
	 * 设置或获取Daemon
	 * @param  string $daemon 
	 * @return mixed
	 */
	public function daemon ($daemon = '')
	{
		if (empty($daemon) && !is_bool($daemon)) return $this->daemon;
		$this->daemon = $daemon;
		return $this;
	}

	/**
	 * 设置服务器参数
	 * @return object
	 */
	public function setSettings ()
	{
		$this->server->set([
		 	'worker_num' 	  		=> self::$swooleConfig['worker_num'],
		 	'task_worker_num' 		=> self::$swooleConfig['task_worker_num'],
		 	'enable_static_handler' => self::$swooleConfig['enable_static_handler'],
		 	'document_root'         => empty(self::$swooleConfig['document_root']) 
		 							   ? rtrim(Container::get('env')->get('root_path','/')) . '/public' : self::$swooleConfig['document_root'],
		    'daemonize'             => $this->daemon,

		    'pid_file'              => $this->getPidFilePath()
		 ]);
		return $this;
	}

	/**
	 * 停止服务
	 * @param mixed $callback
	 * @return mixed
	 */
	public function stop ($callback = null)
	{
		$path = $this->getPidFilePath();

		if (!file_exists($path)) {
			 return is_callable($callback) ? $callback(false,"pid file not exists") : false;
		}

		$pid = file_get_contents($path);
		
		if (!Process::kill($pid,0)) {
			 return is_callable($callback) ? $callback(false,"pid not exists") : false;
		}
		Process::kill($pid);

		// 等待5秒
		$time = time();
		while (true) {
			usleep(1000);
			if (!Process::kill($pid,0)) {
				file_exists($path) && unlink($path);
				return is_callable($callback) ? $callback(true,'server stop at ' . date('Y-m-d H:i:s')) : true;
			} else {
				if (time() - $time > 5) {
					is_callable($callback) && $callback(null,"stop server fail.try again");
					break;
				}
			}
			return is_callable($callback) ? $callback(false,"server stop fail") : false;
		}
	}

	/**
	 * 重启服务
	 * @param mixed $callback
	 * @return void
	 */
	public function reload ($callback = null)
	{
		$path = $this->getPidFilePath();

		if (!file_exists($path)) {
			 return is_callable($callback) ? $callback(false,"pid file not exists") : false;
		}
		$sig = SIGUSR1;
		$pid = file_get_contents($path);
		if (!Process::kill($pid,0)) {
			 return is_callable($callback) ? $callback(false,"pid not exists") : false;
		}
		Process::kill($pid,$sig);
		return is_callable($callback) ? $callback(true,"send server reload command at " . date("Y-m-d H:i:s")) : true;
	}

	/**
	 * 获取pid文件路径
	 * @return string
	 */
	protected function getPidFilePath ()
	{
		$path = Env::get('runtime_path',__DIR__) . '/temp/' . md5('concise.swoole.server.pid') . '.pid';
		is_dir(dirname($path)) || mkdir(dirname($path));
		return $path;
	}

	/**
	 * worker启动
	 * @param  object $server   
	 * @param  integer $workerId 
	 * @return mixed          
	 */
	public function onWorkerStart ($server,$workerId)
	{
		 swoole_set_process_name("concise_swoole_server");

         $this->notifyEvent('onWorkerStart',[$server,$workerId]);
         
		 if ($workerId == 0 && $this->heatRestart) {
		 	$path = dirname(Env::get('app_path'));
			Watcher::make()->add($path)->callback(function ($events) use ($server) {
				$server->reload();
			})->start();
         }
	}
	
	/**
	 * 监听请求
	 * @param  object $request
	 * @param  object $response 
	 * @return mixed         
	 */
	public function onRequest ($request, $response)
	{
        // 保存swoole request object
       	!Container::exists('swooleRequest') && Container::set('swooleRequest',$request);
        // 保存swoole response object
       	!Container::exists('swooleResponse') && Container::set('swooleResponse',$response);

        $this->notifyEvent('onRequest',[$request,$response]);
        
        if($request->server['request_uri'] == '/favicon.ico') {
    	    $response->header("Content-Type", "text/html; charset=utf-8");
            $response->status(404);
            $response->end();
            return ;
        }

        $paths = pathinfo($request->server['request_uri']);

        if (!empty(self::$swooleConfig['extension'])) {
        	try {
        		if (array_key_exists($paths['extension'],self::$swooleConfig['content_type'])) {
        			$response->header("Content-Type", self::$swooleConfig['content_type'][$paths['extension']]);
                    $response->status(200);
                    $response->end(file_get_contents(rtrim(self::$swooleConfig['document_root'],'/')  . '/' . ltrim($request->server['request_uri'],'/')));
        		}
        		if (array_key_exists($paths['extension'],self::$swooleConfig['download_type'])) {
    			    $response->header("Content-Type", self::$swooleConfig['download_type'][$paths['extension']]);
                    $response->sendfile(urldecode(rtrim(self::$swooleConfig['document_root'],'/') . '/' . ltrim($request->server['request_uri'],'/')));
        		}
        	} catch (\Exception $e) {
    			$response->header("Content-Type", "text/html; charset=utf-8");
                $response->status(404);
                $response->end();
        	}
        	return ;
        }
        
        if (!empty(self::$swooleConfig['allow_origin'])) {
        	$allowOrigin = self::$swooleConfig['allow_origin'];
        	try {
        		if (is_string($allowOrigin)) {
        			$response->header('Access-Control-Allow-Origin',$allowOrigin);
        		} else if (is_array($allowOrigin)) {
        			$origin = isset($request->server['http_origin']) ? $request->server['http_origin'] : '';
        			in_array($origin, $allowOrigin) && $response->header('Access-Control-Allow-Origin',$origin);
        		}
        	} catch (\Exception $e) {
        		$response->header("Content-Type", "text/html; charset=utf-8");
                $response->status(500);
                $response->end();
        	}
        }


		// 更新php原生超全局变量数据
		$_SERVER = [];
		if (isset($request->server)) {	
			foreach ($request->server as $k => $v) {
				$_SERVER[strtoupper($k)] = $v; 
			}
		}
		if (isset($request->header)) {	
			foreach ($request->header as $k => $v) {
				$_SERVER['HTTP_' . strtoupper($k)] = $v; 
			}
		}
		$_GET = [];
		if (isset($request->get)) {	
			foreach ($request->get as $k => $v) {
				$_GET[$k] = $v; 
			}
		}
		$_POST = [];
		if (isset($request->post)) {	
			foreach ($request->post as $k => $v) {
				$_POST[$k] = $v; 
			}
		}
		// 执行应用响应
		try {
			ob_start();
			$conciseResponse = Container::get('app')->run();
			$conciseResponse->send();
			$content = ob_get_clean();

			$headers = $conciseResponse->getHeader();

			if (isset($headers['Location'])) {
				$response->redirect($headers['Location'],$conciseResponse->getStatusCode());
				return;
			}

			if (!empty($headers)) {
				foreach ($headers as $k => $v) {
					$response->header($k,$v);
				}
			}
            $response->status($conciseResponse->getStatusCode());
	        $response->end($content);
		} catch (\Concise\Exception\SwooleExitException $e) {
		} catch (\Exception $e) {
			try {
				\Concise\Error\Error::getErrorHandle()->exceptionHandle($e);
			} catch (\Concise\Exception\SwooleExitException $e) {	
			} catch (\Exception $e) {
	    	    $response->header("Content-Type", "text/html; charset=utf-8");
				$response->status(500);
				$response->end();
			}
		}
	}
}