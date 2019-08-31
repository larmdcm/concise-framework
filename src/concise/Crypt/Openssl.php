<?php

namespace Concise\Crypt;

class Openssl
{
	/**
	 * 等待加密字符串
	 */
	protected $str;

	/**
	 * 公钥
	 * @var string
	 */
	protected $publicKey;

	/**
	 * 秘钥
	 * @var string
	 */
	protected $privateKey;

	/**
	 * 生成的密码
	 * @var string
	 */
	protected $password;

	/**
	 * 初始化
	 * @param  string $str 
	 * @return void
	 */
	public function __construct ($str)
	{
		$this->str = $str;
	}

	/**
	 * 创建
	 * @param  string $str 
	 * @param  string $type 
	 * @return object
	 */
	public static function make ($str,$type = '')
	{
		$class = empty($type) ? static::class : '\Concise\Crypt\Openssl\\' . ucfirst($type);

		return new $class($str);
	}

	/**
	 * 获取hash值
 	 * @param  mixed $mode 
	 * @return string
	 */
	public function hash ($mode = PASSWORD_DEFAULT)
	{
		$this->password = password_hash($this->str,$mode);
		return $this;
	}

	/**
	 * 验证hash值
 	 * @param  string $hash 
	 * @return bool
	 */
	public function verifyHash ($hash)
	{
		return password_verify($this->str, $hash);
	}

	/**
	 * 设置密码
 	 * @param  string $password 
	 * @return object
	 */
	public function setPassWord ($password = '')
	{
		$this->password = $password;
		return $this;
	}

	/**
	 * 设置字符串
 	 * @param  string $str 
	 * @return object
	 */
	public function setStr ($str = '')
	{
		$this->str = $str;
		return $this;
	}

	/**
	 * 获取字符串
	 * @return str
	 */
	public function getStr ()
	{
		return $this->str;
	}

	/**
	 * 设置key值
	 * @param  string $publicKey 
	 * @param  string $privateKey 
	 * @return object
	 */
	public function setKey ($publicKey = '',$privateKey = '')
	{
		if (!empty($publicKey)) {
			$this->setPublicKey($publicKey);
		}
		if (!empty($privateKey)) {
			$this->setPrivateKey($privateKey);
		}
		return $this;
	}

	/**
	 * 设置公钥
	 * @param  string $publicKey 
	 * @return object
	 */
	public function setPublicKey ($publicKey)
	{
		$this->publicKey = is_file($publicKey) ? $this->readFileKey($publicKey) : $publicKey;
		return $this;
	}

	/**
	 * 设置私钥
	 * @param  string $publicKey 
	 * @return object
	 */
	public function setPrivateKey ($privateKey)
	{
		$this->privateKey = is_file($privateKey) ? $this->readFileKey($privateKey) : $privateKey;
		return $this;
	}

	/**
	 * 读取文件key
	 * @param  string $publicKey 
	 * @return object
	 */
	public function readFileKey ($path)
	{
		if (!is_file($path)) {
			throw new \RuntimeException("{$path}->key file not exists");
		}
		return file_get_contents($path);
	}

	/**
	 * 获取密码
	 * @return string
	 */
	public function get ()
	{
		return $this->password;
	}

	/**
	 * 返回base64加密结果
	 * @return string
	 */
	public function toBase64 ()
	{
		return base64_encode($this->get());
	}

	/**
	 * 返回base64解密结果对象
	 * @return object
	 */
	public function deBae64 ()
	{
		return $this->setPassWord(base64_decode($this->getStr()));
	}

	/**
	 * 返回url安全传输base64加密结果
	 * @return string
	 */
	public function toUrlBase64 ()
	{
		return str_replace(['+','/','='],['-','_',''],$this->toBase64());
	}

	/**
	 * 返回url安全传输base64解密结果对象
	 * @return string
	 */
	public function deUrlBase64 ()
	{
		$data = str_replace(['-','_'],['+','/'],$this->getStr());
		$mod4 = strlen($data) % 4;
		if ($mod4) {
			$data .= substr('====', $mod4);
		}
		return $this->setPassWord(base64_decode($data));
	}

	/**
	 * 返回加密字符串
	 * @param string $hashType
	 * @return string
	 */
	public function toHash ($hashType = 'md5')
	{
		return hash($hashType, $this->get());
	}
}