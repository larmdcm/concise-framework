<?php

namespace Concise\Error\Handle;

use Concise\Error\ErrorHandleInterface;
use Concise\Container\Container;
use Concise\Foundation\Config;
use Concise\Exception\ClassNotException;
use Concise\Log\Log;

class ErrorHandle implements ErrorHandleInterface
{
	/**
	 * 错误标题
	 * @var string
	 */
	protected $title;

	/**
	 * 错误消息
	 * @var string
	 */
	protected $message;

	/**
	 * 自定义错误处理
	 * @var mixed
	 */
	protected $customErrorHandle;

	/**
	 * 注册错误处理
	 * @return bool     
	 */
	public function register ()
	{
		set_error_handler(function ($errno,$errmsg,$errfile,$errline) {
			return $this->errorHandle($errno,$errmsg,$errfile,$errline);
		});
		register_shutdown_function(function () {
			return $this->fatalHandle();
		});
		set_exception_handler(function ($exception) {
			return $this->exceptionHandle($exception);
		});
		return true;
	}

	/**
	 * call
	 * @param  string $method 
	 * @param  array $params 
	 * @return mixed    
	 */
	public function __call ($method,$params)
	{	
		$instuct = substr($method, 0,3);
		if ($instuct == 'set') {
			$attr = lcfirst(substr($method,-(strlen($method) - 3)));
			$this->$attr = $params[0];
		}
	}

	/**
	 * 错误处理
	 * @param  integer $errno   
	 * @param  string $errmsg  
	 * @param  string $errfile 
	 * @param  string $errline 
	 * @return mixed          
	 */
	public function errorHandle ($errno,$errmsg,$errfile,$errline)
	{
		return $this->handle($errno,$errmsg,$errfile,$errline);
	}
	/**
	 * 致命错误处理
	 * @return mixed
	 */
	public function fatalHandle ()
	{
		$e = error_get_last();
		if (isset($e)) {
			return $this->handle($e['type'],$e['message'],$e['file'],$e['line']);
		}
	}
	/**
	 * 异常处理
	 * @param  object $exception 
	 * @return mixed         
	 */
	public function exceptionHandle ($exception)
	{
		return $this->handle($exception->getCode(),$exception->getMessage(),$exception->getFile(),$exception->getLine(),get_class($exception));
	}
	/**
	 * 错误处理
	 * @param  integer $errno   
	 * @param  string $errmsg  
	 * @param  string $errfile 
	 * @param  string $errline 
	 * @param string $exceptionName 
	 * @return void
	 */
	public function handle ($errno,$errmsg,$errfile,$errline,$exceptionName = '')
	{
		$append = !empty($exceptionName) ? ['Exception' => $exceptionName] : [];

        Log::write(sprintf("%s@%s %s",$errmsg,$errfile,$errline),'error',$append);

		if (($response = $this->customHandle($errno,$errmsg,$errfile,$errline,$exceptionName)) !== false) {
			return $this->createOutput()->response($response);
		}
		$this->createOutput()->out($errno,$errmsg,$errfile,$errline,$exceptionName);
	}

	/**
	 * 创建输出对象
	 * @return object
	 */
	public function createOutput ()
	{
		$output = Container::get('request')->isAjax() ? 'Json' : 'Html';

		if (!empty(Config::get('error_handle.output',''))) {
			$output = Config::get('error_handle.output');
		}

		$outputClass = "\\Concise\\Error\\Output\\" . ucfirst($output);

		if (!class_exists($outputClass)) {
			throw new ClassNotException("Class {$outputClass} Not Exists",$outputClass);
		}
		return new $outputClass($this->title,$this->message);
	}

	/**
	 * 响应Http错误
	 * @param  HttpException $response 
	 * @return mixed        
	 */
	public function responseHttpError ($e)
	{
		
        Log::write(sprintf("%s@%s %s",$e->getMessage(),$e->getFile(),$e->getLine()),'error',['Exception' => get_class($e)]);

		if (($response = $this->customHandle($e->getCode(),$e->getMessage(),$e->getFile(),$e->getLine(),get_class($e),$e)) !== false) {
			return $this->createOutput()->response($response);
		}
		return $this->createOutput()->responseHttpError($e);
	}

	/**
	 * 自定义错误
	 * @param  integer $errno         
	 * @param  string $errmsg        
	 * @param  string $errfile       
	 * @param  string $errline       
	 * @param  string $exceptionName 
	 * @param  object $e             
	 * @return mixed                
	 */
	protected function customHandle ($errno,$errmsg,$errfile,$errline,$exceptionName = '',$e = null) 
	{
		if (empty($this->customErrorHandle)) {
			return false;
		}
		if (is_string($this->customErrorHandle)) {
			if (class_exists($this->customErrorHandle)) {
				$customHandle = $this->customErrorHandle;
				$response     = (new $customHandle)->handle($errno,$errmsg,$errfile,$errline,$exceptionName);
				return $response;
			} else {
				throw new ClassNotException("Class {$this->customErrorHandle} Not Exists",$outputClass);
			}
		}
		if (is_callable($this->customErrorHandle)) {
			return call_user_func_array($this->customErrorHandle, [$errno,$errmsg,$errfile,$errline,$exceptionName]);
		}
		return false;
	}
}