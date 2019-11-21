<?php

namespace Concise\Database\Connection;

use PDO;
use Concise\Database\Connection;

class Sqlsrv extends Connection
{

	/**
	 * 连接参数
	 * @var array
	 */
    protected $connectionParams = [
        PDO::ATTR_CASE              => PDO::CASE_NATURAL,
        PDO::ATTR_ERRMODE           => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_ORACLE_NULLS      => PDO::NULL_NATURAL,
        PDO::ATTR_STRINGIFY_FETCHES => false,
    ];

	/**
	 * 解析dsn
	 * @return string
	 */
	public function parseDsn ()
	{
		$dsn = 'sqlsrv:Database=' . self::$config['database'] . ';Server=' . self::$config['host'];

        if (!empty(self::$config['port'])) {
            $dsn .= ',' . self::$config['port'];
        }

        return $dsn;
	}
}