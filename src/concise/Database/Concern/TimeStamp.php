<?php

namespace Concise\Database\Concern;

trait TimeStamp
{
	/**
	 * 是否自动写入时间
	 * @var boolean
	 */
	protected $autoWriteTimestamp = true;

	/**
	 * 自动写入创建保存时的时间字段 false表示关闭
	 * @var array
	 */
	protected $createTime = 'create_time';

	/**
	 * 自动写入修改时的时间字段 false表示关闭
	 * @var array
	 */
	protected $updateTime = 'update_time';

	/**
	 * 自动写入时间字段类型
	 * @var string
	 */
	protected $autoWriteTimestampFieldType = 'datetime';

	/**
	 * 自动写入时间字段
	 * @var array
	 */
	protected $autoWriteTimestampField = [];

	/**
	 * 获取写入时间
	 * @return string
	 */
	protected function getTimestamp ()
	{
		switch ($this->autoWriteTimestampFieldType) {
			case 'datetime':
				return date('Y-m-d H:i:s',time());
				break;
			case 'date':
				return date('Y-m-d',time());
				break;
			case 'integer':
				return time();
				break;
		}

		throw new \RuntimeException("getTimestamp autoWriteTimestampFieldType parse error");
	}

	/**
	 * 检查是否写入时间戳
	 * @return boolean
	 */
	protected function checkTimeStampWrite ()
	{
		if (!$this->autoWriteTimestamp) {
			return false;
		}

		array_walk($this->autoWriteTimestampField, function ($val,$key) {
			if (is_int($key)) {
				$field = $val;
				$isWrite = true;
			} else {
				$field = $key;
				$isWrite = $val;
			}

			if ($field && $isWrite !== false) {
				$this->data[$field] = $this->getTimestamp();
			}
		});

		if ($this->createTime && !$this->isUpdate) {
			$this->data[$this->createTime] = $this->getTimestamp();
		}

		if ($this->updateTime && $this->isUpdate) {
			$this->data[$this->updateTime] = $this->getTimestamp();
		}

		return true;
	}
}