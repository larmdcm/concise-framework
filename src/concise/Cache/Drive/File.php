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
	protected $options = [];

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
	}

	/**
	 * 获取缓存key值
	 * @param  string $name 
	 * @return mixed 
	 */
	public function get ($name)
	{
		$name   = $this->getCacheKey($name);
		
		$data   = $this->getCache($name);
		return Arr::get($data,$name);
	}

	/**
	 * 值自增
	 * @param  string $name 
	 * @return mixed 
	 */
	public function incr ($name)
	{
		$value = $this->get($name);

		if (is_null($value)) {
			return null;
		}

		$value++;
		return $this->set($name,$value);
	}

	/**
	 * 值自减
	 * @param  string $name 
	 * @return mixed 
	 */
	public function decr ($name)
	{
		$value = $this->get($name);

		if (is_null($value)) {
			return null;
		}

		$value--;
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

		$cache  = unserialize(file_get_contents($path));

		Arr::set($cache['data'],$name,$value);

		$cache['create_time'] = time();
		$cache['expire_time'] = is_null($time) ? $this->options['expire_time'] : $time;

		return file_put_contents($path,serialize($cache)) ? true : false;
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

		$cache  = unserialize(file_get_contents($path));

		Arr::delete($cache['data'],$name);

		return file_put_contents($path,serialize($cache)) ? true : false;
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
	 * @return string
	 */
	protected function getCacheFilePath ($name)
	{
		$path = Env::get('runtime_path',__DIR__) . '/cache/' . md5(explode('.',$name)[0]) . '.php';
		is_dir(dirname($path)) || mkdir(dirname($path),0755);
		if (file_exists($path)) {
			$cache = unserialize(file_get_contents($path));
			if ($cache['expire_time'] != 0) {
				$expireTime = $cache['create_time'] + $cache['expire_time'];
				if (time() < $expireTime) {
					return $path;
				}
			} else {
				return $path;
			}
		}
		$data['data']        = [];
		$data['expire_time'] = $this->options['expire_time'];
		$data['create_time'] = time();
		file_put_contents($path, serialize($data));
		return $path;
	}

	/**
	 * 获取cache
	 * @param  string $name 
	 * @return array    
	 */
	protected function getCache ($name)
	{
		$path  = $this->getCacheFilePath($name);
		$cache = unserialize(file_get_contents($path));
		if ($cache['expire_time'] != 0) {
			$expireTime = $cache['create_time'] + $cache['expire_time'];
			if (time() > $expireTime) {
				return null;
			}
		}

		return $cache['data'];
	}
}