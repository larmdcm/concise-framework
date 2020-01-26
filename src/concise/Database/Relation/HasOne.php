<?php

namespace Concise\Database\Relation;

class HasOne extends Relation
{
	/**
	 * 初始化
	 * @param  object  $model       
	 * @param  object  $relationModel        
	 * @param  string  $foreignKey 
	 * @param  string  $primaryKey 
	 * @return object             
	 */
	public function __construct ($model,$relationModel,$foreignKey,$primaryKey)
	{
		$this->model = $model;
		$this->relationModel = $relationModel;
		$this->foreignKey = $foreignKey;
		$this->primaryKey = $primaryKey;
	}

	/**
	 * 获取查询
	 * @return object
	 */
	public function getQuery ()
	{
		$this->relationModel->{$this->foreignKey} = $this->model->{$this->primaryKey};
		return $this->relationModel->where(function ($query) {
			return $query->where($this->foreignKey,$this->model->{$this->primaryKey});
		});
	}

	/**
	 * 获取关联模型数据
	 * @return mixed
	 */
	public function getRelationModelData ()
	{
		return $this->getQuery()->find();
	}

	/**
	 * 无方法调用
	 * @param  string $method 
	 * @param  array $params 
	 * @return mixed
	 */
    public function __call($method,$params)
    {
    	$query = $this->getQuery();
    	return call_user_func_array([$query,$method],$params);
    }
}