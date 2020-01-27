<?php

namespace Concise\Http\Request;

use Concise\Container\Container;
use Concise\Foundation\Config;

trait AuthenticateRequest
{
	protected $authKey = 'concise_user';

	public function user ()
	{
		$user = Container::get('session')->get($this->getAuthKey());
		if (empty($user)) {
			return false;
		}

		return $user;
	}

	public function auth ($user)
	{
		return Container::get('session')->set($this->getAuthKey(),$user);
	}

	public function authExit ()
	{
		return Container::get('session')->delete($this->getAuthKey());
	}

	protected function getAuthKey ()
	{
		return sprintf("%s_%s_%s",Config::get('app_name','Concise'),$this->authKey,$this->module());
	}
}