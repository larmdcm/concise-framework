<?php

namespace Concise\Database\Builder;

use Concise\Database\Builder;

class Mysql extends Builder
{
	/**
	 * 转义
	 * @param  string $str 
	 * @return string      
	 */
	public function escape ($str)
	{
		if (strpos($str, '.') !== false) {
			$strs = explode('.',$str);
			return sprintf('`%s`.`%s`',$strs[0],$strs[1]);
		}
		return sprintf('`%s`',$str);
	}

	/**
	 * 获取limit
	 * @param  string $limit 
	 * @return string
	 */
	public function limit ($limit)
	{
		return empty($limit) ? '' : sprintf(' LIMIT %s',$limit);
	}

	/**
	 * 获取查询语句
	 * @return string
	 */
	public function getSelectSql ()
	{
		return 'SELECT %FIELD% FROM %TABLE%%JOIN%%WHERE%%GROUPBY%%HAVING%%ORDERBY%%LIMIT%';
	}
}