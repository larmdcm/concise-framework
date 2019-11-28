<?php

namespace Concise\Database;

use PDO;
use Concise\Database\Exception\DBException;
use Concise\Database\Exception\SQLException;

class Connection
{
	/**
	 * 对象实例
	 * @var object
	 */
	private static $instance = null;

	/**
	 * PDO instance
	 * @var object
	 */
	private static $pdo = null;

	/**
	 * 连接配置
	 * @var array
	 */
	protected static $config = [
		'drive'      => 'mysql',
		'socket'     => '',
		'host' 	     => '127.0.0.1',
		'port'       => 3306,
		'username'   => '',
		'password'   => '',
		'database'   => '',
		'charset'    => 'utf8',
		'prefix'     => '',
		'persistent' => false
	];

	/**
	 * 上一次执行的sql
	 * @var string
	 */
	private $lastSql;

	/**
	 * 连接参数
	 * @var array
	 */
	protected $connectionParams = [
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
	];

	/**
	 * 初始化
	 * @param array $config
	 */
	public function __construct ($config = [])
	{
		self::$config = array_merge(self::$config,$config);
	}

	/**
	 * 获取对象单例
	 * @param array $config
	 * @return object
	 */
	public static function getInstance ($config = [])
	{
		if (is_null(static::$instance)) {
			$className = sprintf('Concise\Database\Connection\%s',ucfirst($config['drive']));
			static::$instance = new $className($config);
		}
		return static::$instance;
	}

	/**
	 * 开启一个事务
	 * @return void
	 */
	public function beginTransaction ()
	{
		$this->getPdo()->beginTransaction();
	}

	/**
	 * 事务回滚
	 * @return void
	 */
	public function rollBack ()
	{
		$this->getPdo()->rollBack();
	}

	/**
	 * 事务提交
	 * @return void
	 */
	public function commit ()
	{
		$this->getPdo()->commit();
	}

	/**
	 * 设置是否自动提交
	 * @return void
	 */
	public function setAutoCommit ($isAutoCommit = false)
	{
		$this->getPdo()->setAttribute(PDO::ATTR_AUTOCOMMIT,$isAutoCommit);
	}

	/**
	 * 获取预处理对象
	 * @param  string $sql    
	 * @param  array $params 
	 * @return boolean
	 */
	public function prepare ($sql,...$params)
	{
		if (!empty($params) && is_array($params[0])) {
			$params = $params[0];
		}

		$prepare = $this->getPdo()->prepare($sql);

		array_walk($params,function ($value,$key) use ($prepare) {
			if (is_array($value)) {
				$key = sprintf(':%s',array_keys($value)[0]);
				$value = array_values($value)[0];
			}
			if (is_numeric($key)) {
				$key += 1;
			}
			$prepare->bindValue($key,$value);
		});

		return $prepare;
	}

	/**
	 * 异常处理
	 * @param  object $prepare 
	 * @param  array $sqlMap 
	 * @param  closure $prepare 
	 * @return void      
	 */
	private function exceptionHandler ($prepare,$sqlMap,$callback = null)
	{
		try {
			$this->lastSql = $this->buildSql($sqlMap[0],$sqlMap[1]);
			$result = $prepare->execute();
			$errorInfo = $prepare->errorInfo();
			if (!empty($errorInfo[2])) {
				throw new SQLException($errorInfo[1] . '-' . $errorInfo[2]);
			}
		} catch (\PDOException $e) {
			throw new SQLException($e->getMessage());
		} catch (\Exception $e) {
			throw new DBException($e->getMessage());
		}
		return is_null($callback) ? null : $callback($result,$prepare);
	}

