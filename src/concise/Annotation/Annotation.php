<?php

namespace Concise\Annotation;

use Concise\Container\Container;
use Concise\Foundation\Facade\FileSystem;
use Concise\Foundation\Facade\Env;

abstract class Annotation
{	
	/**
	 * 对象名称
	 * @var string
	 */
	protected $name = null;

	/**
	 * el变量列表
	 * @var array
	 */
	protected $elVarData = [
		'asterisk' => '*',
		'space'    => ' ',
		'empty'    => '',
		'line'     => "\r\n"
	];

	/**
	 * 参数列表
	 * @var array
	 */
	protected $arguments = [];

	/**
	 * 默认参数名称列表
	 * @var array
	 */
	protected $defaultArgumentNames = [];

	/**
	 * 构造方法初始化
	 * @return void
	 */
	public function __construct ()
	{
		if (method_exists($this,'initialize')) {
			$alias = get_class($this);
			Container::set($alias,$this)->invokeMethod($alias,'initialize',true);
		}
	}

	/**
	 * 扫描目录解析资源
	 * @param  string  $dir      
	 * @param  string  $method      
	 * @param  boolean $isMulits 
	 * @return void          
	 */
	public function resource ($dir,$method = 'resourceMethods',$isMulits = false)
	{
		FileSystem::directoryAsFiles($dir,function ($path) use ($method) {
			$rootPath = Env::get('base_path');
			$path = str_replace('/','\\',str_replace($rootPath,'',$path));
			$className = explode('.',$path)[0];
			$this->{$method}($className);
		},$isMulits);
	}

	/**
	 * 解析资源类
	 * @param  string $className 
	 * @param  string $method    
	 * @return void    
	 */
	public function resourceClass ($className)
	{
		if (!class_exists($className)) return; 
		$ref = new \ReflectionClass($className);
		$comment = $ref->getDocComment();
		if (!$comment) return;
		$this->parse($comment,$ref);

	}
	/**
	 * 解析资源方法
	 * @param  string $className 
	 * @param  string $method    
	 * @return void    
	 */
	public function resourceMethods ($className,$method = null)
	{
		if (!class_exists($className)) return; 
		$methods = is_null($method) ? (new \ReflectionClass($className))->getMethods() : [new \ReflectionMethod($className,$method)];

		array_walk($methods,function ($ref) {
			$comment = $ref->getDocComment();
			if (!$comment) return;
			$this->parse($comment,$ref);
		});

	}


	/**
	 * 注解参数处理
	 * @return void
	 */
	abstract function handle ($ref);

	/**
	 * 解析注解
	 * @param  string $comment 
	 * @param  object $ref 
	 * @return void
	 */
	protected function parse ($comment,$ref)
	{
		$comment = $this->replace($comment);
		$pattern = sprintf('/\@%s\(\s*([^\)]*)\)|\@%s\(\s*([^\)]*)\)/',preg_quote($this->getName()),
			lcfirst(preg_quote($this->getName())));

		if (preg_match_all($pattern, $comment, $matchs)) {
			array_walk($matchs[1],function ($match) use ($ref) {
				$annotComment = preg_replace("/\s+/","",$match);
				$annotItems = explode(",",$annotComment);
				array_walk($annotItems,function ($item,$index) {
					$this->parseAnnotArgument($item,$index);
				});
				$this->handle($ref);
				$this->arguments = [];
			});
		}
	}

	/**
	 * 解析注解参数
	 * @param  string $item 
	 * @param  integer $index 
	 * @return void      
	 */
	private function parseAnnotArgument ($item,$index) 
	{
		$value = $item;
		if (strpos($item,'=') !== false) {
			$items  = explode('=',$item);
			$key    = $items[0]; 
			$value  = $items[1];
		} else {
			$defaultArgumentNames = $this->getDefaultArgumentNames();
			$key = isset($defaultArgumentNames[$index]) ? $defaultArgumentNames[$index] : count($defaultArgumentNames);
		}
		$this->arguments[$key] = $this->parseElVar($value);
	}

	/**
	 * 解析el
	 * @param  string $value 
	 * @return string      
	 */
	private function parseElVar ($value)
	{
		$value = $this->replace($value);
		$pattern = '/\$\{\s*(\w+)\s*\}|\{\$\s*(\w+)\s*\}|\{\s*(\w+)\s*\}/';
		return preg_replace_callback($pattern, function ($matchs) {
			 $key = $matchs[count($matchs) - 1];
			 return isset($this->elVarData[$key]) ? $this->elVarData[$key] : '';
		}, $value);
	}

	/**
	 * 替换字符
	 * @param string $comment
	 * @return string
	 */
	private function replace ($comment)
	{
		$comment = str_replace("*","",$comment);
		return $comment;
	}

	/**
	 * 设置名称
	 * @param stromg $name 
	 * @return object
	 */
	public function setName ($name) 
	{
		$this->name = $name;
		return $this;
	}
	/**
	 * 获取注解对象名
	 * @return string
	 */
	public function getName () {
		return is_null($this->name) ? basename(get_class($this)) : $this->name;
	}

	/**
	 * 获取参数列表
	 * @return array
	 */
	public function getArgsuments ()
	{
		return $this->arguments;
	}

	/**
	 * 获取默认参数名称列表
	 * @return array
	 */
	public function getDefaultArgumentNames ()
	{
		return $this->defaultArgumentNames;
	}
}