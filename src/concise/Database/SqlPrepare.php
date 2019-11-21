<?php

namespace Concise\Database;

class SqlPrepare
{	
	/**
	 * sql语句
	 * @var string
	 */
	private $sql;

	/**
	 * 预绑定参数
	 * @var array
	 */
	private $params;

	/**
	 * 初始化
	 * @param string $sql    
	 * @param array $params 
	 */
	public function __construct ($sql,$params = [])
	{
		$this->sql = $sql;
		$this->params = $params;
	}

	/**
	 * 获取sql语句
	 * @return string
	 */
	public function getSql ()
	{
		return $this->sql;
	}

	/**
	 * 获取绑定的参数
	 * @return array
	 */
	public function getParams ()
	{
		return $this->params;
	}
}