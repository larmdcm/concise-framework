<?php

namespace Concise\Database\Concern;

trait SoftDelete 
{
	/**
	 * 获取软删除条件
	 * @return array
	 */
	protected function getSoftDeleteWhere ()
	{
		return function ($query) {
			$field = $this->deleteTime;
			$alias = $this->getDb()->getAlias();
			if (!empty($alias)) {
				$field = sprintf('%s.%s',$alias,$field);
			}
			return is_null($this->defaultSoftDelete) ? $query->where($field,'null') 
				   : $query->where($field,$this->defaultSoftDelete);
		};
	}

	/**
	 * 获取软删除数据
	 * @return string
	 */
	protected function getSoftDeleteValue ()
	{
		switch ($this->deleteTimeFieldType) {
			case 'datetime':
				return date('Y-m-d H:i:s',time());
				break;
			case 'integer':
				return time();
				break;
		}
		throw new \RuntimeException("getSoftDeleteData deleteTimeFieldType parse error");
	}

	/**
	 * 软删除
	 * @param  array $condtion 
	 * @param  boolean $force 
	 * @return mixed      
	 */
	public function delete ($condtion = [],$force = false)
	{
		$where = !empty($condtion) ? [$this->primaryKey => $this->data[$this->primaryKey]] : $condtion;
		return $force ? $this->getDb()->where($where)->delete()
			   : $this->isUpdate(true)->save([$this->deleteTime => $this->getSoftDeleteValue()],$where);
	}

	/**
	 * 软删除
	 * @param  mixed $condtion 
	 * @return bool         
	 */
	public static function destroy ($condtion = [],$force = false)
	{
		$model = new static();
		if (is_string($condtion) && strpos($condtion,',') !== false) {
			$condtion = explode(',',$condtion);
		}
		if (is_array($condtion) && !empty($condtion) && is_number_array($condtion)) {
			return $model->whereIn($model->primaryKey,$condtion)->delete([],$force);
		}
		return $model->delete($condtion,$force);
	}
}