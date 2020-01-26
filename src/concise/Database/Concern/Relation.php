<?php

namespace Concise\Database\Concern;

use Concise\Database\Relation\HasOne;
use Concise\Database\Relation\HasMany;
use Concise\Database\Relation\BelongsTo;
use Concise\Database\Relation\BelongsToMany;

trait Relation
{
	/**
	 * 一对一关联
	 * @param  string  $modelName       
	 * @param  string  $foreignKey 
	 * @param  string  $primaryKey 
	 * @return object             
	 */
	public function hasOne ($modelName,$foreignKey = '',$primaryKey = 'id')
	{
		return new HasOne($this,new $modelName,$this->getRelationModelKey($modelName,$foreignKey),$this->getRelationPrimaryKey($primaryKey));
	}

	/**
	 * 一对多关联
	 * @param  string  $modelName       
	 * @param  string  $foreignKey 
	 * @param  string  $primaryKey 
	 * @return object             
	 */
	public function hasMany ($modelName,$foreignKey = '',$primaryKey = 'id')
	{
		return new HasMany($this,new $modelName,$this->getRelationModelKey($modelName,$foreignKey),$this->getRelationPrimaryKey($primaryKey));
	}

	/**
	 * 反向一对一关联
	 * @param  string  $modelName       
	 * @param  string  $foreignKey 
	 * @param  string  $primaryKey 
	 * @return object             
	 */
	public function belongsTo ($modelName,$foreignKey = '',$primaryKey = 'id')
	{
		return new BelongsTo($this,new $modelName,$this->getRelationModelKey($modelName,$foreignKey),$this->getRelationPrimaryKey($primaryKey));
	}

	/**
	 * 多对多关联
	 * @param  string  $modelName       
	 * @param  string  $middleModelName       
	 * @param  string  $foreignKey 
	 * @param  string  $relationKey 
	 * @return object             
	 */
	public function belongsToMany ($modelName,$middleModelName,$foreignKey = '',$relationKey = '')
	{
		return new BelongsToMany($this,new $modelName,new $middleModelName,$this->getRelationModelKey($modelName,$foreignKey),$this->getRelationModelKey(get_class($this),$relationKey));
	}

	/**
	 * 获取关系外键key
	 * @param  string $name 
	 * @param  string $foreignKey 
	 * @return string            
	 */
	protected function getRelationModelKey ($name,$foreignKey = '')
	{
		if (!empty($foreignKey)) {
			return $foreignKey;
		}
		$modelName = basename($name);
		return $this->uncamelize($modelName) . '_id';
	}

	/**
	 * 获取关系主键key
	 * @param  string $primaryKey 
	 * @return string            
	 */
	protected function getRelationPrimaryKey ($primaryKey = 'id')
	{
		return empty($primaryKey) ? $this->getPrimaryKey() : $primaryKey;
	}
}