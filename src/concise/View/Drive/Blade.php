<?php

namespace Concise\View\Drive;

use terranc\Blade\Compilers\BladeCompiler;
use terranc\Blade\Engines\CompilerEngine;
use terranc\Blade\FileViewFinder;
use terranc\Blade\Factory;
use Concise\View\View;
use Concise\Container\Container;

class Blade extends View implements ViewInterface
{

	/**
	 * 初始化
	 * @return void            
	 */
	public function boot ()
	{
		$request = Container::get('request');
		
		$this->config['view_cache_path'] = $this->config['view_cache_path'] . '/' . substr(md5(empty($request->module()) ? 'home' : $request->module()),6,6);

		$compiler = new BladeCompiler($this->config['view_cache_path'],$this->config['tpl_cache']);
	    $compiler->setContentTags($this->config['tpl_begin'], $this->config['tpl_end'], true);
        $compiler->setContentTags($this->config['tpl_begin'], $this->config['tpl_end'], false);
        $compiler->setRawTags($this->config['tpl_raw_begin'], $this->config['tpl_raw_end'], false);

	    $engine = new CompilerEngine($compiler);
        $finder = new FileViewFinder([$this->config['view_path']], [$this->config['view_suffix'], 'tpl']);

        // 实例化 Factory
        $this->template = new Factory($engine, $finder);
	}

	/**
	 * 渲染视图
	 * @param  string $template 
	 * @param  array $data 
	 * @return string           
	 */
	public function render ($template = '',$data = [])
	{
		echo $this->fetch($template,$data);
	}
	/**
	 * 渲染视图文件
	 * @param  string $template 
	 * @param  array $data     
	 * @return string
	 */
	public function fetch ($template = '',$data = [])
	{
		$template = empty($template) ? $this->templatePath : $template; 
		$file 	  = $this->parseTemplate($template);
		$data 	  = empty($data) ? $this->data : array_merge($this->data,$data);
		return $this->template->file($file,$data,[])->render(); 
	}
}