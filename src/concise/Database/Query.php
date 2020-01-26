<?php

namespace Concise\Database;

use PDO;
use Concise\Collection\Collection;
use Concise\Database\Exception\FailException;

class Query
{
	/**
	 * database connection
	 * @var object
	 */
	private $connection;

	/**
	 * builder object
	 * @var Builder
	 */
	private $buidler = null;

	/**
	 * 表名
	 * @var string
	 */
	private $tableName;

	/**
	 * 主键
	 * @var string
	 */
	private $primaryKey = 'id';

	/**
	 * 字段
	 * @var string
	 */
	private $fields = '*';

	/**
	 * 表别名
	 * @var string
	 */
	private $alias = '';
	
	/**
	 * wherePrepare
	 * @var array
	 */
	private $wherePrepare = [];

	/**
	 * joins
	 * @var array
	 */
	private $joins = [];

	/**
	 * order by sql
	 * @var string
	 */
	private $orderBySql = '';

	/**
	 * group by sql
	 * @var string
	 */
	private $groupBySql = '';

	/**
	 * having sql
	 * @var string
	 */
	private $havingSql = '';

	/**
	 * limit sql
	 * @var string
	 */
	private $limitSql = '';

	/**
	 * 构造函数初始化
	 * @param array $config
	 */
	public function __construct ($config = [])
	{
		if (!empty($config)) {
			$this->connection = Connection::getInstance($config);
		}
	}

	/**
	 * 原生查询
	 * @param  string $sql    
	 * @param  array $params 
	 * @return mixed         
	 */
	public function query ($sql,...$params)
	{
		return Collection::make(
			$this->createDataProxy(
				$this->getConnection()->query($sql,...$params)->fetchAll(PDO::FETCH_ASSOC)
			)
		);
	}

	/**
	 * 原生查询单条
	 * @param  string $sql    
	 * @param  array $params 
	 * @return mixed         
	 */
	public function queryOne ($sql,...$params)
	{
		return $this->createDataProxy(
			$this->getConnection()->query($sql,...$params)->fetch(PDO::FETCH_ASSOC)
		);
	}

	/**
	 * 原生执行更新写入或删除
	 * @param  string $sql    
	 * @param  array $params 
	 * @return boolean         
	 */
	public function execute ($sql,...$params)
	{
		require $this->getConnection()->execute($sql,...$params);
	}

	/**
	 * 开启一个事务
	 * @return void
	 */
	public function beginTransaction ()
	{
		$this->getConnection()->beginTransaction();
	}

	/**
	 * 事务回滚
	 * @return void
	 */
	public function rollBack ()
	{
		$this->getConnection()->rollBack();
	}

	/**
	 * 事务提交
	 * @return void
	 */
	public function commit ()
	{
		$this->getConnection()->commit();
	}

	/**
	 * 设置是否自动提交
	 * @return void
	 */
	public function setAutoCommit ($isAutoCommit = false)
	{
		$this->getConnection()->setAttribute(PDO::ATTR_AUTOCOMMIT,$isAutoCommit);
	}

	/**
	 * 事务控制操作
	 * @param  \Closure $callback 
	 * @return void 
	 */
	public function transaction (\Closure $callback)
	{
		try {
			$this->beginTransaction();
			call_user_func($callback,$this);
			$this->commit();
		} catch (\Exception $e) {
			$this->rollback();
			throw $e;
		}
	}

	/**
	 * 设置表名称
	 * @param  string $tableName 
	 * @return Query            
	 */
	public function table ($tableName = '')
	{
		if (!empty($tableName)) {
			$this->tableName = $tableName;
		}
		return $this;
	}

	/**
	 * 设置表名称加前缀
	 * @param  string $name 
	 * @return Query
	 */
	public function name ($name = '')
	{
		if (!empty($name)) {
			$this->tableName = $this->getPrefixTable($name);
		}
		return $this;
	}

	/**
	 * 设置表别名
	 * @param  string $name 
	 * @return Query    
	 */
	public function alias ($name = '')
	{
		if (!empty($name)) {
			$this->alias = $name;
			$this->tableName = $this->getBuilder()->fieldAlias($this->getTableName(),$name);
		}
		return $this;
	}

