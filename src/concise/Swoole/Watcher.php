<?php

namespace Concise\Swoole;

use Concise\Foundation\Facade\FileSystem;

class Watcher
{	
	/**
	 * 监听句柄
	 * @var integer
	 */
	protected $inotify;

	/**
	 * 回调
	 * @var Closure
	 */
	protected $callback;

	/**
	 * 单例对象
	 * @var object
	 */
	protected static $instance;
	/**
	 * 创建类实例
	 * @return object
	 */
	public static function make ()
	{
		if (is_null(static::$instance)) {
			static::$instance = new static();
		}
		return static::$instance;
	}

	/**
	 * 初始化
	 * @return void
	 */
	public function __construct ()
	{
		if (!extension_loaded('inotify')) {
            throw new \BadFunctionCallException('not support: inotify');
        }
        $this->inotify = inotify_init();
	}

	/**
	 * 添加
	 * @param mixed $path 
	 * @return object
	 */
	public function add ($path,$mask = IN_MODIFY | IN_DELETE | IN_CREATE)
	{
		FileSystem::directorys($path,function ($directory) use ($mask) {
			inotify_add_watch($this->inotify, $directory, $mask);
		});
		inotify_add_watch($this->inotify, $path, $mask);
		return $this;
	}

	/**
	 * 设置回调
	 * @param   Closure  $callback 
	 * @return object           
	 */
	public function callback ($callback)
	{
		if (is_callable($callback)) {
			$this->callback = $callback;
		}
		return $this;
	}

	/**
	 * get inotify
	 * @return mixed
	 */
	public function getInotify ()
	{
		return $this->inotify;
	}
	/**
	 * 启动监听
	 * @return void
	 */
	public function start ()
	{
		if (extension_loaded('swoole')) {
		    swoole_event_add($this->inotify, function ($fd) {
			    $events = inotify_read($fd);
		        $events && call_user_func_array($this->callback, [$events]);
			});
		} else {
			 while (true) {
	             $events = inotify_read($this->inotify);
	             $events && call_user_func_array($this->callback, [$events]);
	         }
		}
	}
}