<?php

namespace Concise\Error;

use Concise\Exception\HttpException;
use Concise\Http\Response;
use Concise\Foundation\Config;
use Concise\Error\Handle\ErrorHandle;
use Concise\Container\Container;

class Error
{

	/**
	 * 注册错误处理
	 * @return mixed
	 */
	public static function register ()
	{
		error_reporting(E_ALL);
		return static::getErrorHandle()->register();
	}

	public static function responseHttpError (HttpException $e)
	{
		return static::getErrorHandle()->responseHttpError($e);
	}

	public static function getErrorHandle ()
	{	
		if (!Container::exists('error')) {
			$handle = new ErrorHandle();
			$handle->setTitle(Config::get('error_handle.title',''));
			$handle->setMessage(Config::get('error_handle.message',''));
			$handle->setCustomErrorHandle(Config::get('error_handle.custom_error_handle',''));
			Container::set('error',$handle);
		}
		return Container::get('error');
	}
}