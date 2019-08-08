<?php

use Concise\Container\Container;
use Concise\Http\Response;
use Concise\Foundation\App;
use Concise\View\View;
use Concise\Foundation\Config;
use Concise\Foundation\Facade\Cookie;

if ( !function_exists('p') ) 
{
	/**
	 * 打印调试
	 * @param  string|bool|array|object|null [打印调试信息]
	 * @return void
	 */
	function p ($resource)  {
	   if (is_bool($resource))
	   	  var_dump($resource);
	    else if (is_null($resource))
	   	  var_dump(null);
	    else if (request()->isCli() || App::$mod == 'swoole') {
	    	print_r($resource);
	    } else{
	   		echo "<pre style='padding:10px;border-radius:5px;background:#f5f5f5;border:1px solid #ccc;font-size:16px;'>";
	   		  print_r($resource);
	   		echo "</pre>";
	    }
	}
}

if (!function_exists('container'))
{
	/**
	 * 服务容器辅助函数
	 * @param  string $name 
	 * @param  array $params 
	 * @param  bool $singleton 
	 * @return object
	 */
	function container ($alias = '',$params = [],$singleton = true) {
		return empty($alias) ? Container::getInstance() : Container::getInstance()->make($alias,$params,$singleton);
	}
}

if (!function_exists('app')) {
	
	/**
	 * App辅助函数
	 * @return object
	 */
	function app () {
		return container('app');
	}
}

if (!function_exists('config'))
{
	/**
	 * 获取配置项
	 * @param  string $key   
	 * @param  string $value 
	 * @param  string $scope 
	 * @return mixed  
	 */
	function config ($key = '',$value = '',$scope = 'app') {
		$key = strtolower($key);
		if (empty($key) && empty($value)) {
			return container('config');
		}
		if (!empty($key) && empty($value)) {
			return container('config')->$scope->get($key);
		}
		if (!empty($key) && !empty($value)) {
			return container('config')->$scope->set($key,$value);
		}
		return false;
	}
}

if (!function_exists('request'))
{
	/**
	 * 获取Request请求对象
	 * @return object
	 */
	function request () {
		return container('request');
	}	
}

if (!function_exists('lang'))
{
	/**
	 * 语言包
	 * @param  string $name 
	 * @return string
	 */
	function lang ($name) {
		static $lang;
		if (empty($lang))
		{
			$path = container('env')->get('lang_path') . '/' . config('lang.default') . '.php';
			if (!is_file($path))
			{
				throw new \Exception(config('lang.default') . ' lang包不存在');
			}
			$lang = include $path;
		}
		return isset($lang[$name]) ? $lang[$name] : ''; 
	}
}

if (!function_exists('env')) 
{
	/**
	 * 环境变量读存删
	 * @param  string $key   
	 * @param  string $value 
	 * @return mixed
	 */
	function env ($key = '',$default = '') {
		$env = container('env');
		if (empty($key) && empty($default)) {
			return $env;
		}
		return $env->get($key,$default);
	}
}

if (!function_exists('response'))
{	
	/**
	 * 响应
	 * @param mixed $data        
	 * @param string $returnType 
	 * @param integer$code       
	 * @param array $header     
	 * @return object
	 */
	function response ($data = [],$returnType = '',$code = 200,$header = []) {
		return Response::create($data,$returnType,$code,$header);
	}
}

if (!function_exists('json'))
{
	/**
	 * 输出json
	 * @param  array $data 
	 * @param  array $header 
	 * @return mixed
	 */
	function json ($data = [],$header = []) {
		return response($data,'json',200,$header);
	}
}


if (!function_exists('redirect')) {
	/**
	 * 跳转
	 * @param  string $url 
	 * @param  array $header 
	 * @return mixed
	 */
	function redirect ($url,$header = []) {
		return response($url,'redirect',302,$header);
	}
}

if ( !function_exists('back') ) {
	/**
	 * 返回上一页面
	 * @param array $errors
	 * @return void
	 */
	function back ($errors = []) {
		return redirect(request()->server('HTTP_REFERER'))->with($errors);
	}
}

if ( !function_exists('view') ) {
	/**
	 * 视图辅助函数
	 * @param  string $template 
	 * @param  array $data     
	 * @return string|object
	 */
	function view ($template = '',$data = []) {
		$data['errors'] = errors();
		return View::make(Config::scope('template')->get('drive',''),Config::scope('template')->get() ? Config::scope('template')->get() : [],$template)->with($data);
	}
}

if (!function_exists('session')) {
	/**
	 * session辅助函数
	 * @param  string $name  
	 * @param  string $value 
	 * @return mixed     
	 */
	function session ($name = '',$value = '') {
		 $session = container('session');
		 if (is_null($name)) {
		 	return $session->delete(null);
		 }
		 if (empty($name) && empty($value)) {
		 	 return $session;
		 }
		 if (!empty($name) && empty($value)) {
		 	return $session->get($name);
		 }
		 return $session->set($name,$value);
	}
}


if ( !function_exists('errors') ) {
	/**
	 * errors服务提供者辅助函数
	 * @param  string $key   
	 * @param  string $value 
	 * @return mixed        
	 */
	function errors ($error = '') {
		$errorsService = container('app')->getServiceContainer('errors');
		if (is_null($error)) {
			return $errorsService;
		}
		if (empty($error)) {
			return $errorsService->all();
		}
		return $errorsService->append($error);
	}
}

if ( !function_exists('route') ) {
	/**
	 * 获取路由地址
	 * @param  string $name   
	 * @param  array $params 
	 * @return string
	 */
	function route ($name,$params = []) {
		return container('router')->route($name,$params);
	}
}

if ( !function_exists('cookie') ) {
    /**
     * Cookie管理
     * @param string|array  $name cookie名称，如果为数组表示进行cookie设置
     * @param mixed         $value cookie值
     * @param mixed         $option 参数
     * @return mixed
     */
    function cookie($name, $value = '', $option = null)
    {
        if (is_array($name)) {
            // 初始化
            Cookie::init($name);
        } elseif (is_null($name)) {
            // 清除
            Cookie::clear($value);
        } elseif ('' === $value) {
            // 获取
            return 0 === strpos($name, '?') ? Cookie::has(substr($name, 1), $option) : Cookie::get($name);
        } elseif (is_null($value)) {
            // 删除
            return Cookie::delete($name);
        } else {
            // 设置
            return Cookie::set($name, $value, $option);
        }
    }
}

if ( !function_exists('base_url') ) {
	/**
	 * 获取根目录
	 * @param  string $path 
	 * @return string
	 */
	function base_url ($path = '') {
		return env('base_path') . '/' . $path;
	}
}

if ( !function_exists('public_path') ) {
	/**
	 * 获取public目录
	 * @param  string $path 
	 * @return string
	 */
	function public_path ($path = '') {
		return base_url('public/' . $path);
	}
}