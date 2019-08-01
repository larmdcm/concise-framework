<?php

return [
	// 项目名称
	'name'            => 'MyApiDoc',
	// bbuild route
	'build_doc_route' => true,
	// 视图显示
	'view'            => [
		'json_format_read' => true,
	],
	//请求url
	'request_url'      => '',
	// api token request stroage
	'rest_api_request'     => [
		'access_token_name'        => 'token',
		'access_token_expire_time' => 'expire_time',
	],
	// 请求默认参数
	'params' => [],
	// 默认返回参数
	'return'          => [
		[
			'type'  => 'integer',
			'var'   => 'code',
			'about' => '返回的状态码'
		],
		[
			'type'  => 'string',
			'var'   => 'msg',
			'about' => '返回的消息'
		],
		[
			'type'  => 'object',
			'var'   => 'data',
			'about' => '返回的数据集'
		],
		[
			'type'  => 'integer',
			'var'   => 'date',
			'about' => '返回的时间戳'
		]
	]
];