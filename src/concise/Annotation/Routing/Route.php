<?php

namespace Concise\Annotation\Routing;

use Concise\Annotation\Annotation;
use Concise\Routing\Route as Router;

class Route extends Annotation
{
	/**
	 * 默认选项
	 * @var array
	 */
	private $options = [
		'method' => 'get',
		'prefix' => ''
	];

	/**
	 * route object
	 * @var Concise\Routing\Route
	 */
	private $route;

	/**
	 * 初始化
	 * @param  RouteDispath $route 
	 * @return void
	 */
	public function initialize (Router $route)
	{
		$this->route = $route;
	}

	/**
	 * 处理注解
	 * @param object $ref
	 * @return void
	 */
	public function handle ($ref)
	{
		$this->options['handle'] = sprintf('%s@%s',basename($ref->class),$ref->getName());

		$arguments = array_merge($this->options,$this->getArgsuments());
		$this->route->group(function () use ($ref,$arguments) {
			$this->route->rule($arguments['method'],$arguments['path'],$arguments['handle'])
						->prefix($this->options['prefix'])
						->namespace(dirname($ref->class));
		});
	}

	/**
	 * 获取默认参数名称列表
	 * @return array
	 */
	public function getDefaultArgumentNames ()
	{
		return [
			'path','method','prefix'
		];
	}
}