<?php

namespace Concise\Exception;

use Concise\Http\Rest\Rest;
use Concise\Container\Container;

class ValidatorErrorException extends \RuntimeException
{
	/**
	 * 错误
	 * @var array
	 */
	protected $error = [];

	/**
	 * 类型
	 * @var string
	 */
	protected $type;

	public function __construct ($error = [],$type = 'api',$message = '',$code = 0)
	{
		$this->error = $error;

		$this->type  = $type;
		
		parent::__construct($message,$code);
	}

	public function end ()
	{
		if ($this->type == 'api') {
			$error = $this->getError();
			return Rest::fail([],400,array_shift($error));
		} else {
			$request = Container::get('request');

			$error   = $this->getError();
			if ($request->isAjax()) {
				return Rest::fail([],400,array_shift($error[0]));
			} else {
				errors($error[0]);
				return redirect($error[1]);
			}
		}
	}

	public function getError ()
	{
		return $this->error;
	}
}