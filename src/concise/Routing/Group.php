<?php

namespace Concise\Routing;

class Group
{
	protected $defaultParams = [
		'namespace'   => '',
		'module'      => '',
		'middleware'  => '',
		'prefix'      => '',
		'doc'		  => []
	];

	protected $groupNumber = -1;

	protected $params = [];

	protected $groupNumbers = [];

	public function create ($params)
	{
		$this->groupNumber++;
		$groupNumber = $this->groupNumber;
		
		$this->groupNumbers[] = $groupNumber;
		$params = array_merge($this->defaultParams,$params);
		$this->params[$groupNumber] = $params;

		return $this->groupNumber;
	}
	
	public function after ($callback,$groupNumber)
	{
		is_callable($callback) && $callback();
		if (isset($this->groupNumbers[$groupNumber])) {
			unset($this->groupNumbers[$groupNumber]);	
		}
	}

	public function getParams ($groupNumber)
	{
		if (!is_array($groupNumber)) {
			$groupNumber = [$groupNumber];
		}
		$groupParams = array_values(array_filter($this->params,function ($item,$key) use ($groupNumber) {
			return in_array($key, $groupNumber);
		},ARRAY_FILTER_USE_BOTH));

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
		return empty($this->groupNumbers) ? -1 : $this->groupNumbers;
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