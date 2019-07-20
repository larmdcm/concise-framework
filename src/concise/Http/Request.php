<?php

namespace Concise\Http;

use Concise\Foundation\Arr;
use Concise\Http\Request\AuthenticateRequest;

class Request
{
    use AuthenticateRequest;
    
	/**
	 * 当前请求模块
	 * @var string
	 */
	protected $module;
	/**
	 * 当前请求控制器
	 * @var string
	 */
	protected $controller;
	/**
	 * 当前请求方法
	 * @var string
	 */
	protected $action;
	/**
	 * server请求变量
	 * @var array
	 */
	protected $server;
	/**
	 * post请求变量
	 * @var array
	 */
	protected $post;
	/**
	 * get请求变量
	 * @var array
	 */
	protected $get;
	/**
	 * request请求变量
	 * @var array
	 */
	protected $request;
	/**
	 * put请求变量
	 * @var array
	 */
	protected $put;

    /**
     * 请求变量
     * @var array
     */
    protected $param;

    /**
     * 路径变量
     * @var array
     */
    protected $params = [];

    /**
     * 请求头
     * @var array
     */
    protected $header;

	/**
	 * 获取post请求数据
	 * @param  string $key     
	 * @param  string $default 
	 * @param  string $filter
	 * @return mixed    
	 */
	public function post ($key = '',$default = '',$filter = '')
	{
		$this->post = $_POST;
		return $this->input($this->post,$key,$default,$filter);
	}
	/**
	 * 获取get请求数据
	 * @param  string $key     
	 * @param  string $default 
	 * @param  string $filter 
	 * @return mixed    
	 */
	public function get ($key = '',$default = '',$filter = '')
	{
		$this->get = $_GET;
		return $this->input($this->get,$key,$default,$filter);
	}
	/**
	 * 获取request请求数据
	 * @param  string $key     
	 * @param  string $default 
	 * @param  string $filter
	 * @return mixed    
	 */
	public function request ($key = '',$default = '',$filter = '')
	{
		$this->request = $_REQUEST;
		return $this->input($this->request,$key,$default,$filter);
	}
	/**
	 * 获取put请求数据
	 * @param  string $key     
	 * @param  string $default 
	 * @param  string $filter 
	 * @return mixed    
	 */
	public function put ($key = '',$default = '',$filter = '')
	{
		$content = empty(file_get_contents('php://input')) ? [] : file_get_contents('php://input');
		$put     = [];
		if (false !== strpos($this->contentType(), 'application/json')) {
            $put = (array) json_decode($content, true);
        } else {
            parse_str($content, $put);
        }
        $this->put = $put;
        return $this->input($this->put,$key,$default,$filter);
	}
	/**
	 * 获取delete请求数据
	 * @param  string $key     
	 * @param  string $default 
	 * @param  string $filter
	 * @return mixed    
	 */
	public function delete ($key = '',$default = '',$filter = '')
	{
		return $this->put($key,$default,$filter);
	}
	/**
	 * 获取patch请求数据
	 * @param  string $key     
	 * @param  string $default 
	 * @param  string $filter
	 * @return mixed    
	 */
	public function patch ($key = '',$default = '',$filter = '')
	{
		return $this->put($key,$default,$filter);
	}
	/**
	 * 获取server请求数据
	 * @param  string $key     
	 * @param  string $default 
	 * @param  string $filter 
	 * @return mixed    
	 */
	public function server ($key = '',$default = '',$filter = '')
	{
		$this->server = $_SERVER;
		return $this->input($this->server,$key,$default,$filter);
	}
	/**
	 * 获取输入数据
	 * @param  array $data     
	 * @param  string $key     
	 * @param  string $default 
	 * @param  string $filter  
	 * @return mixed    
	 */
	public function input ($data = [],$key = '',$default = '',$filter = '')
	{
		if (empty($key))
		{
			return $data;
		}
		return Arr::get($data,$key,$default,$filter);
	}
	/**
	 * 获取header请求变量
	 * @param  string $key    
	 * @param  string $default 
	 * @return string          
	 */
    public function header($name = '', $default = '')
    {
	    $header = [];
        
        if (function_exists('apache_request_headers') && $result = apache_request_headers()) {
            $header = $result;
        } else {
            $server = $this->server ?: $_SERVER;
            foreach ($server as $key => $val) {
                if (0 === strpos($key, 'HTTP_')) {
                    $key          = str_replace('_', '-', strtolower(substr($key, 5)));
                    $header[$key] = $val;
                }
            }
            if (isset($server['CONTENT_TYPE'])) {
                $header['content-type'] = $server['CONTENT_TYPE'];
            }
            if (isset($server['CONTENT_LENGTH'])) {
                $header['content-length'] = $server['CONTENT_LENGTH'];
            }
        }
        $this->header = array_change_key_case($header);

        if (is_array($name)) {
            return $this->header = array_merge($this->header, $name);
        }

        if ('' === $name) {
            return $this->header;
        }

        $name = str_replace('_', '-', strtolower($name));

        return isset($this->header[$name]) ? $this->header[$name] : $default;
    }
    /**
     * 根据请求获取变量
     * @param  string $key     
     * @param  string $default 
     * @param  string $filter
     * @return mixed
     */
    public function param ($key = '',$default = '',$filter = '')
    {
        $requestMethod = $this->method();
        $requestVars   = [];

        switch ($requestMethod)
        {
            case 'POST':
                $requestVars = $this->post();
                break;
            case 'PUT':
            case 'DELETE':
            case 'PATCH':
                $requestVars = $this->put();
                break;
        }
        $this->param = array_merge($this->get(),$this->params,$requestVars);
        return $this->input($this->param,$key,$default,$filter);
    }