	/**
	 * 设置字段列表
	 * @param  mixed $field 
	 * @return Query
	 */
	public function field (...$field)
	{
		if (empty($field)) {
			return $this;
		}

		if (count($field) != 1) {
			return $this->parseArrayField($field);
		}

		if (is_array($field[0])) {
			return $this->parseArrayField($field[0]);
		}

		$this->fields = $field[0];

		return $this;
	}

	/**
	 * 解析数组字段
	 * @param  array $fields 
	 * @return Query       
	 */
	private function parseArrayField ($field)
	{
		$buidler = $this->getBuilder();
		$resultField = '';
		array_walk($field,function ($value,$key) use ($buidler,&$resultField) {
			if (is_array($value)) {
				$key 	= array_keys($value)[0];
				$value  = array_values($value)[0];
			}

			if (is_numeric($key)) {
				$resultField .= $buidler->escape($value) . ',';
			} else {
				$resultField .= $buidler->fieldAlias($key,$value) . ',';
			}
		});
		$this->fields = rtrim($resultField,',');
		return $this;
	}

	/**
	 * order by
	 * @param  mixed $field 
	 * @param  string $mode  
	 * @return Query        
	 */
	public function orderBy ($field,$mode = 'DESC')
	{
		$buidler = $this->getBuilder();
		$this->orderBySql = $buidler->orderBy(
			is_array($field) ? $buidler->parseField($field) : $field,$mode
		);
		return $this;
	}

	/**
	 * group by
	 * @param  mixed $field 
	 * @return Query    
	 */
	public function groupBy ($field)
	{
		$buidler = $this->getBuilder();
		$this->groupBySql = $buidler->groupBy(
			is_array($field) ? $buidler->parseField($field) : $field
		);
		return $this;
	}

	/**
	 * having
	 * @param  string $condtion 
	 * @return Query
	 */
	public function having ($condtion = '')
	{
		if (!empty($condtion)) {
			$this->havingSql = $this->getBuilder()->having($condtion);
		}
		return $this;
	}

	/**
	 * limit
	 * @param  string $offset 
	 * @param  string $limit  
	 * @return Query      
	 */
	public function limit ($offset = '',$limit = '')
	{
		if (!empty($offset) || is_numeric($offset)) {
			$this->limitSql = $offset . (empty($limit) ? '' : ',' . $limit);
		}
		return $this;
	}

	/**
	 * 便捷分页查询
	 * @param  integer $pageNo   
	 * @param  integer $pageSize 
	 * @return Query
	 */
	public function page ($pageNo,$pageSize)
	{
		return $this->limit(($pageNo - 1) * $pageSize,$pageSize);
	}

	/**
	 * 执行添加
	 * @param  array $data      
	 * @param  string $tableName 
	 * @return boolean        
	 */
	public function insert ($data = [],$tableName = '')
	{
		return $this->name($tableName)->getConnection()->executeSqlPrepare(
			$this->getBuilder()->insert($this->getTableName(),$data)
		);
	}

	/**
	 * 执行添加多条数据
	 * @param  array $data      
	 * @param  string $tableName 
	 * @return boolean        
	 */
	public function insertAll ($data = [],$tableName = '')
	{
		return $this->name($tableName)->getConnection()->executeSqlPrepare(
			$this->getBuilder()->insertAll($this->getTableName(),$data)
		);
	}

	/**
	 * 执行添加返回自增id
	 * @param  array $data      
	 * @param  string $tableName 
	 * @return integer         
	 */
	public function insertGetId ($data = [],$tableName = '')
	{
		return $this->insert($data,$tableName) ? $this->getConnection()->getLastInsertId() : 0;
	}

	/**
	 * 执行修改
	 * @param  array $data      
	 * @param  mixed $condtion  
	 * @param  string $tableName 
	 * @return boolean         
	 */
	public function update ($data,$condtion = [],$tableName = '')
	{
		return $this->name($tableName)->getConnection()->executeSqlPrepare(
			$this->getBuilder()->update($this->getTableName(),$data,
				$this->where($condtion)->parseWhere()
			)
		);
	}