	/**
	 * build sql
	 * @param  string $sql       
	 * @param  array $params 
	 * @return string
	 */
	public function buildSql ($sql,$params)
	{
		if (!empty($params) && is_array($params[0])) {
			$params = $params[0];
		}

		array_walk($params,function ($value,$key) use (&$sql,$params) {
			$bindParams = $params;
			if (is_array($value)) {
				$key 	= array_keys($value)[0];
				$value  = array_values($value)[0];
				$bindParams[$key] = $value;
			}
			$sql = is_numeric($key) ? preg_replace_callback('/\?+/', function ($matchs) use ($bindParams,$key) {
				if (!isset($bindParams[$key])) {
					return '?';
				}
				return is_string($bindParams[$key]) ? $this->getPdo()->quote($bindParams[$key]) : $bindParams[$key];
			}, $sql,1) : preg_replace_callback(sprintf('/\:%s/',preg_quote($key)),function ($matchs) use ($bindParams) {
				$key = substr($matchs[0], 1);
				if (!isset($bindParams[$key])) {
					return $bindParams[$key];
				}
				return is_string($bindParams[$key]) ? $this->getPdo()->quote($bindParams[$key]) : $bindParams[$key];
			},$sql);
		});

		return $sql;
	}

	/**
	 * 执行一条预处理语句
	 * @param  string $sql    
	 * @param  array $params 
	 * @return boolean
	 */
	public function execute ($sql,...$params)
	{
		$prepare = $this->prepare($sql,...$params);
		return $this->exceptionHandler($prepare,[$sql,$params],function ($result) {
			return $result;
		});
	}

	/**
	 * 执行预处理语句
	 * @param  SqlPrepare $sqlPrepare 
	 * @return boolean
	 */
	public function executeSqlPrepare ($sqlPrepare)
	{
		return $this->execute($sqlPrepare->getSql(),...$sqlPrepare->getParams());
	}

	/**
	 * 执行一条预处理语句查询
	 * @param  string $sql    
	 * @param  array $params 
	 * @return PDOStatement         
	 */
	public function query ($sql,...$params)
	{
		$prepare = $this->prepare($sql,...$params);
		return $this->exceptionHandler($prepare,[$sql,$params],function ($result,$prepare) {
			return $prepare;
		});
	}

	/**
	 * 执行预处理语句查询
	 * @param  SqlPrepare $sqlPrepare 
	 * @return PDOStatement
	 */
	public function querySqlPrepare ($sqlPrepare)
	{
		return $this->query($sqlPrepare->getSql(),...$sqlPrepare->getParams());
	}
	/**
	 * 获取上次插入id
	 * @return integer
	 */
	public function getLastInsertId ()
	{
		return $this->getPdo()->lastInsertId();
	}

	/**
	 * 创建builder对象
	 * @return object
	 */
	public function createBuilder ()
	{
		$builder = sprintf('\Concise\Database\Builder\%s',ucfirst(self::$config['drive']));
		return new $builder($this);
	}

	/**
	 * 获取上次执行的sql
	 * @return string
	 */
	public function getLastSql ()
	{
		return $this->lastSql;
	}

	/**
	 * 关闭连接
	 * @return void
	 */
	public function close ()
	{
		self::$pdo = null;
	}

	/**
	 * 解析dsn
	 * @return string
	 */
	public function parseDsn ()
	{
		throw new DBException("parse connection dsn invalid error");
	}

	/**
	 * 创建一个pdo连接
	 * @return object
	 */
	private function createPdo ()
	{
		try {
			$pdo = new PDO($this->parseDsn(),self::$config['username'],self::$config['password'],[
				PDO::ATTR_PERSISTENT => self::$config['persistent']
			]);
		} catch (\PDOException $e) {
			throw new DBException($e->getMessage());
		}

		return $pdo;
	}

	/**
	 * 获取pdo对象
	 * @return PDO
	 */
	public function getPdo ()
	{
		if (is_null(self::$pdo)) {
			self::$pdo = $this->createPdo();
			array_walk($this->connectionParams,function ($value,$key) {
				self::$pdo->setAttribute($key,$value);
			});
		}

		return self::$pdo;
	}

	/**
	 * 获取连接配置
	 * @param string $key
	 * @return array
	 */
	public function getConfig ($key = '')
	{
		if (empty($key)) {
			return self::$config;
		}

		return self::$config[$key];
	}
}