<?php

namespace Concise\Database;

abstract class Model
{
	/**
	 * 主键key
	 * @var string
	 */
	protected $primaryKey = 'id';

	/**
	 * 数据表名称
	 * @var string
	 */
	protected $name;

	/**
	 * 数据表
	 * @var string
	 */
	protected $table;

	/**
	 * 数据
	 * @var array
	 */
	protected $data = [];

	/**
	 * Db
	 * @var Db
	 */
	protected $db;

	/**
	 * 初始化
	 * @param array $data
	 */
	public function __construct ($data = [])
	{
		if (!empty($data)) {
			$this->data = $data;
		}

		if (!$this->table) {
			$this->table = $this->uncamelize(basename(__CLASS__));
		}
	}

	/**
	 * 获取db对象
	 * @return Db
	 */
	public function getDb ()
	{
		return $this->name ? Db::name($this->name) : Db::table($this->name);
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
}