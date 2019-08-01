<?php

namespace Concise\Console\Command;

use Concise\Console\Console;
use Concise\Env;

class MakeConfig extends Console
{
	/**
	 * handle
	 * @return void
	 */
	public function handle ()
	{
		$args = $this->args;
		if (isset($args['make'])) {
			$configPath = Env::get('config_path');
			$configFile = $configPath . DIRECTORY_SEPARATOR . $args['make'] . '.php';
			$makePath   = __DIR__ . DIRECTORY_SEPARATOR . 'Make' . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . $args['make'] . '.php';
			if (file_exists($makePath)) {
				if (!file_exists($configFile)) {
					file_put_contents($configFile, file_get_contents($makePath));
					$this->out('config make success');
				} else {
					$this->out(basename($configFile) . ' file is exists');
				}
			} else {
				$this->out(basename($makePath) . ' file not exists');
			}
		}
	}
} 