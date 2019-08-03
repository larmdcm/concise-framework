<?php

namespace Concise\Routing;

use Concise\Http\Request;
use Concise\Routing\Route\Handle as RouteHandle;
use Concise\Routing\Route\Group as RouteGroup;
use Concise\Routing\Route\Rule as RouteRule;
use Concise\Routing\Route\MethodGroup as RouteMethodGroup;
use Concise\Routing\Route\RouteVar;
use Concise\Routing\Route\RoutePath;
use Concise\Routing\Route\RouteName;
use Concise\Exception\RouteNotFoundException;

class Router
{
	/**
	 * 请求对象
	 * @var object
	 */
	protected $request;

	/**
	 * 分组对象
	 * @var array
	 */
	protected $group;

	/**
	 * 请求方法组
	 * @var object
	 */
	protected $methodGroup;

	/**
	 * 路由变量解析对象
	 * @var object
	 */
	protected $routeVar;

	/**
	 * 路径解析对象
	 * @var object
	 */
	protected $routePath;

	/**
	 * 路由别名存储
	 * @var object
	 */
	protected $routeName;

	/**
	 * 路由路径参数
	 * @var array
	 */
	protected $routeParams;

	/**
	 * 当前方法
	 * @var string
	 */
	protected $currentAttachMethod;

	/**
	 * 分组参数
	 * @var array
	 */
	protected $groupParams = [];

	/**
	 * 资源路由
	 * @var array
	 */
	protected $rest = [
		'index'   => ['get','','index'],
		'create'  => ['get','/create','create'],
		'store'   => ['post','','store'],
		'show'    => ['get','/show/{id}','show'],
		'edit'    => ['get','/edit/{id}','edit'],
		'update'  => ['put','/{id}','update'],
		'destroy' => ['delete','/{id}','destroy'],
	];

	public function __construct (
		Request $request,RouteGroup $group,RouteMethodGroup $methodGroup,RouteVar $routeVar,RoutePath $routePath,
		RouteName $routeName
	)
	{
		$this->request 	   = $request;
		$this->group   	   = $group;
		$this->methodGroup = $methodGroup;
		$this->routeVar    = $routeVar;
		$this->routePath   = $routePath;
		$this->routeName   = $routeName;
	}

	public function __get ($key) 
	{
		return $this->$key;
	}
	
	/**
	 * 路由分组
	 * @param  array $params   
	 * @param  Closure $callback 
	 * @return object   
	 */
	public function group ($params,$callback = null)
	{
		if (is_null($callback)) {
			$groupParams = $this->groupParams;
			$this->groupParams = [];
			$this->group->create($groupParams)->after(function () use ($params) {
				if (is_callable($params)) {
					$params($this,$this->group);
				} else if (is_string($params) && is_file($params)) {
					include $params;
				}
				$this->currentAttachMethod = null;
			});
		} else {
			$this->group->create($params)->after(function () use ($callback) {
				is_callable($callback) && $callback($this,$this->group);
				$this->currentAttachMethod = null;
			});
		}
		return $this;
	}

	/**
	 * 添加路由规则
	 * @param  string $method 
	 * @param  string $rule   
	 * @param  mixed $handle 
	 * @return object         
	 */
	public function rule (string $method = 'GET',string $rule = '/',$handle = null)
	{
		$rule = new RouteRule($method,$rule,$this->group->getGroupNumber(),$handle);
		$this->methodGroup->attach($rule);
		$this->currentAttachMethod = $method;
		return $this;
	}

	/**
	 * 路由ANY请求
	 * @param  string $rule   
	 * @param  mixed $handle 
	 * @return object         
	 */
	public function any (string $rule,$handle)
	{
		return $this->rule('ANY',$rule,$handle);
	}

	/**
	 * 路由GET请求
	 * @param  string $rule   
	 * @param  mixed $handle 
	 * @return object         
	 */
	public function get (string $rule,$handle)
	{
		return $this->rule('GET',$rule,$handle);
	}

