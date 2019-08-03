<?php

namespace Concise\Ioc\Provider;

class ErrorServiceProvider
{
	/**
	 * 获取全局错误信息
	 * @return array
	 */
	public function get ()
	{
		if (!session()->isInit()) {
			return [];
		}
		$errors = session('errors');
		if (empty($errors)) {
			return [];
		}
		session()->delete('errors');
		return $errors;
	}
	/**
	 * 设置全局错误信息
	 * @param mixed $errors 
	 * @return object
	 */
	public function set ($errors)
	{
		$errors = is_array($errors) ? $errors : [$errors];
		session('errors',$errors);
		return $this;
	}
	/**
	 * 添加全局错误信息
	 * @param  mixed $error 
	 * @return object        
	 */
	public function append ($error)
	{
		$errors = session('errors') ? session('errors') : [];
		if (is_array($error)) {
			$errors = array_merge($errors,$error);
		} else {
			array_push($errors, $error);
		}
		return $this->set($errors);
	}
}