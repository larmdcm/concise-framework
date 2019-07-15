<?php

namespace Concise\Foundation;

class Arr
{
   /**
    * 获取输入过滤数据
    * @access public
    * @param  array $data   
    * @param  string $name    
    * @param  string $default 
    * @param  mixed $filter 
    * @return string         
    */
  	public static function get (array $data = [],$name = '',$default = '',$filter = null)
  	{
         if (empty($data)) {
             return $default;
         }
         
         if (empty($name)) {
             return $data;
         }

         if (!strpos($name,'.')) {
             $result = isset($data[$name]) ? $data[$name] : $default;
         } else {
             $args   = explode('.',$name); // 最高可获取二维数组值
             $result = isset($data[$args[0]][$args[1]]) ? $data[$args[0]][$args[1]] : $default;            
         }

         if (is_null($filter)) {
            return $result;
         }

         $filters = is_array($filter) ? $filter : explode('|',$filter);
         foreach ($filters as $item) {
            $items  = explode(':',$item);
            if (function_exists($items[0])) {
                $params = array_merge([$result],count($items) > 1 ? explode(',',$items[1]) : []);
                $result = call_user_func_array($items[0], $params);
            }
         }
         
         return $result;
  	}
    /**
     * 设置值
     * @access public
     * @param array $data  
     * @param stromg $name  
     * @param string $value
     * @return bool 
     */
    public static function set (array &$data,$name,$value = '') : bool
    {
        if (!strpos($name,'.')) {
            $data[$name] = $value;
        } else {
            $args = explode('.',$name);
            if (!is_array($data[$args[0]])) {
                $newData = empty($data[$args[0]]) ? [] : [$args[0] => $data[$args[0]]];
                $data[$args[0]] = $newData;
            }
            $data[$args[0]][$args[1]] = $value;
        }
        return true;
    }
    /**
     * 删除值
     * @access public
     * @param  array &$data 
     * @param  string $name  
     * @return bool        
     */
    public static function delete (array &$data,$name) : bool
    {
        if (!strpos($name,',')) {
            if(isset($data[$name])) unset($data[$name]);
        } else {
           $args = explode('.',$name);
           if(isset($data[$args[0]][$args[1]])) unset($data[$args[0]][$args[1]]);
        }
        return true;
    }
    /**
     * 清除
     * @access public
     * @param  array &$data 
     * @return bool
     */
    public static function clear (array &$data) : bool
    {
        $data = [];
        return true;
    }  
    /**
     * 是否存在
     * @access public
     * @param  array   &$data 
     * @param  string  $key   
     * @return bool        
     */
    public static function has (array $data,string $key) : bool
    {
        if (!strpos($key,'.')) {
            return isset($data[$key]);
        }
        $keys = explode('.', $key);
        return isset($data[$keys[0]][$keys[1]]);
    }
    /**
     * 返回数组最后一位
     * @param  array  $data 
     * @return mixed    
     */
    public static function end (array $data) 
    {
        return end($data);
    }
}