	/**
	 * 路由POST请求
	 * @param  string $rule   
	 * @param  mixed $handle 
	 * @return object         
	 */
	public function post (string $rule,$handle)
	{
		return $this->rule('POST',$rule,$handle);
	}

	/**
	 * 路由PUT请求
	 * @param  string $rule   
	 * @param  mixed $handle 
	 * @return object         
	 */
	public function put (string $rule,$handle)
	{
		return $this->rule('PUT',$rule,$handle);
	}

	/**
	 * 路由PATCH请求
	 * @param  string $rule   
	 * @param  mixed $handle 
	 * @return object         
	 */
	public function patch (string $rule,$handle)
	{
		return $this->rule('PATCH',$rule,$handle);
	}

	/**
	 * 路由DELETE请求
	 * @param  string $rule   
	 * @param  mixed $handle 
	 * @return object         
	 */
	public function delete (string $rule,$handle)
	{
		return $this->rule('DELETE',$rule,$handle);
	}

	/**
	 * 路由OPTIONS请求
	 * @param  string $rule   
	 * @param  mixed $handle 
	 * @return object         
	 */
	public function options (string $rule,$handle)
	{
		return $this->rule('OPTIONS',$rule,$handle);
	}

	/**
	 * 添加路由中间件
	 * @param  mixed $middleware 
	 * @return object             
	 */
	public function middleware ($middleware)
	{
		$middlewares = is_array($middleware) ? $middleware : [$middleware];
		!is_null($this->currentAttachMethod) && $this->methodGroup->setRuleParams($this->currentAttachMethod,['middleware' => $middlewares]);
		$this->groupParams['middleware'] = $middlewares;
		return $this;
	}

	/**
	 * 添加路由前缀
	 * @param  string $prefix 
	 * @return object         
	 */
	public function prefix ($prefix)
	{
		!is_null($this->currentAttachMethod) && $this->methodGroup->setRuleParams($this->currentAttachMethod,['prefix' => $prefix]);
		$this->groupParams['prefix'] = $prefix;
		return $this;
	}

	/**
	 * 设置命名空间
	 * @param  string $namespace 
	 * @return object         
	 */
	public function namespace ($namespace)
	{
		!is_null($this->currentAttachMethod) && $this->methodGroup->setRuleParams($this->currentAttachMethod,['namespace' => $namespace]);
		$this->groupParams['namespace'] = $namespace;
		return $this;
	}

	/**
	 * 设置模块
	 * @param  string $module 
	 * @return object         
	 */
	public function module ($module)
	{
		!is_null($this->currentAttachMethod) && $this->methodGroup->setRuleParams($this->currentAttachMethod,['module' => $module]);
		$this->groupParams['module'] = $module;
		return $this;
	}

	/**
	 * 文档
	 * @param  array $options 
	 * @return object 
	 */
	public function doc ($options = [])
	{
		!is_null($this->currentAttachMethod) && $this->methodGroup->setRuleParams($this->currentAttachMethod,['doc' => $options]);
		$this->groupParams['doc'] = $options;
		return $this;
	}

	/**
	 * build doc
	 * @param string $module 
	 * @param string $prefix
	 * @param string $name
	 * @return object
	 */
	public function buildApiDocumentRoute ($module = 'ApiDoc',$prefix = 'doc',$name = 'apidocument')
	{
		return $this->group(['module' => $module,'prefix' => $prefix],function () {
			 $this->get('home',"ApiDocumentController@home")->name($name . '.home');
			 $this->get('index',"ApiDocumentController@index")->name($name . '.index');
			 $this->post('show',"ApiDocumentController@show")->name($name . '.show');
			 $this->post('get',"ApiDocumentController@get")->name($name . '.get');
			 $this->post('detail',"ApiDocumentController@detail")->name($name . '.detail');
		});
	}

	/**
	 * 设置路由别名
	 * @param  string $name 
	 * @return object
	 */
	public function name ($name)
	{
		!is_null($this->currentAttachMethod) && $this->methodGroup->setRuleParams($this->currentAttachMethod,['name' => $name]);
		$this->routeName->set($name,$this->methodGroup->getCurrentRule($this->currentAttachMethod));
		return $this;
	}

