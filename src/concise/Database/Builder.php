<?php

namespace Concise\Database;

use Concise\Database\Exception\SQLException;

class Builder
{	
	/**
	 * 表达式
	 * @var array
	 */
	private $exp = ['null','notNull','in','notIn','between','notBetween','like','notLike','exists','notExists'];

	/**
	 * database connection
	 * @var object
	 */
	private $connection;

	/**
	 * 初始化
	 * @param Connection $connection
	 */
	public function __construct (Connection $connection)
	{
		$this->connection = $connection;
	}

	/**
	 * 返回where条件语句
	 * @param  string $condtion 
	 * @return string           
	 */
	public function where ($condtion)
	{
		return sprintf(" %s %s",$this->getWhere(),$condtion);
	}

	/**
	 * get where语句
	 * @return string
	 */
	public function getWhere ()
	{
		return 'WHERE';
	}

	/**
	 * 返回where and 条件语句
	 * @param  string $condtion 
	 * @return string           
	 */
	public function whereAnd ($condtion)
	{
		return sprintf(' AND %s',$condtion);
	} 

	/**
	 * 返回where or 条件语句
	 * @param  string $condtion 
	 * @return string           
	 */
	public function whereOr ($condtion)
	{
		return sprintf(' OR %s',$condtion);
	} 

	/**
	 * 返回select语句
	 * @param  string $fields    
	 * @param  string $tableName 
	 * @return string            
	 */
	public function select (...$params)
	{
		return str_replace($this->getSelectFind(),$params,$this->getSelectSql());
	}

	/**
	 * 返回join语句
	 * @param  string $tableName 
	 * @param  string $condtion  
	 * @param  string $joinType  
	 * @return string     
	 */
	public function join ($tableName,$condtion = '',$joinType = 'INNER')
	{
		return sprintf(' %s JOIN %s ON %s',strtoupper($joinType),$tableName,$condtion);
	}

	/**
	 * 返回order by语句
	 * @param  string $field 
	 * @param  string $mode  
	 * @return string 
	 */
	public function orderBy ($field,$mode = 'DESC')
	{
		$mode = strtoupper($mode);
		return sprintf(' ORDER BY %s %s',$field,$mode);
	}

	/**
	 * 返回group by语句
	 * @param  string $field 
	 * @return string 
	 */
	public function groupBy ($field)
	{
		return sprintf(' GROUP BY %s',$field);
	}

	/**
	 * 返回having语句
	 * @param  string $condtion 
	 * @return string         
	 */
	public function having ($condtion)
	{
		return sprintf(' HAVING %s',$condtion);
	}

	/**
	 * 返回is null语句
	 * @param  string  $field 
	 * @return string 
	 */
	public function null ($field)
	{
		return $this->createSqlPrepare(
			sprintf('%s IS NULL',$this->escape($field))
		);
	}

	/**
	 * 返回is not null语句
	 * @param  string  $field 
	 * @return string 
	 */
	public function notNull ($field)
	{
		return $this->createSqlPrepare(
			sprintf('%s IS NOT NULL',$this->escape($field))
		);
	}

	/**
	 * 返回in语句对象
	 * @param string $field
	 * @param string|array $value
	 * @param string $exp
	 * @return SqlPrepare
	 */
	public function in ($field,$value,$exp = '')
	{
		if (is_string($value)) {
			$value = explode(',', $value);
		}

		return $this->createSqlPrepare(
			sprintf('%s %sIN (%s)',$this->escape($field),$exp,rtrim(str_repeat("?,",count($value)),',')),$value
		);
	}

	/**
	 * 返回not in语句对象
	 * @param string $field
	 * @param string|array $value
	 * @return SqlPrepare
	 */
	public function notIn ($field,$value)
	{
		return $this->in($field,$value,'NOT ');
	}

	/**
	 * 返回between语句对象
	 * @param string $field
	 * @param string $start
	 * @param string $end
	 * @param string $exp
	 * @return SqlPrepare
	 */
	public function between ($field,$start,$end = '',$exp = '')
	{
		if (empty($end) && is_string($start)) {
			$start = explode(',',$start);
		}
		if (is_array($start) && count($start) > 1) {
			$end = $start[1];
			$start = $start[0];
		}
		return $this->createSqlPrepare(
			sprintf('%s %sBETWEEN %s AND %s',$this->escape($field),$exp,'?','?'),[$start,$end]
		);
	}

	/**
	 * 返回not between语句对象
	 * @param string $field
	 * @param string $start
	 * @param string $end
	 * @return SqlPrepare
	 */
	public function notBetween ($field,$start,$end = '')
	{
		return $this->between($field,$start,$end,'NOT ');
	}

	/**
	 * 返回like语句对象
	 * @param  string $field     
	 * @param  string $keyworkds 
	 * @param  string $exp 
	 * @return SqlPrepare
	 */
	public function like ($field,$keyworkds,$exp = '')
	{
		return $this->createSqlPrepare(
			sprintf('%s %sLIKE %s',$this->escape($field),$exp,'?'),[$keyworkds]
		);
	}

