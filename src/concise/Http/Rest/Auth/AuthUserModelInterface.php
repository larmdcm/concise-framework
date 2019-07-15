<?php

namespace Concise\Http\Rest\Auth;

interface AuthenticationModelInterface
{
	/**
	 * 查询方法
	 * @param  mixed $data 
	 * @return mixed
	 */
	public function find ($data);
}