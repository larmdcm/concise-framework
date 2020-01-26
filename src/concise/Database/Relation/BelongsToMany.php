<?php

namespace Concise\Database\Relation;

class BelongsToMany extends Relation
{
	/**
	 * 初始化
	 * @param  object  $model       
	 * @param  object  $relationModel
	 * @param  object  $middleModel
	 * @param  string  $foreignKey 
	 * @param  string  $relationKey 
	 * @return object             
	 */
	public function __construct ($model,$relationModel,$middleModel,$foreignKey,$relationKey)
	{
		$this->model = $model;
		$this->relationModel = $relationModel;
		$this->middleModel = $middleModel;
		$this->foreignKey = $foreignKey;
		$this->relationKey = $relationKey;
	}

	/**
	 * 获取查询
	 * @return object
	 */
	public function getQuery ()
	{
		return $this->relationModel->where(function ($query) {
			return $query->whereIn(
				$this->relationModel->getPrimaryKey(),
				$this->middleModel->where($this->relationKey,$this->model->{$this->model->getPrimaryKey()})
								  ->column($this->foreignKey)
			);
		});
	}

	/**
	 * 获取关联模型数据
	 * @return mixed
	 */
	public function getRelationModelData ()
	{
		return $this->getQuery()->select();
	}

	/**
	 * 关联写入
	 * @param  mixed $relations 
	 * @param  array $data      
	 * @return mixed            
	 */
	public function attach ($relations,$data = [])
	{
		$relations = is_array($relations) ? $relations : [$relations];
		array_walk($relations,function ($relationKey) use ($data) {
			$data[$this->foreignKey] = $relationKey;
			$data[$this->relationKey] = $this->model->{$this->model->getPrimaryKey()};
			$this->middleModel->save($data);
		});
		return true;
	}

	/**
	 * 关联删除
	 * @param  mixed $relations 
	 * @return mixed            
	 */
	public function detach ($relations)
	{
		$relations = is_array($relations) ? $relations : [$relations];
		array_walk($relations,function ($relationKey) {
			$this->middleModel->where($this->foreignKey,$relationKey)->delete();
		});
		return true;
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