	/**
	 * 获取路由地址
	 * @param  string $name 
	 * @param  array $params 
	 * @return string 
	 */
	public function route ($name,$params = [])
	{
		$rule = $this->routeName->get($name);
		$baseUrl = $this->request->server('REQUEST_SCHEME') . '://' . $this->request->server('HTTP_HOST');
		list($result,$routePath,$vars,$optVars) = $this->parseRoutPath($rule);
		$vars = array_merge($result['optVars'],$result['vars']);
		$paramPath = [];
		$queryStr = "";
		if (!empty($vars) && !empty($params)) {
			foreach ($vars as $key) {
				if (isset($params[$key])) {
					array_push($paramPath, $params[$key]);
					unset($params[$key]);				
				}
			}
		}
		if (!empty($params)) {
			$queryStr = "?" . http_build_query($params);
		}
		return $baseUrl . $result['path'] . (empty($paramPath) ? '' : '/' . implode('/', $paramPath)) . $queryStr;
	}

	/**
	 * 资源路由
	 * @param  string $name       
	 * @param  string $controller 
	 * @param  string $prefix
	 * @return void             
	 */
	public function resource ($name,$controller,$prefix = '')
	{
		foreach ($this->rest as $rest) {
			$method = $rest[0];
			$url = $name . $rest[1];
			$action = sprintf("%s@%s",$controller,$rest[2]);
			$routeName = (empty($prefix) ? '' : $prefix . '.') . $name . '.' . $rest[2];
			$this->{$method}($url,$action)->name($routeName);
		}
	}

	/**
	 * 解析路由路径
	 * @param  Rule   $rule 
	 * @return array
	 */
	protected function parseRoutPath ($rule)
	{
		$path         = '/' . $this->request->pathinfo();
		$result  = $this->routeVar->rule($rule->rule)->parse();
		$vars 	 = $result['vars'];
		$optVars = $result['optVars'];

		$groupParams = [];
		if ($rule->groupNumber != -1) {
			$groupParams = $this->group->getParams($rule->groupNumber);
		} else {
			$groupParams = $this->group->getDefaultParams();
		}
		if (!empty($rule->prefix)) {
			$result['path'] = (substr($rule->prefix,0,1) == "/" ? $rule->prefix : "/" . $rule->prefix) . $result['path'];
		} else {
			if (!empty($groupParams['prefix'])) {
				$result['path'] = (substr($groupParams['prefix'],0,1) == "/" ? $groupParams['prefix'] : "/" . $groupParams['prefix']) . $result['path'];
			}
		}

		if (!empty($vars) || !empty($optVars)) {
			$routePath = $this->routePath->path($path)->routePath($result['path'])->vars($vars)->optVars($optVars)->parse();
		} else {
			$routePath = ['path' => $path,'params' => []];
		}
		return [
			$result,
			$routePath,
			$vars,
			$optVars,
		];
	}

	/**
	 * 路由调度
	 * @return mixed
	 */
	public function dispatch ()
	{
		$method       = $this->request->param('route_method',$this->request->method(),'strtoupper');
		$rules        = $this->methodGroup->get($method);

		foreach ($rules as $rule)
		{
			list($result,$routePath,$vars,$optVars) = $this->parseRoutPath($rule);
			
			if (empty($routePath['path']) || $result['path'] !== $routePath['path']) {
				continue;
			}


			$combVars = array_merge($vars,$optVars);


			if (!(count($vars) == count($routePath['params']) || count($combVars) == count($routePath['params']))) {
				continue;
			}


			$count = count($combVars);

			$routeParams = [];

			for ($i = 0; $i < $count; $i++) {
				if (isset($routePath['params'][$i])) {
					$routeParams[$combVars[$i]] = $routePath['params'][$i];
				}
			}
			
			$this->request->params($routeParams);
			$this->routeParams = $routeParams;
			$result = RouteHandle::make($rule,$this)->prev();
			$this->methodGroup->after();
			return $result;
		}

		throw new RouteNotFoundException();
	}
}