	/**
	 * 设置字段
	 * @param array $fields 
	 * @param string $type   
	 */
	public function setField ($fields,$type = 'incrment')
	{
		return $this->getConnection()->executeSqlPrepare(
			$this->getBuilder()->setField($this->getTableName(),$fields,
				$this->parseWhere(),$type
			)
		);
	}	

	/**
	 * 字段自增
	 * @param string  $field 
	 * @param integer $step  
	 * @return mixed
	 */
	public function incrment ($field,$step = 1)
	{
		return $this->setField([$field => $step],'incrment');
	}

	/**
	 * 字段自减
	 * @param string  $field 
	 * @param integer $step  
	 * @return mixed
	 */
	public function decrement ($field,$step = 1)
	{
		return $this->setField([$field => $step],'decrement');
	}


	/**
	 * 执行删除
	 * @param  mixed $condtion  
	 * @param  string $tableName 
	 * @return boolean         
	 */
	public function delete ($condtion = [],$tableName = '')
	{
		return $this->name($tableName)->getConnection()->executeSqlPrepare(
			$this->getBuilder()->delete($this->getTableName(),
				$this->where($condtion)->parseWhere()
			)
		);
	}

	/**
	 * join连接查询
	 * @param  string $tableName 
	 * @param  string $condtion  
	 * @param  string $joinType  
	 * @return Query      
	 */
	public function join ($tableName = '',$condtion = '',$joinType = 'INNER')
	{
		$buidler = $this->getBuilder();
		if (is_array($tableName)) {
			$tableName = $buidler->fieldAlias($this->getPrefixTable(array_keys($tableName)[0]),array_values($tableName)[0]);
		} else {
			$tableName = $this->getPrefixTable($tableName);
		}
		$this->joins[] = $buidler->join($tableName,$condtion,$joinType);
		return $this;
	}

	/**
	 * left join连接查询
	 * @param  string $tableName 
	 * @param  string $condtion  
	 * @return Query      
	 */
	public function leftJoin ($tableName = '',$condtion = '')
	{
		return $this->join($tableName,$condtion,'LEFT');
	}

	/**
	 * right join连接查询
	 * @param  string $tableName 
	 * @param  string $condtion  
	 * @return Query      
	 */
	public function rightJoin ($tableName = '',$condtion = '')
	{
		return $this->join($tableName,$condtion,'RIGHT');
	}

	/**
	 * buildSelectSqlPrepare
	 * @return SqlPrepare
	 */
	public function buildSelectSqlPrepare ()
	{
		$wherePrepare = $this->parseWhere();

		$buidler = $this->getBuilder();

		$sql = $buidler->select(
			$this->getFields(),
			$this->getTableName(),
			$this->parseJoin()->getSql(),
			$wherePrepare->getSql(),
			$this->getGroupBySql(),
			$this->getHavingSql(),
			$buidler->parseOrderBy($this->getOrderBySql()),
			$buidler->limit($this->getLimitSql())
		);
		return $buidler->createSqlPrepare($sql,$wherePrepare->getParams());
	}

	/**
	 * build sql
	 * @return string
	 */
	public function buildSql ()
	{
		$sqlPrepare = $this->buildSelectSqlPrepare();
		return $this->getConnection()->buildSql($sqlPrepare->getSql(),$sqlPrepare->getParams());
	}

	/**
	 * 执行select查询
	 * @return Collection
	 */
	public function select ()
	{
		return Collection::make(
			$this->createDataProxy(
				$this->getConnection()->querySqlPrepare($this->buildSelectSqlPrepare())->fetchAll(PDO::FETCH_ASSOC)
			)
		);
	}

	/**
	 * 执行select查询
	 * @return Collection
	 */
	public function get ()
	{
		return Collection::make(
			$this->getConnection()->querySqlPrepare($this->buildSelectSqlPrepare())->fetchAll(PDO::FETCH_ASSOC)
		);
	}

	/**
	 * 查询返回某列
	 * @param  string $field 
	 * @param  string $name  
	 * @return array       
	 */
	public function column ($field,$name = null)
	{
		return $this->get()->column($field,$name);
	}

