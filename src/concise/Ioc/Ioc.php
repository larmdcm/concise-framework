<?php

namespace Concise\Ioc;

use Concise\Container\Container;

class Ioc
{	

	/**
	 * 获取类对象实例
     * @access public
	 * @param  string $className 
     * @param  array $params  
	 * @return object
	 */
	public static function getInstance ($className,$params = [])
	{
		$paramArr = self::getMethodParams($className,'__construct',$params);

        return (new \ReflectionClass($className))->newInstanceArgs($paramArr);
	}

	/**
	 * 执行类方法
     * @access public
	 * @param  string $className   
	 * @param  string $methodsName 
	 * @param  array $params 
	 * @return string|array|object            
	 */
	public static function make ($className,$methodsName,$params = []) {
		 // 获取类的实例
        $instance = self::getInstance($className);
        // 获取该方法所需要依赖注入的参数
        $paramArr = self::getMethodParams($className, $methodsName,$params);
        if (!method_exists($instance,$methodsName)) 
        {
        	throw new \RuntimeException(sprintf("%s->%s Method Not Exists",$className,$methodsName));
        }
        return call_user_func_array([$instance,$methodsName],$paramArr);
	}
	/**
	 * 获取类的方法参数
     * @access public
	 * @param  string $className  
	 * @param  string $methodsName 
	 * @param  array $arguments 
	 * @return array   
	 */
	public static function getMethodParams ($className,$methodsName = '__construct',$arguments = [])
	{
		// 通过反射获得该类
        $class = new \ReflectionClass($className);
        $paramArr = []; // 记录参数，和参数类型

        // 服务容器
        $container = Container::getInstance();
                
        // 判断该类是否存在该方法
        if ($class->hasMethod($methodsName)) {
            // 获得该方法
            $construct = $class->getMethod($methodsName);

            // 判断方法是否有参数
            $params = $construct->getParameters();

            if (count($params) > 0) {

                // 判断参数类型
                foreach ($params as $key => $param) {

                    if ($paramClass = $param->getClass()) {
                        // 获得参数类型名称
                        $paramClassName = $paramClass->getName();
                        // 获得参数类型
                        $paramsClassNames = explode("\\",$paramClass->getName());
                        $paramsClassName  = lcfirst($paramsClassNames[count($paramsClassNames) - 1]);

                        $paramArr[] = $container->has($paramsClassName)
                        			  ? $container->make($paramsClassName)
                        			  : (new \ReflectionClass($paramClass->getName()))->newInstanceArgs(self::getMethodParams($paramClassName));

                    }
                    if ($paramsName = $param->getName()) {
                    	if (isset($arguments[$paramsName])) {
                    		$paramArr[] = $arguments[$paramsName];
                    	} elseif ($param->isDefaultValueAvailable()) {
                    		$paramArr[] = $param->getDefaultValue();
                    	}
                    }
                }
            }
        }

        return $paramArr;
	}

    /**
     * 获取函数的参数
     * @access public
     * @param  mixed $func  
     * @param  array $arguments 
     * @return array   
     */
    public static function getFuncParams ($func,$arguments = [])
    {
        $reflectionFunc = new \ReflectionFunction($func);
        $paramArr = []; // 记录参数，和参数类型

         // 服务容器
        $container = Container::getInstance();
        $params = $reflectionFunc->getParameters();

        if (count($params) > 0) {
            foreach ($params as $param) {
                if ($paramClass = $param->getClass()) {
                    // 获得参数类型名称
                    $paramClassName = $paramClass->getName();
                    // 获得参数类型
                    $paramsClassNames = explode("\\",$paramClass->getName());
                    $paramsClassName  = lcfirst($paramsClassNames[count($paramsClassNames) - 1]);

                    $paramArr[] = $container->has($paramsClassName)
                                  ? $container->make($paramsClassName)
                                  : (new \ReflectionClass($paramClass->getName()))->newInstanceArgs(self::getMethodParams($paramClassName));
                }
                 if ($paramsName = $param->getName()) {
                    if (isset($arguments[$paramsName])) {
                        $paramArr[] = $arguments[$paramsName];
                    } elseif ($param->isDefaultValueAvailable()) {
                        $paramArr[] = $param->getDefaultValue();
                    }
                }
            }
        }
        return $paramArr;
    }
}