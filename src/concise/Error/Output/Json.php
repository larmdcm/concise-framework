<?php

namespace Concise\Error\Output;

use Concise\Error\Output;
use Concise\Error\OutputInterface;
use Concise\Http\Response;

class Json extends Output implements OutputInterface
{
	/**
	 * 输出错误
	 * @param  integer $errno   
	 * @param  string $errmsg  
	 * @param  string $errfile 
	 * @param  string $errline 
	 * @param  string $exceptionName 
	 * @param  string $title 
	 * @return void
	 */
	public function out ($errno,$errmsg,$errfile,$errline,$exceptionName = '',$title = '')
	{
		return $this->output($this->buildException($errno,$errmsg,$errfile,$errline,$exceptionName),'json');
	}

	/**
	 * 响应http错误
	 * @param  HttpException $e     
	 * @param  string $title 
	 * @return  mixed
	 */
	public function responseHttpError ($e)
	{
		$errors = $this->buildException($e->getCode(),$e->getMessage(),$e->getFile(),$e->getLine(),get_class($e));
		return $this->output($errors,'json',$e->getStatusCode(),$e->getHeaders());
	}

	/**
	 * 响应错误退出
	 * @param  integer $code   
	 * @param  array $header 
	 * @return mixed          
	 */
	public function responseErrorExit ($code = 500,$header = [])
	{	
		$response = Response::create(['errors' => ['message' => $this->message]],'json',$code,$header);
		return $this->response($response);
	}
}