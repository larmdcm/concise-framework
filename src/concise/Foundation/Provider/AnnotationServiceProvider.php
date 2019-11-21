<?php

namespace Concise\Foundation\Provider;

use Concise\Annotation\Import as ImportAnnotation;

class AnnotationServiceProvider
{
	protected $import;

	public function __construct ()
	{
		$this->import = new ImportAnnotation();
	}

	/**
	 * 注册注解
	 * @return void
	 */
	public function map ()
	{
	}

	public function __call ($method,$params)
	{
		if (method_exists($this->import, $method)) {
			return call_user_func_array([$this->import,$method],$params);
		}
	}
}