<?php

namespace Concise\Log;

use Monolog\Logger; 
use Monolog\Handler\StreamHandler; 
use Monolog\Handler\FirePHPHandler;
use Concise\Foundation\Config;
use Concise\Foundation\Facade\Env;

class Repository
{
	/**
	 * 配置
	 * @var array
	 */
	protected $config = [];

	/**
	 * Logger
	 * @var array
	 */
	protected $logger = [];

	/**
	 * Channel
	 * @var string
	 */
	protected $channel = 'app';

	/**
	 * stream
	 * @var object
	 */
	protected $stream;

	/**
	 * firephp
	 * @var object
	 */
	protected $firephp;

	// 构造方法初始化
	public function __construct ()
	{
		$this->config  =  [
			 // 是否开启日志记录
		 	 'is_record'   => true,
			 	 // 目录格式
		 	 'dir_format'  => function () {
		 	 	 return date('Y-m');
		 	 },
		 	 // 文件名格式
		 	 'file_format' => function () {
		 	 	 return date('d');
		 	 },
		 	 'ext' 		   => 'log'
		];
		$this->config  = array_merge($this->config,Config::get('log',[]));

		$this->stream  = new StreamHandler($this->getLogPath(Env::get('runtime_path',__DIR__) . '/logs'), Logger::DEBUG);

		$this->firephp = new FirePHPHandler();
		
		$this->createLogger();
		
	}
	/**
	 * 写日志
	 * @param  string $content 内容
	 * @param  string $level   
	 * @param  array @appends 
	 * @param  string $channel 
	 * @return mixed         
	 */
	public function write ($content,$level = 'Debug',$appends = [],$channel = 'app')
	{
		if (!$this->config['is_record']) return false;
		$this->channel = $channel ?: $this->channel;
		if (!is_object($this->logger[$channel])) {
			$this->createLogger($channel);
		}
		$method = lcfirst($level);
		$args   = array_merge(['Runtime' => $this->getRuntime() . 's'],$appends);
		return method_exists($this->logger[$channel],$method) && call_user_func_array([$this->logger[$channel],$method],[$content,$args]);
	}
	/**
	 * 紧急状况，比如系统挂掉
	 * @param  string $content 
	 * @param  array $appends 
	 * @param  string $channel 
	 * @return mixed          
	 */
	public function emergency ($content,$appends = [],$channel = 'app')
	{
		return $this->write($content,'emergency',$appends,$channel);
	}
	/**
	 * 需要立即采取行动的问题
	 * @param  string $content 
	 * @param  array $appends 
	 * @param  string $channel 
	 * @return mixed          
	 */
	public function alert ($content,$appends = [],$channel = 'app')
	{
		return $this->write($content,'alert',$appends,$channel);
	}
	/**
	 * 严重问题
	 * @param  string $content 
	 * @param  array $appends 
	 * @param  string $channel 
	 * @return mixed          
	 */
	public function critical ($content,$appends = [],$channel = 'app')
	{
		return $this->write($content,'critical',$appends,$channel);
	}
	/**
	 * 运行时错误
	 * @param  string $content 
	 * @param  array $appends 
	 * @param  string $channel 
	 * @return mixed          
	 */
	public function error ($content,$appends = [],$channel = 'app')
	{
		return $this->write($content,'error',$appends,$channel);
	}
	/**
	 * /警告但不是错误
	 * @param  string $content 
	 * @param  array $appends 
	 * @param  string $channel 
	 * @return mixed          
	 */
	public function warning ($content,$appends = [],$channel = 'app')
	{
		return $this->write($content,'warning',$appends,$channel);
	}
	/**
	 * 普通但值得注意的事件
	 * @param  string $content 
	 * @param  array $appends 
	 * @param  string $channel 
	 * @return mixed          
	 */
	public function notice ($content,$appends = [],$channel = 'app')
	{
		return $this->write($content,'notice',$appends,$channel);
	}
	/**
	 * 感兴趣的事件
	 * @param  string $content 
	 * @param  array $appends 
	 * @param  string $channel 
	 * @return mixed          
	 */
	public function info ($content,$appends = [],$channel = 'app')
	{
		return $this->write($content,'info',$appends,$channel);
	}
	/**
	 * 详细的调试信息
	 * @param  string $content 
	 * @param  array $appends 
	 * @param  string $channel 
	 * @return mixed          
	 */
	public function debug ($content,$appends = [],$channel = 'app')
	{
		return $this->write($content,'debug',$appends,$channel);
	}
	/**
	 * 获取日志记录路径
	 * @param string $basePath
	 * @return string
	 */
	public function getLogPath ($basePath = '')
	{	
		is_dir(dirname($basePath)) || mkdir(dirname($basePath));
		is_dir($basePath) || mkdir($basePath);
		$dirFormat = $this->config['dir_format'];
		$path = $basePath . '/' . $dirFormat();
		is_dir($path) || mkdir($path);
		$fielFormat  = $this->config['file_format'];
		$path = $path . '/' . $this->channel .'_' . $fielFormat() . '.' . $this->config['ext'];
		is_file($path) || touch($path);
		return $path;
	}

	/**
	 * 创建Loger对象
	 * @param string $channel
	 * @return void
	 */
	private function createLogger ($channel = '')
	{
		$this->channel = $channel ?: $this->channel;
		$this->logger[$this->channel] = new Logger($this->channel);
		$this->logger[$this->channel]->pushHandler($this->stream);
		$this->logger[$this->channel]->pushHandler($this->firephp);
	}
	/**
	 * 设置handle对象
	 * @param mixed $handler 
	 * @return object
	 */
	public function setHandler ($handler)
	{
		$this->logger[$this->channel]->pushHandler( $handler instanceof \Closure ? $handler() : $handler);
		return $this;
	}
	/**
	 * 设置format
	 * @param mixed $format
	 * @return  object
	 */
	public function setFormat ($format)
	{
		$format = $format instanceof \Closure ? $format() : $format;
		$this->stream->setFormatter($format);
		return $this;
	}
	/**
	 * 获取当前使用
	 * @return string
	 */
	public function getChannel ()
	{
		return $this->channel;
	}
	/**
	 * 设置当前使用
	 * @param string $channel 
	 * @return object
	 */
	public function setChannel ($channel)
	{
		$this->channel = $channel;
		return $this;
	}
	/**
	 * 获取结束运行时间
	 * @return floot
	 */
	public function getRuntime ()
	{
		if (!defined('CONCISE_START')) {
			return 0;
		} 
		$end = microtime(true);
		return round($end - CONCISE_START,3);
	}
	/**
	 * 返回Monlog对象
	 * @return object
	 */
	public function getMonlog ()
	{
		return $this->logger[$this->channel];
	}
	
	public function __call ($method,$params)
	{
		$logger = $this->logger[$this->channel];
		if (method_exists($logger,$methods)) {
			return call_user_func_array([$logger,$methods],$params);
		}
		throw new \BadMethodCallException('method not exists:' . __CLASS__ . '->' . $method);
	}
}