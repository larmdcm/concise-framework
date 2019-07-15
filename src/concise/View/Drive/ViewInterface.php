<?php

namespace Concise\View\Drive;

interface ViewInterface
{
	/**
	 * 模板引擎初始化
	 * @return void
	 */
	public function boot();
	/**
	 * 渲染模板文件
	 * @return string
	 */
	public function render();
	/**
	 * 获取模板文件
	 * @return string
	 */
	public function fetch();
}