	/**
	 * 获取值
	 * @param  string $field 
	 * @return string        
	 */
	public function value ($field)
	{
		$data = $this->find();
		return $data->isEmpty() ? null : $data[$field];
	}

	/**
	 * 执行select查询单条
	 * @param mixed $condtion
	 * @return DataProxy
	 */
	public function find ($condtion = [])
	{
		return $this->createDataProxy(
			$this->getConnection()->querySqlPrepare($this->where(
				is_numeric($condtion) ? [$this->primaryKey => $condtion] : $condtion
			)->limit(1)->buildSelectSqlPrepare())->fetch(PDO::FETCH_ASSOC)
		);
	}

	/**
	 * 查询当条不存在抛出异常
	 * @param mixed $condtion
	 * @return DataProxy
	 */
	public function findOrFail ($condtion = [])
	{
		$result = $this->find($condtion);
		if ($result->isEmpty()) {
			throw new FailException();
		}
		return $result;
	}

	/**
	 * 查询当条不存在返回空数组
	 * @param mixed $condtion
	 * @return DataProxy
	 */
	public function findOrEmpty ($condtion = [])
	{
		$result = $this->find($condtion);
		return $result->isEmpty() ? [] : $result;
	}

	/**
	 * 查询当条不存在返回null
	 * @param mixed $condtion
	 * @return DataProxy
	 */
	public function findOrNull ($condtion = [])
	{
		$result = $this->find($condtion);
		return $result->isEmpty() ? null : $result;
	}

	/**
	 * 聚合查询条数
	 * @param  string $field  
	 * @return integer
	 */
	public function count ($field = '*')
	{
		return $this->field($this->getBuilder()->count($field))->find()->concise_count;
	}

	/**
	 * 聚合查询最大值
	 * @param  string $field  
	 * @return integer
	 */
	public function max ($field)
	{
		return $this->field($this->getBuilder()->max($field))->find()->concise_max;
	}

	/**
	 * 聚合查询最小值
	 * @param  string $field  
	 * @return integer
	 */
	public function min ($field)
	{
		return $this->field($this->getBuilder()->min($field))->find()->concise_min;
	}

	/**
	 * 聚合查询平均值
	 * @param  string $field  
	 * @return integer
	 */
	public function avg ($field)
	{
		return $this->field($this->getBuilder()->avg($field))->find()->concise_avg;
	}

	/**
	 * 聚合查询总分
	 * @param  string $field  
	 * @return integer
	 */
	public function sum ($field)
	{
		return $this->field($this->getBuilder()->sum($field))->find()->concise_sum;
	}

	/**
	 * 获取上次执行sql
	 * @return string
	 */
	public function getLastSql ()
	{
		return $this->getConnection()->getLastSql();
	}

	/**
	 * 解析where
	 * @return SqlPrepare
	 */
	private function parseWhere ()
	{
		$buidler = $this->getBuilder();
		$sql  	 = '';
		$params  = [];
		array_walk($this->wherePrepare,function ($item) use (&$sql,&$params,$buidler) {
			$params = array_merge($params,$item['condtion']->getParams());
			$sql .= $buidler->{$item['method']}($item['condtion']->getSql());
		});
		$this->wherePrepare = [];
		return $buidler->createSqlPrepare($sql,$params);
	}

	/**
	 * 解析join
	 * @return string
	 */
	private function parseJoin ()
	{
		$sql = '';
		array_walk($this->joins,function ($join) use (&$sql) {
			$sql .= $join . ' ';
		});
		$this->joins = [];
		return $this->getBuilder()->createSqlPrepare($sql,[]);
	}

	/**
	 * where条件
	 * @param  array $arguments 
	 * @return Query
	 */
	public function where (...$arguments)
	{
		return $this->whereExp('And',...$arguments);
	}

	/**
	 * whereOr条件
	 * @param  array $arguments 
	 * @return Query
	 */
	public function whereOr (...$arguments)
	{
		return $this->whereExp('Or',...$arguments);
	}

