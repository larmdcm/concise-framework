<?php

namespace Concise\Crypt\Openssl;

use Concise\Crypt\Openssl;

class Rsa extends Openssl
{
	/**
	 * rsa加密
	 * @param mixed $padding
	 */
	public function encrypt ($padding = OPENSSL_PKCS1_PADDING)
	{
		if (!$this->checkEnPadding($padding)) {
			throw new \RuntimeException("Rsa Encrypt Padding check error");
		}
		openssl_public_encrypt($this->getStr(), $password, $this->getRsaPublicKey(),$padding);
		return $this->setPassWord($password);
	}

	/**
	 * rsa解密
	 * @param mixed $padding
	 */
	public function decrypt ($padding = OPENSSL_PKCS1_PADDING)
	{
		if (!$this->checkDePadding($padding)) {
			throw new \RuntimeException("Rsa Decrypt Padding check error");
		}

		$data = $this->get();

		if (!$data) {
			return $this->setPassWord(false);
		}

		if (!openssl_private_decrypt($data, $result, $this->getRsaPrivateKey(),$padding)) {
			return $this->setPassWord(false);
		}

		return $this->setPassWord($result);
	}

	/**
	 * 生成签名
	 * @return object
	 */
	public function sign ()
	{
		openssl_sign($this->getStr(), $sign, $this->getRsaPrivateKey());
		return $this->setPassWord($sign);
	}

	/**
	 * 验证签名
	 * @return object
	 */
	public function verify ()
	{
		$sign = $this->get();
		openssl_verify($sign, $result, $this->getRsaPublicKey());
		return $this->setPassWord($result);
	}

	/**
	 * 获取rsa公钥
	 * @return mixed
	 */
	protected function getRsaPublicKey ()
	{
	    $publicKey = "-----BEGIN PUBLIC KEY-----\n" .
            wordwrap($this->publicKey, 64, "\n", true) .
        "\n-----END PUBLIC KEY-----";
		return openssl_get_publickey($publicKey);
	}

	/**
	 * 获取rsa私钥
	 * @return mixed
	 */
	protected function getRsaPrivateKey ()
	{
		$privateKey = "-----BEGIN RSA PRIVATE KEY-----\n" .
            wordwrap($this->privateKey, 64, "\n", true) .
        "\n-----END RSA PRIVATE KEY-----";
		return openssl_get_privateKey($privateKey);
	}

	/**
	 * 检查加密填充
	 * @param mixed $padding
	 * @return bool
	 */
	protected function checkEnPadding ($padding)
	{
		return $padding == OPENSSL_PKCS1_PADDING;
	}

	/**
	 * 检查解密填充
	 * @param mixed $padding
	 * @return bool
	 */
	protected function checkDePadding ($padding)
	{
		return $padding == OPENSSL_PKCS1_PADDING || $padding == OPENSSL_NO_PADDING;
	}
}