<?php

namespace Concise\Cache\Drive;

use Concise\Cache\CacheAbstract;
use Concise\Foundation\Facade\Env;
use Concise\Foundation\Arr;

class File extends CacheAbstract
{
	/**
	 * 参数
	 * @var array
	 */
	protected $options = [
		'cache_subdir' => true,
		'hash_type'    => 'md5',
		'path'         => ''
	];

	/**
	 * 初始化
	 * @param array $options 参数
	 * @return void
	 */
	public function __construct ($options = [])
	{
		if (!empty($options)) {
       		$this->options = array_merge($this->options,$options);
        }
        if (empty($this->options['path'])) {
        	$this->options['path'] = Env::get('runtime_path',__DIR__) . DIRECTORY_SEPARATOR . 'cache';
	        is_dir($this->options['path']) || mkdir(Env::get('runtime_path',__DIR__) . DIRECTORY_SEPARATOR . 'cache',0755,true);
        }
	}

	/**
	 * 获取缓存key值
	 * @param  string $name 
	 * @param  string $default
	 * @return mixed 
	 */
	public function get ($name,$default = '')
	{
		$name   = $this->getCacheKey($name);
		
		$data   = $this->getCache($name);
		return Arr::get($data,$name,$default);
	}

	/**
	 * 值自增
	 * @param  string $name 
	 * @param  integer $step
	 * @return mixed 
	 */
	public function incr ($name,$step = 1)
	{
		$value = $this->get($name);

		if (is_null($value)) {
			return null;
		}

		$value += $step;
		return $this->set($name,$value);
	}

	/**
	 * 值自减
	 * @param  string $name 
	 * @param  integer $step 
	 * @return mixed 
	 */
	public function decr ($name,$step = 1)
	{
		$value = $this->get($name);

		if (is_null($value)) {
			return null;
		}

		$value -= $step;
		return $this->set($name,$value);
	}

	/**
	 * 设置key值
	 * @param string $name  
	 * @param mixed $value 
	 * @param mixed $time 
	 * @return mixed
	 */
	public function set ($name,$value,$time = null) 
	{
		$name   = $this->getCacheKey($name);
		$path   = $this->getCacheFilePath($name);

		$cache  = $this->unserialize(file_get_contents($path));

		Arr::set($cache['data'],$name,$value);

		$cache['create_time'] = time();
		$cache['expire_time'] = is_null($time) ? $this->options['expire_time'] : $time;

		return file_put_contents($path,$this->serialize($cache)) ? true : false;
	}

	/**
	 * 获取缓存值是否存在
	 * @param  string  $name 
	 * @return boolean       
	 */
	public function has ($name)
	{
		$name   = $this->getCacheKey($name);
		
		$data   = $this->getCache($name);
		
		return Arr::has($data,$name);
	}

	/**
	 * 删除key值
	 * @param  string $name 
	 * @return mixed
	 */
	public function delete ($name)
	{
		$name   = $this->getCacheKey($name);
		$path   = $this->getCacheFilePath($name);

		$cache  = $this->unserialize(file_get_contents($path));

		Arr::delete($cache['data'],$name);

		return file_put_contents($path,$this->serialize($cache)) ? true : false;
	}

	/**
	 * 清除全部
	 * @param string $name 
	 * @return mixed
	 */
	public function clear($name)
	{
		$name   = $this->getCacheKey($name);
		$path   = $this->getCacheFilePath($name);
		return unlink($path);
	}

	/**
	 * 获取缓存文件路径
	 * @param  string $name 
	 * @param  bollean $getCache 
	 * @return string
	 */
	protected function getCacheFilePath ($name,$getCache = false)
	{
		$name = hash($this->options['hash_type'],explode('.',$name)[0]);
		
		if ($this->options['cache_subdir']) {
			$name = substr($name, 0, 2) . DIRECTORY_SEPARATOR . substr($name, 2);
		}
		$path = $this->options['path'] . DIRECTORY_SEPARATOR . $name . '.php';
		$subDir = dirname($path);
		is_dir($subDir) || mkdir($subDir,0755,true);

		if (file_exists($path)) {
			return $path;
		}
		if ($getCache) {
			is_dir($subDir) && rmdir($subDir);
			return !$getCache;
		}
		$data['data']        = [];
		$data['expire_time'] = $this->options['expire_time'];
		$data['create_time'] = time();
		file_put_contents($path, $this->serialize($data));
		return $path;
	}

	/**
	 * 获取cache
	 * @param  string $name 
	 * @return mixed    
	 */
	protected function getCache ($name)
	{
		$path  = $this->getCacheFilePath($name,true);
		if ($path === false) {
			return [];
		}
		$cache = $this->unserialize(file_get_contents($path));
		if ($cache['expire_time'] != 0) {
			$expireTime = $cache['create_time'] + $cache['expire_time'];
			if (time() > $expireTime) {
				$subDir = dirname($path);
				$paths = explode(DIRECTORY_SEPARATOR,$subDir);
				unlink($path);
				if ($this->options['cache_subdir'] && is_dir($subDir) &&  $paths[count($paths) - 1] !== 'cache') {
					rmdir($subDir);
				}
				return [];
			}
		}

		return $cache['data'];
	}
}