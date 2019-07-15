<?php

namespace Concise\Console\Command;

use Concise\Console\Console;
use Concise\Http\Rest\Doc\RestDoc;
use Concise\Container\Container;

class ApiDocBuild extends Console
{
	/**
	 * handle
	 * @return void
	 */
	public function handle ()
	{
		$args = $this->args;

		Container::get('app')->buildRoute();

		RestDoc::bind(Container::get('router'))->build();
		$this->out("api doc build success");	
	}
}