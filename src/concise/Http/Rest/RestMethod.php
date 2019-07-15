<?php

namespace Concise\Http\Rest;

use Concise\Http\Response;

trait RestMethod
{	

	/**
	 * 返回追加参数
	 * @var array
	 */
	protected $resultAppend = [];

	/**
	 * 返回响应
	 * @param  array  $data       
	 * @param  integer $code       
	 * @param  string  $msg        
	 * @param  integer $statusCode 
	 * @param  array  $header     
	 * @return Response              
	 */
	public function result ($data = [],$code = 0,$msg = '',$statusCode = 200,array $header = [])
	{
		$result = [
			'code' => $code,
			'msg'  => $msg,
			'data' => $data,
			'date' => time()
		];
		$result = array_merge($result,$this->resultAppend);
		$this->resultAppend = [];
		return Response::create($result,'json',$statusCode,[])->header($header);
	}

	/**
	 * 追加参数
	 * @param  mixed $name  
	 * @param  mixed $value 
	 * @return object
	 */
	public function append ($name,$value = '')
	{
		if (is_array($name)) {
			$this->resultAppend = array_merge($this->resultAppend,$name);
		} else {
			$this->resultAppend[$name] = $value;
		}
		return $this;
	}

	/**
	 * 请求成功
	 * @param  array  $data       
	 * @param  integer $code       
	 * @param  string  $msg        
	 * @param  array  $header     
	 * @return Response              
	 */
	public function correct ($data = [],$code = 0,$msg = '',array $header = [])
	{
		return $this->result($data,$code,$msg,200,$header);
	}

	/**
	 * 创建成功
	 * @param  array  $data       
	 * @param  integer $code       
	 * @param  string  $msg        
	 * @param  array  $header     
	 * @return Response              
	 */
	public function created ($data = [],$code = 0,$msg = '',array $header = [])
	{
		return $this->result($data,$code,$msg,201,$header);
	}

	/**
	 * 更新成功
	 * @param  array  $data       
	 * @param  integer $code       
	 * @param  string  $msg        
	 * @param  array  $header     
	 * @return Response              
	 */
	public function updaed ($data = [],$code = 0,$msg = '',array $header = [])
	{
		return $this->result($data,$code,$msg,202,$header);
	}

	/**
	 * 删除成功
	 * @param  array  $data       
	 * @param  integer $code       
	 * @param  string  $msg        
	 * @param  array  $header     
	 * @return Response              
	 */
	public function deleted ($data = [],$code = 0,$msg = '',array $header = [])
	{
		return $this->result($data,$code,$msg,204,$header);
	}

	/**
	 * 请求错误
	 * @param  array  $data       
	 * @param  integer $code       
	 * @param  string  $msg        
	 * @param  array  $header     
	 * @return Response              
	 */
	public function fail ($data = [],$code = 0,$msg = '',array $header = [])
	{
		return $this->result($data,$code,$msg,400,$header);
	}

	/**
	 * 授权错误
	 * @param  array  $data       
	 * @param  integer $code       
	 * @param  string  $msg        
	 * @param  array  $header     
	 * @return Response              
	 */
	public function authError ($data = [],$code = 0,$msg = '',array $header = [])
	{
		return $this->result($data,$code,$msg,401,$header);
	}

	/**
	 * 无权访问
	 * @param  array  $data       
	 * @param  integer $code       
	 * @param  string  $msg        
	 * @param  array  $header     
	 * @return Response              
	 */
	public function accessError ($data = [],$code = 0,$msg = '',array $header = [])
	{
		return $this->result($data,$code,$msg,403,$header);
	}

	/**
	 * 请求资源不存在
	 * @param  array  $data       
	 * @param  integer $code       
	 * @param  string  $msg        
	 * @param  array  $header     
	 * @return Response              
	 */
	public function notFound ($data = [],$code = 0,$msg = '',array $header = [])
	{
		return $this->result($data,$code,$msg,404,$header);
	}

	/**
	 * 文件上传错误
	 * @param  array  $data       
	 * @param  integer $code       
	 * @param  string  $msg        
	 * @param  array  $header     
	 * @return Response              
	 */
	public function fileUpError($data = [],$code = 0,$msg = '',array $header = [])
	{
		return $this->result($data,$code,$msg,413,$header);
	}

	/**
	 * 服务器错误
	 * @param  array  $data       
	 * @param  integer $code       
	 * @param  string  $msg        
	 * @param  array  $header     
	 * @return Response              
	 */
	public function error ($data = [],$code = 0,$msg = '',array $header = [])
	{
		return $this->result($data,$code,$msg,500,$header);
	}
}