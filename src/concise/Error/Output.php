<?php

namespace Concise\Error;

use Concise\Foundation\App;
use Concise\Http\Response;
use Concise\Container\Container;
use Concise\Foundation\Config;

class Output implements OutputInterface
{
	protected $title;

	protected $message;

	public function __construct ($title = '',$message = '')
	{
		$this->title   = $title;
		$this->message = $message;
	}

	/**
	 * 输出
	 * @param  mixed  $data   
	 * @param  string  $type   
	 * @param  integer $code   
	 * @param  array  $header 
	 * @return mixed          
	 */
	public function output ($data,$type = '',$code = 500,$header = [])
	{
		if (!Config::get('app_debug',false)) {
			return $this->responseErrorExit($code,$header);
		}
		$response = Response::create($data,$type,$code,$header);
		return $this->response($response);
	}

	/**
	 * buildException
	 * @param  integer $errno         
	 * @param  string $errmsg        
	 * @param  string $errfile       
	 * @param  string $errline       
	 * @param  string $exceptionName 
	 * @return array         
	 */
	public function buildException ($errno,$errmsg,$errfile,$errline,$exceptionName)
	{
		return [
			'errors' => [
				'file'      => $errfile,
				'line'      => $errline,
				'message'   => $errmsg,
				'errno'     => $errno,
				'exception' => $exceptionName
			]
		];
	}

	/**
	 * 响应
	 * @param  Response $response 
	 * @return void    
	 */
	public function response (Response $response)
	{
		if (!$response instanceof Response) {
			return;
		}

		if (App::$mod !== 'swoole') {
			exit($response->send());
		}
		if (Container::exists('swooleResponse')) {
			$swooleResponse = Container::get('swooleResponse');
			$swooleResponse->header('Content-Type',$response->getContentType());
	        $swooleResponse->status($response->getStatusCode());
		    $swooleResponse->end($response->getContent());
		    throw new \Concise\Exception\SwooleExitException();
		}
	}
}