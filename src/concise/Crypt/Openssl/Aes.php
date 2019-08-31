<?php

namespace Concise\Crypt\Openssl;

use Concise\Crypt\Openssl;

class Aes extends Openssl
{
	/**
	 * aes加密
	 * @param string $method
	 * @param string $iv
	 * @param mixed $option
	 */
	public function encrypt ($method = 'AES-128-CBC',$iv = "1234567891011126",$option = OPENSSL_RAW_DATA)
	{
		return $this->setPassword(openssl_encrypt($this->getStr(), $method, $this->publicKey,$option,$iv));
	}

	/**
	 * aes解密
	 * @param string $method
	 * @param string $iv
	 * @param mixed $option
	 */
	public function decrypt ($method = 'AES-128-CBC',$iv = "1234567891011126",$option = OPENSSL_RAW_DATA)
	{
		return $this->setPassword(openssl_decrypt($this->get(), $method, $this->publicKey,$option,$iv));
	}

	/**
	 * 获取aes加密可用方法列表
	 * @return array
	 */
	public function getMethods ()
	{
		return openssl_get_cipher_methods();
	}
}