    /**
     * set params
     * @param  array $params 
     * @return object
     */
    public function params ($params)
    {
        $this->params = $params;
        return $this;
    }

    /**
     * 获取原生数据
     * @return string
     */
    public function raw ()
    {
        return file_get_contents("php://input");
    }

    /**
     * 获取json请求变量
     * @param  string $key    
     * @param  string $default 
     * @param  array $ignore 
     * @return string          
     */
    public function json ($key = '',$default = '',$filter = '')
    {
        $data = json_decode($this->raw(),true);
        return $this->input($data,$key,$default,$filter);
    }
    
    /**
     * 获取xml请求变量
     * @param  string $key    
     * @param  string $default 
     * @param  array $ignore 
     * @return string          
     */
    public function xml ($key = '',$default = '',$filter = '')
    {
        libxml_disable_entity_loader(true); 
         
        $xmlstring = simplexml_load_string($this->raw(), 'SimpleXMLElement', LIBXML_NOCDATA); 
         
        $data = json_decode(json_encode($xmlstring),true);

        return $this->input($data,$key,$default,$filter);
    }

    /**
     * 获取指定的参数
     * @access public
     * @param  string|array  $name 变量名
     * @param  string        $type 变量类型
     * @return mixed
     */
    public function only($name, $type = 'param')
    {
        $param = $this->$type();

        if (is_string($name)) {
            $name = explode(',', $name);
        }

        $item = [];
        foreach ($name as $key => $val) {

            if (is_int($key)) {
                $default = null;
                $key     = $val;
            } else {
                $default = $val;
            }

            if (isset($param[$key])) {
                $item[$key] = $param[$key];
            } elseif (isset($default)) {
                $item[$key] = $default;
            }
        }

        return $item;
    }

    /**
     * 排除指定参数获取
     * @access public
     * @param  string|array  $name 变量名
     * @param  string        $type 变量类型
     * @return mixed
     */
    public function except($name, $type = 'param')
    {
        $param = $this->$type();
        if (is_string($name)) {
            $name = explode(',', $name);
        }

        foreach ($name as $key) {
            if (isset($param[$key])) {
                unset($param[$key]);
            }
        }

        return $param;
    }
    
    /**
     * 当前请求 HTTP_CONTENT_TYPE
     * @access public
     * @return string
     */
    public function contentType()
    {
        $contentType = $this->server('CONTENT_TYPE');

        if ($contentType) {
            if (strpos($contentType, ';')) {
                list($type) = explode(';', $contentType);
            } else {
                $type = $contentType;
            }
            return trim($type);
        }

        return '';
    }
    /**
     * 获取pathinfo
     * @param bool $caseArray
     * @return array
     */
    public function pathinfo ($caseArray = false)
    {
    	$server = $this->server();
    	$params = isset($server['PHP_INFO']) ? $server['PHP_INFO'] : $server['REQUEST_URI'];
		if (strpos($params,'?') !== false) {
			$params = explode('?', $params)[0];
		}
		return $caseArray ? explode('/',trim($params,'/')) : ltrim($params,'/');
    }
    /**
     * 获取当前请求模块
     * @param string $module 
     * @return mixed
     */
    public function module ($module = '')
    {
    	return empty($module) ? strtolower($this->module) : $this->module = $module;
    }
    /**
     * 获取当前请求控制器
     * @param string $controller 
     * @return mixed
     */
    public function controller ($controller = '')
    {
    	return empty($controller) ? strtolower($this->controller) : $this->controller = $controller;
    }
    /**
     * 获取当前请求方法
     * @param string $action 
     * @return mixed
     */
    public function action ($action = '')
    {
    	return empty($action) ? strtolower($this->action) : $this->action = $action;
    }
	/**
	 * 获取请求方法
	 * @return string
	 */
	public function method ()
	{
		return !empty($this->server('REQUEST_METHOD')) ? $this->server('REQUEST_METHOD') : 'GET';
	}
    /**
     * 是否为GET请求
     * @access public
     * @return bool
     */
    public function isGet()
    {
        return $this->method() == 'GET';
    }

    /**
     * 是否为POST请求
     * @access public
     * @return bool
     */
    public function isPost()
    {
        return $this->method() == 'POST';
    }

    /**
     * 是否为PUT请求
     * @access public
     * @return bool
     */
    public function isPut()
    {
        return $this->method() == 'PUT';
    }

    /**
     * 是否为DELTE请求
     * @access public
     * @return bool
     */
    public function isDelete()
    {
        return $this->method() == 'DELETE';
    }

    /**
     * 是否为HEAD请求
     * @access public
     * @return bool
     */
    public function isHead()
    {
        return $this->method() == 'HEAD';
    }

    /**
     * 是否为PATCH请求
     * @access public
     * @return bool
     */
    public function isPatch()
    {
        return $this->method() == 'PATCH';
    }

    /**
     * 是否为OPTIONS请求
     * @access public
     * @return bool
     */
    public function isOptions()
    {
        return $this->method() == 'OPTIONS';
    }

    /**
     * 是否为ajax请求
     * @return boolean 
     */
    public function isAjax ()
    {
        return (!empty($this->server('HTTP_X_REQUESTED_WITH')) && strtolower($this->server('HTTP_X_REQUESTED_WITH')) == 'xmlhttprequest');
    }
    
    /**
     * 是否为cli
     * @return bool
     */
    public function isCli()
    {
        return PHP_SAPI == 'cli';
    }
     /**
     * 是否为cgi
     * @access public
     * @return bool
     */
    public function isCgi()
    {
        return strpos(PHP_SAPI, 'cgi') === 0;
    }
}