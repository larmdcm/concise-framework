<?php

namespace Concise\Server\ServerHandle;

use Concise\Server\Server as BaseServer;
use Concise\Container\Container;

class HttpServer extends BaseServer
{
	/**
	 * 初始化
	 * @return void
	 */
	public function __construct ()
	{
		$this->init();
	}

	/**
	 * 开启服务器
	 * @return void
	 */
	public function start ()
	{
		 // 创建对象
		 $this->server = new \Swoole\Http\Server(self::$swooleConfig['bind_host'],self::$swooleConfig['bind_port']);
		 // 设置对象配置
		 $this->setSettings();
		  // 设置监听回调函数
		 $this->server->on("workerstart", [$this, 'onWorkerStart']);
         $this->server->on("request", [$this, 'onRequest']);
         $this->server->on("task", [$this, 'onTask']);
         $this->server->on("finish", [$this, 'onFinish']);
         $this->server->on("close", [$this, 'onClose']);
		 // 保存server对象
		 Container::set('swooleServer',$this->server);
 		 // 开启http服务器
         $this->server->start();
	}
	/**
	 * task任务进入
	 * @param  object $server   
	 * @param  integer $taskId   
	 * @param  integer $workerId 
	 * @param  array $data     
	 * @return mixed      
	 */
	public function onTask ($server, $taskId, $workerId, $data)
	{
         $this->notifyEvent('onTask',[$server, $taskId, $workerId, $data]);
	}
	/**
	 * 任务处理完毕返回
	 * @param  object $server 
	 * @param  integer $taskId 
	 * @param  array $data   
	 * @return mixed         
	 */
	public function onFinish ($server, $taskId, $data)
	{
         $this->notifyEvent('onFinish',[$server, $taskId, $data]);
	}
	/**
	 * 连接关闭
	 * @param  object $ws 
	 * @param  integer $fd 
	 * @return mixed    
	 */
	public function onClose ($ws,$fd)
	{
         $this->notifyEvent('onClose',[$ws,$fd]);
	}
}