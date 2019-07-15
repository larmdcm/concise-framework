<?php

namespace Concise\Hash;

use Concise\Hash\Phpass\PasswordHash;

class Hash
{
	/**
	 * 待处理串
	 * @var string
	 */
	protected $str;

	/**
	 * passwordHasher
	 * @var object
	 */
	protected $passwordHasher;

	/**
	 * 构造方法初始化
	 * @param string $str 
	 */
	private function __construct (string $str)
	{
		$this->str = $str;
		$this->passwordHasher = new PasswordHash(8,false);
	}
	/**
	 * 创建
	 * @param  string $str 
	 * @return object
	 */
	public static function make (string $str)
	{
		return new static($str);
	}
	/**
	 * 获取加密后的字符串
	 * @return string
	 */
	public function get ()
	{
		return $this->passwordHasher->HashPassword($this->str);
	}
	/**
	 * 验证加密串
	 * @return bool
	 */
	public function check ($hashPassword)
	{
		return $this->passwordHasher->CheckPassword($this->str,$hashPassword);
	}
	/**
	 * 无方法时调用
	 * @param  string $method 
	 * @param  array $params 
	 * @return  mixed
	 */
	public function __call ($method,$params)
	{
		if (method_exists($this->passwordHasher, $method))
		{
			return call_user_func_array([$this->passwordHasher,$method],$params);
		}
		throw new \Exception(__CLASS__ . '->' . $method . " method not exists!");
	}
}