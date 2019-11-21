<?php

namespace Concise\File;

class FileSystem
{	
	/**
	 * 扫描目录
	 * @param  string $directory 
	 * @param  Closure $callback
	 * @param  boolean $isMulits
	 * @return mixed
	 */
	public function scanDirectory ($directory,$callback = null,$isMulits = true)
	{
		if (!is_dir($directory)) {
			return false;
		}
		$handle = opendir($directory);

		while (($item = readdir($handle)) !== false) {
			if ($item == "." || $item == "..") continue;

			$path = $directory . '/' . $item;
			if (is_dir($path)) {
				$type = 'dir';
				$isMulits && $this->scanDirectory($path,$callback,$isMulits);
			} else {
				$type = 'file';
			}
			is_callable($callback) && $callback($type,$path);
		}
		closedir($handle);
	}

	/**
	 * 遍历目录
	 * @param  string $directory 
	 * @param  Closure $callback
	 * @param  boolean $isMulits
	 * @return mixed
	 */
	public function directorys ($directory,$callback = null,$isMulits = true)
	{
		$this->scanDirectory($directory,function ($type,$path) use ($callback) {
			$type == 'dir' && $callback($path);
		},$isMulits);
	}

	/**
	 * 遍历目录下的文件
	 * @param  string $directory 
	 * @param  Closure $callback
	 * @param  boolean $isMulits
	 * @return mixed
	 */
	public function directoryAsFiles ($directory,$callback = null,$isMulits = true)
	{
		$this->scanDirectory($directory,function ($type,$path) use ($callback) {
			$type == 'file' && $callback($path);
		},$isMulits);
	}
}