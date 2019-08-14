<?php

namespace Concise\Foundation\Provider;

class CsrfTokenMapServiceProvider
{
	protected $hashType = 'md5';

	public $token = '__token';

	/**
	 * 注册token
	 * @return \CsrfTokenMapServiceProvider
	 */
	public function map ()
	{
		if (empty(session($this->token))) {
			$token = hash($this->hashType, time() . mt_rand(111111,999999));
			session()->set($this->token,$token);
		}

		return $this;
	}

	/**
	 * 获取token
	 * @return string
	 */
	public function getToken ()
	{
		return session($this->token);
	}
}