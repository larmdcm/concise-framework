<?php

namespace Concise\Routing;

class RouteVar
{
	protected $leftTag;

	protected $rightTag;

	protected $rule;

	public function __construct ($leftTag = "{",$rightTag = "}")
	{
		$this->setParseTag($leftTag,$rightTag);
	}

	public function setParseTag ($leftTag = "{",$rightTag = "}")
	{
		$this->leftTag  = preg_quote($leftTag);
		$this->rightTag = preg_quote($rightTag);
	}

	public function rule ($rule)
	{
		$this->rule = $rule;
		return $this;
	}

	public function parseVars (string $rule)
	{
		$patternVar = sprintf("/%s(\w+)%s/",$this->leftTag,$this->rightTag);
		$vars = [];

		if (!preg_match_all($patternVar, $rule,$vars)) {
			return ['rule' => $rule,'vars' => []];
		}
		$rule = preg_replace($patternVar,"",$rule);
		return ['rule' => $rule,'vars' => $vars[1]];
	}

	public function parseOptVars (string $rule)
	{
		$patternOpt = sprintf("/%s\?(\w+)%s/",$this->leftTag,$this->rightTag);
		$optVars = [];
		if (!preg_match_all($patternOpt,$rule,$optVars)) {
			return ['rule' => $rule,'optVars' => []]; 
		}
		$rule = preg_replace($patternOpt,"",$rule);
		return ['rule' => $rule,'optVars' => $optVars[1]]; 
	}


	public function parse () : array
	{
		$vars    = [];
		$optVars = [];
		$parseVars = $this->parseVars($this->rule);
		$vars = $parseVars['vars'];
		$parseOptVars = $this->parseOptVars($parseVars['rule']);
		$optVars = $parseOptVars['optVars'];
		$rule = $parseOptVars['rule'];

		$rules = explode('/',$rule);
		$rules = array_filter($rules,function ($value) {
			return !empty($value);
		});
		$path = implode('/', $rules);
		return ['path' => substr($path,0,1) === "/" ? $path : "/" . $path,'vars' => $vars,'optVars' => $optVars];
	}
}