<?php

namespace Concise\Http\Rest\Auth;

use Concise\Foundation\Config;
use Concise\Container\Container;

class Auth
{
	/**
	 * instance
	 * @var Auth
	 */
	protected static $instance;

	/**
	 * handler instance
	 * @var object
	 */
	protected $handler;

	/**
	 * 用户信息
	 * @var object
	 */
	protected $user;

	/**
	 * 额外传递数据
	 * @var array
	 */
	protected $params;

	/**
	 * 错误
	 * @var mixed
	 */
	protected $error;

	/**
	 * 默认配置
	 * @var array
	 */
	protected $config = [];

	/**
	 * 错误码
	 * @var integer
	 */
	protected $errorCode;

	/**
	 * 构造方法初始化
	 * @return void
	 */
	public function __construct ()
	{
		$this->config = [
			// 选择驱动
			'drive' 	   		   => 'redis',
			// 过期时间
			'expire_time'  		   => 3600,
			// token名称
			'token_name'   		   => 'token',
			// 过期时间名称
			'expire_time_name'     => 'expire_time',
			// 用户数据调用模型
			'user_model'           => "App\\Model\\User",
			// 模型标识key
			 'user_model_key'      => "id",
			// token生成函数
			'token_generate_func'  => function ($mixed) {
				return \Concise\Hash\Hash::make($mixed . Container::get('request')->server('REQUEST_TIME') . md5(mt_rand(111111,999999)))->get();
			},
			// token获取错误
			'token_get_error_msg'  	       => 'token get error.',
			// token验证错误码
			'token_get_error_code'  	   => '402',
			// token验证错误消息
			'token_valid_error_msg'        => 'token valid error.',
			// token验证错误码
			'token_valid_error_code'       => '403',

			'connect_options' => []
		];

		$config = Config::scope('api')->get('auth',[]);
		$this->config = array_merge($this->config,is_null($config) ? [] : $config);

		$drive = ucfirst(empty($this->config['drive']) ? 'redis' : $this->config['drive']);
		$className = "Concise\\Http\\Rest\\Auth\\Drive\\" . ucfirst($drive);
		if (!class_exists($className)) {
			throw new \RuntimeException("Api Redis Drive not exists!");
		}
		$this->handler = new $className(isset($this->config['connect_options']) ? $this->config['connect_options'] : []);
	}

	/**
	 * 获取单例对象
	 * @return mixed
	 */
	public static function getInstacne ()
	{
		if (is_null(static::$instance)) {
			static::$instance = new static();
		}
		return static::$instance;
	}

	/**
	 * 保存
	 * @param  object $user  
	 * @param  array $params 
	 * @param  mixed $mixed 
	 * @return mixed        
	 */
	public function save ($user = [],$params = [],$mixed = '')
	{
		$accessToken = $this->config['token_generate_func']($mixed);
		$result = $this->handler->set($mixed,$accessToken,[
			'user' 	 => $user,
			'params' => $params
		],$this->config['expire_time']);
		return [
			$this->config['token_name'] 	  => $accessToken,
			$this->config['expire_time_name'] => $this->config['expire_time']
		];
	}
	/**
	 * 验证
	 * @param string $accessToken
	 * @return mixed
	 */
	public function check ($accessToken = '')
	{
		if (empty($accessToken)) {
			$accessToken = $this->getAccessToken();
		}

		if (empty($accessToken)) {
			$this->error     = $this->config['token_get_error_msg'];
			$this->errorCode = $this->config['token_get_error_code'];
			return false;
		}
		$data = $this->handler->get($accessToken);
		if (empty($data)) {
			$this->error     = $this->config['token_valid_error_msg'];
			$this->errorCode = $this->config['token_valid_error_code'];
			return false;
		}
		$this->user   = $data['user'];
		$this->params = $data['params'];
		return true;
	}

	/**
	 * 获取用户数据
	 * @return object
	 */
	public function user ()
	{
		$userModel = new $this->config['user_model'];
		return $userModel->find($this->user[$this->config['user_model_key']]);
	}
	
	/**
	 * 获取用户信息
	 * @return mixed
	 */
	public function getUser ()
	{
		return $this->user;
	}

	/**
	 * 获取传递的额外参数
	 * @return mixed
	 */
	public function getParams ()
	{
		return $this->params;
	}

	/**
	 * 获取错误
	 * @return mixed
	 */
	public function getError ()
	{
		return $this->error;
	}

	/**
	 * 获取错误码
	 * @return integer
	 */
	public function getErrorCode ()
	{
		return $this->errorCode;
	}

	/**
	 * 获取授权token
	 * @return string
	 */
	public function getAccessToken ()
	{
		$request 	 = Container::get('request');
		$accessToken = empty($request->header($this->config['token_name'])) ? $request->param($this->config['token_name']) 
																		: $request->header($this->config['token_name']);
        return $accessToken;
	}
}