	/**
	 * 返回not like语句对象
	 * @param  string $field     
	 * @param  string $keyworkds 
	 * @param  string $exp 
	 * @return SqlPrepare
	 */
	public function notLike ($field,$keyworkds)
	{
		return $this->like($field,$keyworkds,'NOT ');
	}

	/**
	 * 返回exists语句对象
	 * @param  string $sql 
	 * @param  string $exp 
	 * @return SqlPrepare
	 */
	public function exists ($sql,$exp = '')
	{
		return $this->createSqlPrepare (
			sprintf('%sEXISTS (%s)',$exp,$sql)
		);
	}

	/**
	 * 返回not exists语句对象
	 * @param  string $sql 
	 * @param  string $exp 
	 * @return SqlPrepare
	 */
	public function notExists ($sql)
	{
		return $this->exists($sql,'NOT ');
	}

	/**
	 * 返回字段比较语句
	 * @param  string $fieldOne 
	 * @param  string $exp      
	 * @param  string $fieldTwo 
	 * @return SqlPrepare 
	 */
	public function column ($fieldOne,$exp,$fieldTwo)
	{
		return $this->createSqlPrepare(
			sprintf('(%s %s %s)',$this->escape($fieldOne),$exp,$this->escape($fieldTwo))
		);
	}

	/**
	 * 解析条件表达式
	 * @param  array $arguments 
	 * @return mixed
	 */
	public function parseWhere (...$arguments)
	{
		$len = count($arguments);

		if ($len == 1) {
			return is_scalar($arguments[0]) ? $this->createSqlPrepare(
				(string)$arguments[0],[]
			) : $this->parseWhereArray($arguments[0]);
		}

		if ($len == 2) {
			if (in_array($arguments[1],['null','notNull','exists','notExists'])) {
				return $this->parseWhereExp($arguments[0],$arguments[1],null);
			}
			return $this->parseWhereArray([$arguments[0] => $arguments[1]]);
		}

		return $this->parseWhereExp($arguments[0],$arguments[1],$arguments[2]);
	}

	/**
	 * 解析数组条件
	 * @param  array $condtion 
	 * @return SqlPrepare  
	 */
	public function parseWhereArray ($condtion)
	{
		if ($condtion instanceof SqlPrepare) {
			return $condtion;
		}

		$count = count($condtion);
		if (($count == 2 || $count == 3) && array_keys($condtion) === range(0,$count - 1)) {
			return $this->parseWhereExp($condtion[0],$condtion[1],$count > 2 ? $condtion[2] : null);
		}

		return $this->createSqlPrepare($this->parseField(array_keys($condtion),true),array_values($condtion));
	}

	/**
	 * 解析exp条件
	 * @param  string $key   
	 * @param  string $exp   
	 * @param  string $value 
	 * @return SqlPrepare    
	 */
	public function parseWhereExp ($key,$exp,$value)
	{
		if (in_array($exp, $this->exp)) {
			if (!method_exists($this,$exp)) {
				throw new \RuntimeException(sprintf('%s表达式解析错误',$exp));
			}
			return call_user_func_array([$this,$exp], [$key,$value]);
		}
		return $this->createSqlPrepare(
			$this->parseField([$key],true,$exp),[$value]
		);
	}

	/**
	 * 生成insert语句
	 * @param  string $tableName 
	 * @param  array $data      
	 * @return string 
	 */
	public function insert ($tableName,$data = [])
	{
		$fields = $this->parseField(array_keys($data));
		$values = array_values($data);
		$sql 	= sprintf('INSERT INTO %s(%s) VALUES (%s)',$tableName,$fields,rtrim(str_repeat("?,",count($values)),','));
		return $this->createSqlPrepare($sql,$values);
	}

	/**
	 * 生成insert语句多条数据插入
	 * @param  string $tableName 
	 * @param  array $data      
	 * @return string 
	 */
	public function insertAll ($tableName,$data = [])
	{
		$sqlValues = '';
		$fields    = $this->parseField(array_keys($data[0]));
		$params    = [];
		array_walk($data,function ($item) use (&$sqlValues,&$params) {
			$values = array_values($item);
			$params = array_merge($params,$values);
			$sqlValues .= sprintf('(%s),',rtrim(str_repeat("?,",count($values)),','));
		});
		$sql = sprintf('INSERT INTO %s(%s) VALUES %s',$tableName,$fields,rtrim($sqlValues,','));
		return $this->createSqlPrepare($sql,$params);
	}

