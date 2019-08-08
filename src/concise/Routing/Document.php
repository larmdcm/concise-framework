<?php

namespace Concise\Routing;

class Document
{
	protected $rules;

	public function attach (Rule $rule,$options = [])
	{
		$this->rules[] = [
			'sort'    => isset($options['sort']) ? $options['sort'] : count($this->rules),
			'rule' 	  => $rule,
			'enabled' => isset($options['enabled']) ?  $options['enabled'] : true,
			'params'  => isset($options['params']) ? $options['params'] : [],
			'return'  => isset($options['return']) ? $options['return'] : [],
		];
		return $this;
	}

	public function all ()
	{	
		$rules = array_filter($this->rules,function ($item) {
			return $item['enabled'];
		});
		return $this->sort(array_values($rules),'sort');
	}

	/**
	 * 快速排序
	 * @param  array $array 
	 * @param  string $key   
	 * @return array
	 */
	protected function sort ($array,$key) {
	    $count = count($array);
	    if ($count == 0) return $array;
	    if (count($array) == 1){
	      return $array;
	    }
	    $mid = $array[0];
	    $leftArray = array();
	    $rightArray = array();
	    for ( $i = 1; $i < $count; $i++) {
	      if ($array[$i][$key] >= $mid[$key]) {
	        $rightArray[] = $array[$i];
	      } else {
	        $leftArray[] = $array[$i];
	      }
	    }
	    $leftArray   = $this->sort($leftArray,$key);
	    $rightArray  = $this->sort($rightArray,$key);
	    $leftArray[] = $mid;
	    if (is_array($rightArray)) {
	      return array_merge($leftArray,$rightArray);
	    } else {
	      return $leftArray;
	    }
	}
}