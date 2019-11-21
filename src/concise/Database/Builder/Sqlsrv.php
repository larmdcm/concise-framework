<?php

namespace Concise\Database\Builder;

use Concise\Database\Builder;

class Sqlsrv extends Builder
{

	/**
	 * limit
	 * @var string
	 */
	private $limit = '';

	/**
	 * 转义
	 * @param  string $str 
	 * @return string      
	 */
	public function escape ($str)
	{
		if (strpos($str, '.') !== false) {
			$strs = explode('.',$str);
			return sprintf('[%s].[%s]',$strs[0],$strs[1]);
		}
		return sprintf('[%s]',$str);
	}

	/**
	 * 获取limit
	 * @param  string $limit 
	 * @return string
	 */
	public function limit ($limit)
	{
		if (empty($limit)) {
            return '';
        }

        $limit = explode(',', $limit);

        if (count($limit) > 1) {
            $limitStr = '(T1.ROW_NUMBER BETWEEN ' . $limit[0] . ' + 1 AND ' . $limit[0] . ' + ' . $limit[1] . ')';
        } else {
            $limitStr = '(T1.ROW_NUMBER BETWEEN 1 AND ' . $limit[0] . ")";
        }

        return 'WHERE ' . $limitStr;
	}

	/**
	 * 解析orderby
	 * @param  string $orderBy 
	 * @return string          
	 */
	public function parseOrderBy ($orderBy)
	{
		return empty($orderBy) ? 'ORDER BY rand()' : $orderBy;
	}
	
	/**
	 * 获取查询语句
	 * @return string
	 */
	public function getSelectSql ()
	{
		return 'SELECT T1.* FROM (SELECT conciseDb.*, ROW_NUMBER() OVER (%ORDERBY%) AS ROW_NUMBER FROM (SELECT %FIELD% FROM %TABLE%%JOIN%%WHERE%%GROUPBY%%HAVING%) AS conciseDb) AS T1 %LIMIT%';
	}
}