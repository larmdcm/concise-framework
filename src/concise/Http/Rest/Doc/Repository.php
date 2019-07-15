<?php

namespace Concise\Http\Rest\Doc;

use Concise\Routing\Router;
use Concise\Routing\Route\Handle\CurrecyHandle;
use Concise\Http\Rest\Doc\Parse\DocParserFactory;
use Concise\Foundation\Config;
use Concise\Cache\Cache;
use Concise\Container\Container;

class Repository
{
	use RestDocRequestRealization;

	CONST SAVE_KEY = 'concise_rest_api_doc';

	protected $router;

	protected $doc;

	protected $config = [
		'name'   => 'MyApi',
		'return' => [],
		'params' => [],
		'request_handle' => null
	];

	protected $postData;

	protected $keyName;

	public function __construct ()
	{
		$this->config = array_merge($this->config,Config::scope('doc')->get() ? Config::scope('doc')->get() : []);
	}

	public function bind (Router $router)
	{
		$this->router = $router;
		return $this;
	}
	/**
	 * 渲染文档
	 * @param  string $view 
	 * @return mixed
	 */
	public function render ($view = '')
	{
		if (empty($view)) {
			$path = __DIR__ . '/Resources/index.html';
		} else {
			$path = __DIR__ . '/Resources/view/' . $view .'.html';
		}
		include $path;
	}

	/**
	 * 生成文档
	 * @param  boolean $readCache 
	 * @return mixed
	 */
	public function build ($readCache = false)
	{
		$methods = $this->router->methodGroup->getMethods();

		foreach ($methods as $method => $items) {
			if (!empty($items)) {
				foreach ($items as $rule) {
					if (is_string($rule->handle) && $rule->doc !== '') {
						$result = $this->buildClass($rule);
						$this->doc[] = [
							 'class'       => $result['class'],
							 'method'      => $result['method'],
							 'doc'         => $rule->doc,
							 'request'     => $rule->method,
							 'groupParams' => is_array($result['groupParams']) ? $result['groupParams'] : [],
							 'methodName'  => '',
							 'requestUrl'  => '',
							 'params'      => [],
							 'return'      => [],
						];
					}
				}
			}
		}
		if (!empty($this->doc)) {
			foreach ($this->doc as $k => $item) {
				$comment     = $this->getClassMethodComment($item['class'],$item['method']);
				
				if (!$comment) {
					continue;
				}

				$docParam    = isset($item['doc']['params']) ? $item['doc']['params'] : [];
				$docReturn   = isset($item['doc']['return']) ? $item['doc']['return'] : [];

				if (isset($item['groupParams']['doc'])) {
					$groupDocParam  = isset($item['groupParams']['doc']['params']) ? $item['groupParams']['doc']['params'] : [];
					$groupDocReturn = isset($item['groupParams']['doc']['return']) ? $item['groupParams']['doc']['return'] : [];
				} else {
					$groupDocParam  = [];
					$groupDocReturn = [];
				}


				$result = DocParserFactory::parse($comment);
				$this->doc[$k]['requestUrl']  = $result['requestUrl'];
				$this->doc[$k]['methodName']  = $result['methodName'];
				$this->doc[$k]['desc']        = $result['desc'];
				$this->doc[$k]['params']      = array_merge($this->config['params'],$groupDocParam,$docParam,$result['param']);
				$this->doc[$k]['return']      = array_merge($this->config['return'],$groupDocReturn,$docReturn,$result['return']);

			}
		}

		$this->buildSettings();
		$data = [];
		foreach ($this->doc as $doc)
		{	
			if (empty($doc['requestUrl'])) {
				continue;
			}
			$key = md5($doc['requestUrl']);
			$data[$key] = [
				'name'   => $doc['methodName'],
				'selectedUrl' => $key,
				'url'    => $doc['requestUrl'],
				'method' => $doc['request'],
				'remark' => $doc['desc'],
				'postparam'   => $doc['params'],
				'returnparam' => $doc['return'],
			];
		}
		$jsonStr = json_encode($data);
		$jsonStr = str_replace("var","name",$jsonStr);
		$jsonStr = str_replace("about","memo",$jsonStr);
		Cache::set(self::SAVE_KEY,$jsonStr);
	}

	public function getClassMethodComment ($className,$classMethod)
	{
		$ref = new \ReflectionMethod($className, $classMethod);

		$comment = $ref->getDocComment();

		return $comment;
	}


	protected function buildClass ($rule)
	{
		if ($rule->groupNumber != -1) {
			$groupParams = $this->router->group->getParams($rule->groupNumber);
		} else {
			$groupParams = $this->router->group->getDefaultParams();
		}
		list($handle,$className,$module) = CurrecyHandle::buildClass($rule->handle,$rule,$groupParams);
		return ['class' => $className,'method' => $handle[1],'groupParams' => $groupParams];
	}

	protected function buildSettings ()
	{
		$config = [
			'name' => $this->config['name'],
			'navs' => [
				[
					'name' => '首页',
					'url'  => '#/index'
				]
			],
			'requestUrl'     => $this->config['request_url'],
			'requestUrlList' => [
				"save" => "/doc/save",
				"read" =>  "/doc/read",
				"get"  => "/doc/get",
				"detail" => "/doc/detail"
			],
			'jsonFormatRead'        => $this->config['view']['json_format_read'],
			'accessTokenName'       => $this->config['rest_api_request']['access_token_name'],
			'accessTokenExpiryTime' => $this->config['rest_api_request']['access_token_expire_time'],
			"status" => [
				"success" => 0,
				"error" => 400,
				"notice" => 401,
				"access" => 403,
				"canfind" => 404,
				"fatal" => 500 
			]
		];
		return file_put_contents(__DIR__ . '/Resources/settings.json',json_encode($config));
	}
}