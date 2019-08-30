<?php

namespace Concise\Swoole\Server;

use Concise\Container\Container;

class WebSocket extends HttpServer
{
	
	/**
	 * 开启服务器
	 * @return void
	 */
	public function start ()
	{
		 // 创建对象
		 $this->server = new \Swoole\WebSocket\Server(self::$swooleConfig['bind_host'],self::$swooleConfig['bind_port']);
		 // 设置对象配置
 		 $this->setSettings();
		  // 设置监听回调函数
		 $this->server->on("workerstart", [$this, 'onWorkerStart']);
         $this->server->on("request", [$this, 'onRequest']);
         $this->server->on("task", [$this, 'onTask']);
         $this->server->on("finish", [$this, 'onFinish']);
         $this->server->on("close", [$this, 'onClose']);
		 // 事件监听
		$this->server->on('open',[$this,'onOpen']);
		$this->server->on('message',[$this,'onMessage']);
		 // 保存server对象
		 Container::set('swooleServer',$this->server);
 		 // 开启http服务器
         $this->server->start();
	}

    /**
     * 监听ws连接事件
     * @param $ws
     * @param $request
     */
    public function onOpen($ws, $request) 
    {
	     $this->notifyEvent('onOpen',[$ws, $request]);
    }
    /**
     * 监听ws消息事件
     * @param $ws
     * @param $frame
     */
    public function onMessage($ws, $frame) 
    {
       $this->notifyEvent('onMessage',[$ws, $frame]);
    }
} 