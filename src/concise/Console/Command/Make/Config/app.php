<?php

return [
	// 顶级命名空间
	'app_namespace'  => env("APP_NAMESPACE",'App'),
	// 是否调试模式
	'app_debug'      => env("APP_DEBUG",false),
	// 请求返回类型
	'return_type'    => 'json',
	// 是否格式化json
	'json_format'    =>  true,
	// 默认时区
	'date_time_zone' => 'PRC',
	// 错误处理
	'error_handle'   => [
		// 错误页面标题
		'title'   => '哎呀~页面出错啦',
		// 输出类型
		'output'  => 'html',
		// 错误小写
		'message' => 'Whoops! There was an error.',
		// 自定义错误处理
		'custom_error_handle' => ''
	],
	// 服务提供者
	'provider' => [
		'mapRoute' => [
			'class'     => 'App\Provider\RouteServiceProvider',
			'arguments' => [],
			'singleton' => true
		]
	]
];