<?php

namespace Concise\Config;

use Concise\Foundation\Arr;
use ArrayAccess;

class Repository implements IConfig,ArrayAccess
{
	/**
	 * 存储
	 * @var array
	 */
	protected $data  = [];

	/**
	 * 构造方法初始化
	 * @param string $path string
	 * @return void
	 */
	public function __construct (string $path)
	{
		$this->data = $this->parse($path);
	}
	/**
	 * 设置配置值
	 * @param string $key   
	 * @param mixed $value 
	 * @return bool
	 */
	public function set (string $key,$value) : bool
	{
		return Arr::set($this->data,$key,$value);
	}
	/**
	 * 获取变量值
	 * @param  string $key 
	 * @param  mixed $default
	 * @return mixed  
	 */
	public function get (string $key = '',$default = null)
	{
		return Arr::get($this->data,$key,$default);
	}
	/**
	 * 获取配置值是否存在
	 * @param  string  $key 
	 * @return bool      
	 */
	public function has (string $key) : bool
	{
		return Arr::has($this->data,$key);
	}
	/**
	 * 清除所有配置
	 * @return bool
	 */
	public function clear () : bool
	{
		return Arr::clear($this->data);
	}
	/**
	 * 删除配置值
	 * @param  string $key 
	 * @return bool
	 */
	public function delete (string $key) : bool
	{
		return Arr::delete($this->data,$key);
	}
	/**
	 * 解析配置
	 * @param  string $path 
	 * @return mixed
	 */
	public function parse (string $path) : array
	{
		$data = is_file($path) ? include $path : [];
		return $data;
	}
	/**
	 * 对象字符串转换
	 * @return string
	 */
	public function __tostring ()
	{
		return json_encode($this->data);
	}
	/**
	 * 检查一个偏移位置是否存在
	 * @param  string $index 
	 * @return bool       
	 */
    public function offsetExists($index) 
    {
        return isset($this->data[$index]);
    }
    /**
     * 获取一个偏移位置的值
     * @param  string $index 
     * @return string      
     */
    public function offsetGet($index) 
    {
        return isset($this->data[$index]) ? $this->data[$index] : '';
    }
    /**
     * 设置一个偏移位置的值
     * @param  string $index    
     * @param  string $newvalue 
     * @return void           
     */
    public function offsetSet($index, $newvalue) 
    {
        $this->data[$index] = $newvalue;
    }
    /**
     * 复位一个偏移位置的值
     * @param  string $index 
     * @return void
     */
    public function offsetUnset($index) 
    {
        unset($this->data[$index]);
    }
}