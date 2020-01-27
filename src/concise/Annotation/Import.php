<?php

namespace Concise\Annotation;

class Import extends Annotation
{
	/**
	 * 默认选项
	 * @var array
	 */
	private $options = [
		'component' => '',
		'name'      => ''
	];

	/**
	 * 默认命名空间
	 * @var array
	 */
	private $namespace = [
		'Concise\Annotation\Routing'
	];

	/**
	 * 处理注解
	 * @param object $ref
	 * @return void
	 */
	public function handle ($ref)
	{
		$arguments = array_merge($this->options,$this->getArgsuments());
		$components = $this->parseArrayArguments($arguments['component']);
		foreach ($components as $component) {
			if (!class_exists($component)) {
				foreach ($this->getNamesapce() as $namespace) {
					$componentClass = sprintf("\\%s\\%s",$namespace,$component);
					if (class_exists($componentClass)) {
						$component = $componentClass;
						break;
					}
				}
			}
			
			$annotComponent = new $component();
			if (!empty($arguments['name'])) {
				$annotComponent->setName($arguments['name']);
			}
			$annotComponent->resourceMethods($ref->getName());
		}
	}

	/**
	 * 获取默认参数名称列表
	 * @return array
	 */
	public function getDefaultArgumentNames ()
	{
		return [
			'component','name'
		];
	}

	/**
	 * 获取命名空间列表
	 * @return array
	 */
	public function getNamesapce ()
	{
		return $this->namespace;
	}
}