	/**
	 * 生成update语句
	 * @param  string $tableName 
	 * @param  array $data      
	 * @param  SqlPrepare $sqlPrepare      
	 * @return SqlPrepare 
	 */
	public function update ($tableName,$data = [],$sqlPrepare)
	{
		if (empty($sqlPrepare->getSql())) {
			throw new SQLException('缺少更新条件语句');
		}
		$fields    = $this->parseField(array_keys($data),true);
		$sql = sprintf('UPDATE %s SET %s %s',$tableName,$fields,$sqlPrepare->getSql());
		return $this->createSqlPrepare($sql,
			array_merge(array_values($data),$sqlPrepare->getParams())
		);
	}

	/**
	 * 设置字段语句
	 * @param string $tableName 
	 * @param array $data
	 * @param SqlPrepare $sqlPrepare
	 * @return SqlPrepare
	 */
	public function setField ($tableName,$data,$sqlPrepare,$type = 'incrment')
	{
		if (empty($sqlPrepare->getSql())) {
			throw new SQLException('缺少更新条件语句');
		}

		$fields = '';
		array_walk($data,function ($value,$key) use (&$fields,$type) {
			$fields .= 
			sprintf('%s = %s %s %s,',$this->escape($key),$this->escape($key),$type == 'incrment' ? '+' : '-',$value);
		});

		$sql = sprintf('UPDATE %s SET %s %s',$tableName,rtrim($fields,','),$sqlPrepare->getSql());

		return $this->createSqlPrepare($sql,
			$sqlPrepare->getParams()
		);
	}

	/**
	 * 生成delete语句
	 * @param  string $tableName 
	 * @param  SqlPrepare $sqlPrepare      
	 * @return string 
	 */
	public function delete ($tableName,$sqlPrepare)
	{
		if (empty($sqlPrepare->getSql())) {
			throw new SQLException('缺少删除条件');
		}
		$sql = sprintf('DELETE FROM %s %s',$tableName,$sqlPrepare->getSql());
		return $this->createSqlPrepare($sql,$sqlPrepare->getParams());
	}

	/**
	 * 解析字段
	 * @param  array $data 
	 * @param  bool $comb 
	 * @return string
	 */
	public function parseField ($data,$comb = false,$exp = '=')
	{
		$fields = '';
		array_walk($data,function ($value) use (&$fields,$comb,$exp) {
			$fields .= ($comb ? sprintf("%s %s %s",$this->escape($value),$exp,'?') : $this->escape($value)) . ',';
		});
		return rtrim($fields,',');
	}

	/**
	 * 字段别名获取
	 * @param  string $field 
	 * @param  string $alias 
	 * @return string
	 */
	public function fieldAlias ($field,$alias)
	{
		return sprintf('%s AS %s',$this->escape($field),$this->escape($alias));
	}

	/**
	 * 函数别名
	 * @param  string $field 
	 * @param  string $alias 
	 * @return string        
	 */
	public function funcAlias ($field,$alias)
	{
		return sprintf('%s AS %s',$field,$this->escape($alias));
	}

	/**
	 * 返回聚合查询条数语句
	 * @param  string $field string
	 * @return string
	 */
	public function count ($field = '*')
	{
		return $this->funcAlias(sprintf('COUNT(%s)',$field),'concise_count');
	}

	/**
	 * 返回聚合查询最大值语句
	 * @param  string $field string
	 * @return string
	 */
	public function max ($field)
	{
		return $this->funcAlias(sprintf('MAX(%s)',$field),'concise_max');
	}

	/**
	 * 返回聚合查询最小值语句
	 * @param  string $field string
	 * @return string
	 */
	public function min ($field)
	{
		return $this->funcAlias(sprintf('MIN(%s)',$field),'concise_min');
	}

	/**
	 * 返回聚合查询平均值语句
	 * @param  string $field string
	 * @return string
	 */
	public function avg ($field)
	{
		return $this->funcAlias(sprintf('AVG(%s)',$field),'concise_avg');
	}

	/**
	 * 返回聚合查询总分语句
	 * @param  string $field string
	 * @return string
	 */
	public function sum ($field)
	{
		return $this->funcAlias(sprintf('SUM(%s)',$field),'concise_sum');
	}


	/**
	 * 转义
	 * @param  string $str 
	 * @return string      
	 */
	public function escape ($str)
	{
		return $str;
	}

	/**
	 * 解析orderby
	 * @param  string $orderBy 
	 * @return string          
	 */
	public function parseOrderBy ($orderBy)
	{
		return $orderBy;
	}

	/**
	 * 返回select find
	 * @return array
	 */
	public function getSelectFind ()
	{
		return ['%FIELD%','%TABLE%','%JOIN%','%WHERE%','%GROUPBY%','%HAVING%','%ORDERBY%','%LIMIT%'];
	}

	/**
	 * 创建SqlPrepare
	 * @param  string $sql    
	 * @param  array $params 
	 * @return SqlPrepare         
	 */
	public function createSqlPrepare ($sql,$params = [])
	{
		return new SqlPrepare($sql,$params);
	}

	/**
	 * 获取数据库连接对象
	 * @return Connection
	 */
	public function getConnection ()
	{
		return $this->connection;
	}
}