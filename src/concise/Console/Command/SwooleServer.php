<?php

namespace Concise\Console\Command;

use Concise\Console\Console;
use Concise\Swoole\Event\Event as SwooleEvent;
use Concise\Exception\ClassNotException;
use Concise\Foundation\App;

class SwooleServer extends Console
{
	use SwooleEvent;

	/**
	 * 服务器对象
	 * @var object
	 */
	protected $server;

	/**
	 * 是否守候进程
	 * @var bool
	 */
	protected $daemon;

	/**
	 * 是否热重启
	 * @var bool
	 */
	protected $heatRestart;

	/**
	 * handle
	 * @return void
	 */
	public function handle ()
	{
		// concise make:web_socket --d --r 
		
		$args = $this->args;

		$this->daemon = isset($args['--d']) ? true : false;
		$this->heatRestart    = isset($args['--r']) ? true : false;

		$make = isset($args['make']) ? $args['make'] : 'web_socket';

		$make = preg_replace_callback('/([-_]+([a-z]{1}))/i',function($matches){ return strtoupper($matches[2]); },$make);
		$serverHandle = '\Concise\Swoole\Server\\' . ucfirst($make);
		if (!class_exists($serverHandle)) {
			throw new ClassNotException("{$serverHandle} Class Not Exists",$serverHandle);
		}
		
		$this->server = new $serverHandle();

		if (isset($args['stop'])) return $this->server->stop(function ($result,$message) {
			return $this->out($message);
		});
		if (isset($args['reload'])) return $this->server->reload(function ($result,$message) {
			return $this->out($message);
		});
		App::$mod = 'swoole';
		// 设置参数
		$this->server->heatRestart($this->heatRestart)->daemon($this->daemon);
		// 设置回调
		$this->server->on("onWorkerStart", [$this, 'onWorkerStart']);
        $this->server->on("onRequest", [$this, 'onRequest']);
        $this->server->on("onTask", [$this, 'onTask']);
        $this->server->on("onFinish", [$this, 'onFinish']);
        $this->server->on("onClose", [$this, 'onClose']);
		$this->server->on('onOpen',[$this,'onOpen']);
		$this->server->on('onMessage',[$this,'onMessage']);
		// 启动服务
		$this->server->start();
	}
}