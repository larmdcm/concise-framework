<?php

namespace Concise\Http\Request;

use Concise\Container\Container;

trait AuthenticateRequest
{
	protected $key = 'concise_user';

	public function user ()
	{
		$user = Container::get('session')->get($this->key);
		if (empty($user)) {
			return false;
		}

		return $user;
	}

	public function auth ($user)
	{
		return Container::get('session')->set($this->key,$user);
	}

	public function authExit ()
	{
		return Container::get('session')->delete($this->key);
	}
}