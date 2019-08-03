<?php

namespace Concise\Routing\Route;

use Concise\Foundation\Config;

class Group
{
	protected $defaultParams = [
		'namespace'   => '',
		'module'      => '',
		'middleware'  => '',
		'prefix'      => ''
	];

	protected $groupNumber = -1;

	protected $params = [];

	protected $isOpen = false;

	public function __construct ()
	{
	}

	public function create ($params)
	{
		if (!$this->isOpen) {
			$this->groupNumber++;
		}
			
		$groupNumber = $this->groupNumber;

		$this->isOpen = true;

		if (!isset($this->params[$groupNumber])) {
			$this->params[$groupNumber] = [];
		}

		array_push($this->params[$groupNumber],array_merge($this->defaultParams,$params));
		return $this;
	}
	
	public function after ($callback)
	{
		is_callable($callback) && $callback();
		$this->isOpen = false;
	}

	public function getParams ($groupNumber)
	{
		$groupParams = isset($this->params[$groupNumber]) ? $this->params[$groupNumber] : [];
		if (!empty($groupParams)) {
			$params = $groupParams[0];
			if (count($groupParams) > 1) {
				unset($groupParams[0]);
				foreach ($groupParams as $param) {
					$params = $this->paramsMerge($params,$param);
				}
			}
			return $params;
		}
		return $groupParams;
	}

	public function getGroupNumber ()
	{
		return $this->isOpen ? $this->groupNumber : -1;
	}

	public function getDefaultParams ()
	{
		return $this->defaultParams;
	}

	protected function paramsMerge ($paramsOne,$paramsTwo) {
		foreach ($paramsTwo as $key => $value) {
			if ($key == 'middleware') {
				$middleware = is_array($value) ? $value : [$value];
				if (isset($paramsOne['middleware']) && !empty($paramsOne['middleware'])) {
					$paramsOne['middleware'] = is_array($paramsOne['middleware']) ?  $paramsOne['middleware'] : [$paramsOne['middleware']];
				} else {
					$paramsOne['middleware'] = [];
				}
				array_walk($middleware, function ($item) use (&$paramsOne) {
					if (!empty($item)) {
						array_push($paramsOne['middleware'],$item);
					}
				});
			} else {
				if (!empty($value)) {
					$paramsOne[$key] = $value;
				}
			}
		}
		return $paramsOne;
	}
}