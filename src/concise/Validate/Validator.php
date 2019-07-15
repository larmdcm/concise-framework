<?php

namespace Concise\Validate;

class Validator
{
	/**
	 * 待验证数据
	 * @var array
	 */
	protected $data    = [];
	/**
	 * 验证规则
	 * @var array
	 */
	protected $rule    = [];
	/**
	 * 错误消息
	 * @var array
	 */
	protected $message = [
		'required' => '%s为必填字段',
		'between'  => '%s字段范围必须大于等于%s为小于等于%s位',
		'length'   => '%s字段长度必须大于等于%s为小于等于%s位',
		'email'	   => '%s字段格式错误',
		'max'	   => '%s字段最大值为%s',
		'min'	   => '%s字段最小值为%s',
		'in'	   => '%s字段值必须在%s范围中',
		'confirm'  => '%s字段和%s字段值不一致',
		'integer'  => '%s字段不是一个整数',
		'mobile'   => '%s字段号码格式错误'
	];

	/**
	 * 错误消息
	 * @var array
	 */
	protected $error   = [];

	/**
	 * 是否验证全部数据
	 * @var boolean
	 */
	protected $batch   = false;

	/**
	 * 自定义验证处理
	 * @var array
	 */
	protected $customValidHandle = [];

	// 构造方法初始化
	public function __construct ($rule = [],$message = [])
	{
		$this->rule($rule)->message($message);
	}
	/**
	 * make
	 * @param  array $rule    
	 * @param  array $message 
	 * @return object
	 */
	public static function make ($rule = [],$message = [])
	{
		return new self($rule,$message);
	}
	/**
	 * 验证
	 * @param  array $data 
	 * @return bool      
	 */
	public function check ($data = [])
	{
		$this->error = [];

		$this->data  = $data ?: [];

		foreach ($this->rule as $k => $v) {
			$item  = isset($this->data[$k]) ? $this->data[$k] : '';
			$rules = explode('|', $v);
			foreach ($rules as $rule) {
				$items  = explode(':',$rule);
				$params = [$item];
				$args   = isset($items[1]) ? explode(',',$items[1]) : [];
				$params = array_merge($params,$args);
				$result = $this->callMethod($items[0],$params);
				if ($result) {
					continue;
				}
				array_unshift($args,$k);
				array_unshift($args,$this->getMessage($k,$items[0]));
				$message = call_user_func_array('sprintf',$args);
				array_push($this->error, $message);
				if (!$this->batch) {
					return false;
				}
			}
		}
		return empty($this->error) ? true : false;
	}
	/**
	 * 设置验证规则
	 * @param array $rule 
	 * @return object
	 */
	public function rule ($rule = [])
	{
		if (!empty($rule)) {
			$this->rule = $rule;
		}
		return $this;
	}
	/**
	 * 设置错误消息
	 * @param array $message  
	 * @return object
	 */
	public function message ($message = [])
	{
		if (!empty($message)) {
			$this->message = array_merge($this->message,$message);
		}
		return $this;
	}
	/**
	 * 返回错误消息
	 * @return array
	 */
	public function getError ()
	{
		return $this->error;
	}
	/**
	 * 设置是否验证全部数据
	 * @return object
	 */
	public function batch ($batch = false)
	{
		$this->batch = $batch;
		return $this;
	}
	/**
	 * 获取执行方法名称
	 * @return string
	 */
	protected function getValidMethod ($method)
	{
		return sprintf("valid%s",ucfirst($method));
	}
	/**
	 * 扩展自定义验证处理
	 * @param  string $name    
	 * @param  object $callback
	 * @return object        
	 */
	public function extend ($name,$callback)
	{
		if (isset($this->customValidHandle[$name])) {
			$this->throwError(sprintf("%s该验证规则已经存在!",$name));
		}
		
		if (!is_callable($callback)) {
			$this->throwError('callback is not a callable!');
		}
		$this->customValidHandle[$this->getValidMethod($name)] = $callback;
		return $this;
	}
	/**
	 * 调用方法
	 * @param  string $method 
	 * @param  array $params 
	 * @return mixed
	 */
	protected function callMethod ($method,$params = [])
	{
		$method = $this->getValidMethod($method);
		if (method_exists($this,$method)) {
			return call_user_func_array([$this,$method],$params);
		}
		if (isset($this->customValidHandle[$method]) && $this->customValidHandle[$method] instanceof \Closure) {
			return call_user_func_array($this->customValidHandle[$method],$params);
		}
		return $this->throwError(__CLASS__ ."->" . $method . " method not exists!");
	}
	/**
	 * 抛出错误
	 * @param  string $message 
	 * @return void     
	 */
	protected function throwError ($message)
	{
		throw new \RuntimeException($message);
	}
	/**
	 * 获取错误消息
	 * @param  string $name 
	 * @param  string $rule 
	 * @return string
	 */
	protected function getMessage ($name,$rule)
	{
		return isset($this->message[sprintf("%s.%s",$name,$rule)]) ? $this->message[sprintf("%s.%s",$name,$rule)] : $this->message[$rule] ?: '';
	}
	/**
	 * 验证是否为空
	 * @param  string $value 
	 * @return bool
	 */
	public function validRequired ($value)
	{
		$value = trim($value);
		return $value === '0' || !empty($value);
	}	
	/**
	 * 验证范围
	 * @param  string $value
	 * @param  integer $min  
	 * @param  integer $max  
	 * @return bool
	 */
	public function validBetween ($value,$min,$max)
	{
		return $value ? $value >= $min && $value <= $max : true;
	}
	/**
	 * 验证长度
	 * @param  string $value 
	 * @param  integer $min   
	 * @param  integer $max   
	 * @return bool       
	 */
	public function validLength ($value,$min,$max = '')
	{
		if  (empty($max))
		{
			$max = $min;
		}
		return  $value ? mb_strlen($value,'utf-8') >= $min &&  mb_strlen($value,'utf-8') <= $max: true;
	}
	/**
	 * 验证邮箱格式
	 * @param  string $value
	 * @return bool 
	 */
	public function validEmail ($value)
	{
		return  $value ? preg_match("/^([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+@([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/",$value) ? true : false : true;
	}
	/**
	 * 验证最大值
	 * @param  string $value 
	 * @param  integer $max   
	 * @return bool       
	 */
	public function validMax ($value,$max)
	{
		return $value ? $value <= $max : true;
	}
	/**
	 * 验证最小值
	 * @param  string $value 
	 * @param  integer $min   
	 * @return bool      
	 */
	public function validMin ($value,$min)
	{
		return $value ? $value >= $min : true;
	}
	/**
	 * 验证值是否存在指定内容中
	 * @param  string $value 
	 * @param  array $in    
	 * @return bool      
	 */
	public function validIn ($value,$in)
	{
		return $value ? in_array($value,explode(',',$in)) : true;
	}
	/**
	 * 验证两个字段的值是否一致
	 * @param  string $value 
	 * @param  string $field 
	 * @return bool        
	 */
	public function validConfirm ($value,$field)
	{
		$confirmData = !empty($this->data[$field]) ? $this->data[$field] : '';
		return $value ? $confirmData == $value : true;
	}
	/**
	 * 验证一个数是否为整数
	 * @param  integer $value 
	 * @return bool    
	 */
	public function validInteger ($value)
	{
		return $value ? preg_match('/^[0-9]*$/',$value) : true;
	}
	/**
	 * 验证手机号码格式是否正确
	 * @param  integer $value 
	 * @return bool    
	 */
	public function validMobile ($value)
	{
		return $value ? preg_match('/^((13[0-9])|(14[5,7])|(15[0-3,5-9])|(17[0,3,5-8])|(18[0-9])|166|198|199|(147))\d{8}$/',$value) : true;
	}
}