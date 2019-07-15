<?php

namespace Concise\Http\Request;

use Concise\Http\Request;
use Concise\Validate\Validator;
use Concise\Container\Container;
use Concise\Foundation\App;
use Concise\Exception\SwooleExit;
use Concise\Exception\ValidatorErrorException;
use Concise\Http\Rest\Rest;

abstract class FormRequest
{
	/**
	 * 验证规则
	 * @var array
	 */
	protected $rule       = [];
	/**
	 * 错误信息
	 * @var array
	 */
	protected $message    = [];
	/**
	 * 验证数据
	 * @var array
	 */
	protected $validData  = [];

	/**
	 * 错误重定向地址
	 * @var string
	 */
	protected $redirectUrl = "refere";

	/**
	 * 是否批量验证
	 * @var boolean
	 */
	protected $batchValidate = false;

	/**
	 * 请求对象
	 * @var object
	 */
	protected $request;

	/**
	 * 验证类型
	 * @var string
	 */
	protected $type = 'api';

	/**
	 * 自定义注册规则
	 * @var array
	 */
	protected $registerRule = [];

	/**
	 * 初始化
	 * @return void
	 */
	public function __construct (Request $request)
	{
		$this->request = $request;
		// 没有规则不做验证
		if (empty($this->rule())) {
			return true;
		}

		$newRule = $this->registerRule();

		$valid  = Validator::make($this->rule(),$this->message())->batch($this->batchValidate);

		if (!empty($newRule)) {
			foreach ($newRule as $rule => $callback) {
				$valid->extend($rule,$callback);
			}
		}

		$this->validData = $this->getValidData();
        $result = $valid->check($this->validData);
        !$result && $this->endRequest($valid->getError());
	}
	
	/**
	 * 获取待验证数据
	 * @return array
	 */
	public function getValidData ()
	{
		return $this->request->param();
	}

	/**
	 * 结束请求
	 * @param  array $errors 
	 * @return void   
	 */
	public function endRequest ($errors = [])
	{
		if ($this->type == 'api') {
			throw new ValidatorErrorException($errors);
		} else {
			throw new ValidatorErrorException([$errors,$this->getRedirectUrl()],'web');
		}
	}
	
	/**
	 * 获取验证规则
	 * @return array
	 */
	public function rule ()
	{
		return $this->rule;
	}

	/**
	 * 获取错误消息
	 * @return array
	 */
	public function message ()
	{
		return $this->message;
	}

	/**
	 * 注册规则
	 * @return array
	 */
	public function registerRule ()
	{
		return $this->registerRule;
	}

	/**
	 * 获取重定向url
	 * @return string
	 */
	protected function getRedirectUrl ()
	{
		if ($this->redirectUrl == 'refere') {
			return $this->request->server('HTTP_REFERER');
		}
		return $this->redirectUrl;
	}
	/**
	 * call
	 * @param  string $method 
	 * @param  array $args   
	 * @return mixed
	 */
	public function __call ($method,$args)
	{
		if (method_exists($this->request, $method)) {
			return call_user_func_array([$this->request,$method],$args);
		}
		throw new \RuntimeException(__CLASS__ . "->" . $method . ' is not exists!');
	}
}