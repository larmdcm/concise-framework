<?php

namespace Concise\Database\Concern;

trait Conversion
{
    /**
     * 转换当前模型对象为数组
     * @return array
     */
    public function toArray()
    {
        return $this->data;
    }

    /**
     * 转换当前模型对象为JSON字符串
     * @param  integer $options json参数
     * @return string
     */
    public function toJson($options = JSON_UNESCAPED_UNICODE)
    {
        return json_encode($this->toArray(), $options);
    }
    
    public function __toString()
    {
        return $this->toJson();
    }

    // JsonSerializable
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}