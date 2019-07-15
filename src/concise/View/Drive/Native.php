<?php

namespace Concise\View\Drive;

use Concise\View\View;

class Native extends View implements ViewInterface
{
	/**
	 * 初始化
	 * @return void            
	 */
	public function boot () {}
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
		$this->with($data);
		ob_start();
		!empty($this->data) && extract($this->data,EXTR_OVERWRITE);
		include $file;
		$content = ob_get_contents();
		ob_clean();
		return $content;
	}
} 