	/**
	 * null条件
	 * @param  string $field 
	 * @return Query
	 */
	public function whereNull ($field)
	{
		return $this->where($field,'null');
	}

	/**
	 * not null条件
	 * @param  string $field 
	 * @return Query
	 */
	public function whereNotNull ($field)
	{
		return $this->where($field,'notNull');
	}

	/**
	 * in条件
	 * @param  string $field 
	 * @param  array|string $value 
	 * @return Query
	 */
	public function whereIn ($field,$value)
	{
		return $this->where($field,'in',$value);
	}

	/**
	 * not in条件
	 * @param  string $field 
  	 * @param  array|string $value 
	 * @return Query
	 */
	public function whereNotIn ($field,$value)
	{
		return $this->where($field,'notIn',$value);
	}

	/**
	 * where between
	 * @param  string $field 
	 * @param  string|array $start 
	 * @param  string $end   
	 * @return Query 
	 */
	public function whereBetween ($field,$start,$end = '')
	{
		
		return $this->where($field,'between',empty($end) ? explode(',',$start) : [$start,$end]);
	}

	/**
	 * where between
	 * @param  string $field 
	 * @param  string|array $start 
	 * @param  string $end   
	 * @return Query 
	 */
	public function whereNotBetween ($field,$start,$end = '')
	{
		return $this->where($field,'notBetween',empty($end) ? explode(',',$start) : [$start,$end]);
	}

	/**
	 * where like
	 * @param  string $field     
	 * @param  string $keyworkds 
	 * @return Query      
	 */
	public function whereLike ($field,$keyworkds)
	{
		return $this->where($field,'like',$keyworkds);
	}

	/**
	 * where not like
	 * @param  string $field     
	 * @param  string $keyworkds 
	 * @return Query      
	 */
	public function whereNotLike ($field,$keyworkds)
	{
		return $this->where($field,'notLike',$keyworkds);
	}

	/**
	 * where exists
	 * @param  string $sql 
	 * @return Query
	 */
	public function whereExists ($sql)
	{
		return $this->where($sql,'exists');
	}

	/**
	 * where not exists
	 * @param  string $sql 
	 * @return Query 
	 */
	public function whereNotExists ($sql)
	{
		return $this->where($sql,'notExists');
	}

	/**
	 * where 字段比较
	 * @param  string $fieldOne 
	 * @param  string $exp      
	 * @param  string $fieldTwo 
	 * @param  string $exp 
	 * @return Query 
	 */
	public function whereColumn ($fieldOne,$exp,$fieldTwo,$expWhere = 'And')
	{
		return $this->whereExp($expWhere,$this->getBuilder()->column($fieldOne,$exp,$fieldTwo));
	}

	/**
	 * 根据exp添加where
	 * @param  array $arguments 
	 * @return Query
	 */
	public function whereExp ($exp,...$arguments)
	{
		if (empty($arguments) || empty($arguments[0])) {
			return $this;
		}

		$exp = ucfirst(empty($this->wherePrepare) ? '' : trim($exp));

		if ($arguments[0] instanceof \Closure) {
			return $this->whereClosure($exp,$arguments[0]);
		}

		$this->wherePrepare[] = ['method'   => sprintf('where%s',$exp),
								 'condtion' => $this->getBuilder()->parseWhere(...$arguments)];
		return $this;
	}

	/**
	 * where闭包
	 * @param  Closure $callback 
	 * @return Query 
	 */
	private function whereClosure ($exp,$callback)
	{
		$buidler = $this->getBuilder();
		$wherePrepare = $callback(static::newQuery()->setConnection($this->getConnection()))->parseWhere();

		$where = str_replace($buidler->getWhere() . ' ', '', $wherePrepare->getSql());
		$sql = empty($where) ? '' : sprintf('(%s)',$where);

		if (!empty($sql)) {
			$this->wherePrepare[] = [
				'method' => sprintf('where%s',$exp),
				'condtion' => $buidler->createSqlPrepare($sql,$wherePrepare->getParams())
			];
		}
		return $this;
	}

	/**
	 * 创建新查询对象
	 * @param  array $config 
	 * @return Query      
	 */
	public static function newQuery ($config = [])
	{
		return new static($config);
	}

