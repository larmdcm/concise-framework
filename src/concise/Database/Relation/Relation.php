<?php

namespace Concise\Database\Relation;

abstract class Relation
{
	/**
	 * model
	 * @var object
	 */
	private $model;

	/**
	 * relationModel
	 * @var object
	 */
	private $relationModel;

	/**
	 * 主键
	 * @var string
	 */
	private $primaryKey;

	/**
	 * 外键
	 * @var string
	 */
	private $foreignKey;

	/**
	 * 关联键
	 * @var string
	 */
	private $relationKey;

	/**
	 * 中间模型
	 * @var object
	 */
	private $middleModel;
}