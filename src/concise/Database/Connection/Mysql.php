<?php

namespace Concise\Database\Connection;

use Concise\Database\Connection;

class Mysql extends Connection
{
	/**
	 * 解析dsn
	 * @return string
	 */
	public function parseDsn ()
	{
        if (!empty(self::$config['socket'])) {
            $dsn = 'mysql:unix_socket=' . self::$config['socket'];
        } elseif (!empty(self::$config['port'])) {
            $dsn = 'mysql:host=' . self::$config['host'] . ';port=' . self::$config['port'];
        } else {
            $dsn = 'mysql:host=' . self::$config['host'];
        }
        $dsn .= ';dbname=' . self::$config['database'];

        if (!empty(self::$config['charset'])) {
            $dsn .= ';charset=' . self::$config['charset'];
        }

        return $dsn;
	}
}