<?php

namespace Concise\Error\Output;

use Concise\Error\Output;
use Concise\Error\OutputInterface;
use Concise\Http\Response;

class Html extends Output implements OutputInterface
{
	/**
	 * 输出错误
	 * @param  integer $errno   
	 * @param  string $errmsg  
	 * @param  string $errfile 
	 * @param  string $errline 
	 * @param  string $exceptionName 
	 * @return void
	 */
	public function out ($errno,$errmsg,$errfile,$errline,$exceptionName = '')
	{
		$title = $this->title;
		$traces = debug_backtrace();
		ob_start();
		include __DIR__ . '/Resources/error.php';
		$content = ob_get_clean();
		return $this->output($content);
	}
	/**
	 * 响应http错误
	 * @param  HttpException $e
	 * @return  mixed
	 */
	public function responseHttpError ($e)
	{
		$errno = $e->getCode();
		$errmsg = $e->getMessage();
		$errfile = $e->getFile();
		$errline = $e->getLine();
		$exceptionName = get_class($e);
		$title = $this->title;
		$traces = debug_backtrace();
		ob_start();
		include __DIR__ . '/Resources/error.php';
		$content = ob_get_clean();
		return $this->output($content,'',$e->getStatusCode(),$e->getHeaders());
	}

	/**
	 * 响应错误退出
	 * @param  integer $code   
	 * @param  array $header 
	 * @return mixed          
	 */
	public function responseErrorExit ($code = 500,$header = [])
	{	
		$title   = $this->title;
		$message = $this->message;
		ob_start();
		include __DIR__ . '/Resources/termination.php';
		$content = ob_get_clean();
		$response = Response::create($content,'',$code,$header);
		return $this->response($response);
	}
}