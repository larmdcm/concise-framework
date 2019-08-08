<?php

namespace Concise\File;

class FileSystem
{	
	/**
	 * 遍历目录
	 * @param  string $directory 
	 * @param  Closure $callback
	 * @return mixed
	 */
	public function directorys ($directory,$callback = null)
	{
		if (!is_dir($directory)) {
			return false;
		}
		$handle = opendir($directory);

		while (($item = readdir($handle)) !== false) {
			if ($item == "." || $item == "..") continue;

			$path = $directory . '/' . $item;
			if (is_dir($path)) {
				is_callable($callback) && $callback($path);
				$this->directorys($path,$callback);
			}
		}
		closedir($handle);
	}

	public function read ($path)
	{
		
	}	
}