	/**
	 * 创建数据代理
	 * @param  array $data 
	 * @return DataProxy
	 */
	public function createDataProxy ($data = [])
	{
		if ($data === false || (!empty($data) && count($data) == count($data,1))) {
			return new DataProxy($data);
		}

		return array_map(function ($item) {
			return new DataProxy($item);
		},$data);
		
	}

	/**
	 * 设置数据库连接对象
	 * @param Connection $connection
	 * @return Query
	 */
	public function setConnection ($connection)
	{
		$this->connection = $connection;
		return $this;
	}

	/**
	 * 获取数据库连接对象
	 * @return Connection
	 */
	public function getConnection ()
	{
		return $this->connection;
	}

	/**
	 * create builder
	 * @return Builder
	 */
	public function getBuilder ()
	{
		if (is_null($this->buidler)) {
			$this->buidler = $this->newBuilder();
		}
		return $this->buidler;
	}

	/**
	 * 获取当前表别名
	 * @return string
	 */
	public function getAlias ()
	{
		return $this->alias;
	}

	/**
	 * new builder
	 * @return Builder
	 */
	public function newBuilder ()
	{
		return $this->getConnection()->createBuilder();;
	}

	/**
	 * 获取前缀表名
	 * @param  string $name 
	 * @return string
	 */
	private function getPrefixTable ($name = '')
	{
		return $this->getConnection()->getConfig('prefix') . $name;
	}

	/**
	 * 获取表名
	 * @return string
	 */
	public function getTableName ()
	{
		return $this->tableName;
	}

	/**
	 * 设置主键
	 * @param string $primaryKey 
	 * @return Query 
	 */
	public function setPrimaryKey ($primaryKey)
	{
		$this->primaryKey = $primaryKey;
		return $this;
	}

	/**
	 * 获取主键
	 * @return string
	 */
	public function getPrimaryKey ()
	{
		return $this->primaryKey;
	}

	/**
	 * 获取属性并赋值
	 * @param  mixed $attr  
	 * @param  mixed $value 
	 * @return mixed  
	 */
	private function getAttributeAndSet ($attr,$value = null)
	{
		$result = $this->$attr;
		$this->$attr = is_null($value) ? $this->$attr : $value;
		return $result;
	}

	/**
	 * 获取字段列表
	 * @return string
	 */
	private function getFields ()
	{
		return $this->getAttributeAndSet('fields','*');
	}

	/**
	 * 获取order by sql
	 * @return string
	 */
	private function getOrderBySql ()
	{
		return $this->getAttributeAndSet('orderBySql','');
	}

	/**
	 * 获取group by sql
	 * @return string
	 */
	private function getGroupBySql ()
	{
		return $this->getAttributeAndSet('groupBySql','');
	}

	/**
	 * 获取having sql
	 * @return string
	 */
	private function getHavingSql ()
	{
		return $this->getAttributeAndSet('havingSql','');
	}

	/**
	 * 获取limitsql
	 * @return string
	 */
	private function getLimitSql ()
	{
		return $this->getAttributeAndSet('limitSql','');
	}


	/**
	 * 驼峰转下划线
	 * @param  string $camelCaps 
	 * @param  string $separator 
	 * @return string  
	 */
    private function uncamelize($camelCaps,$separator = '_')
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', "$1" . $separator . "$2", $camelCaps));
    }

	/**
	 * 无方法调用
	 * @param  string $method 
	 * @param  array $params 
	 * @return mixed
	 */
	public function __call ($method,$params)
	{
		if (substr($method, 0,7) == 'whereOr') {
			$fieldName = $this->uncamelize(substr($method,7));
			return call_user_func_array([$this,'whereOr'],array_merge([$fieldName],$params));
		}

		if (substr($method, 0,5) == 'where') {
			$fieldName = $this->uncamelize(substr($method,5));
			return call_user_func_array([$this,'where'],array_merge([$fieldName],$params));
		}

		if (substr($method,0,5) == 'getBy') {
			$fieldName = $this->uncamelize(substr($method,5));
			return $this->where($fieldName,$params[0])->find();
		}
	}
}