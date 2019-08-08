<?php

namespace Concise\Foundation\Provider;

class CsrfTokenMapServiceProvider
{
	protected $hashType = 'md5';

	public function map ()
	{
		$token = hash($this->hashType, time() . mt_rand(111111,999999));
		session()->set('__token',$token);
	}
}