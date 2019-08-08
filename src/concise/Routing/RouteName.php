<?php

namespace Concise\Routing;

class RouteName
{
	protected $rules = [];

	public function set ($name,Rule $rule)
	{
		$this->rules[$name] = $rule;
	}

	public function get ($name)
	{
		if (isset($this->rules[$name])) {
			return $this->rules[$name];
		}
		throw new \RuntimeException("route {$name} alias not a set");
	}

	public function all ()
	{
		return $this->rules;
	}
} 