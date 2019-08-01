<?php

return [
	// 认证
	'auth' => [
		 // 选择驱动
		'drive' 	   		   => 'redis',
		// 过期时间
		'expire_time'  		   => 3600,
		// token名称
		'token_name'   		   => 'token',
		// 过期时间名称
		'expire_time_name'     => 'expire_time',
		// 用户数据调用模型
		'user_model'           => "App\\Model\\User",
		// 模型标识key
		 'user_model_key'      => "id",
		// token生成函数
		'token_generate_func'  => function ($mixed) {
			return \Concise\Hash\Hash::make($mixed . Container::get('request')->server('REQUEST_TIME') . md5(mt_rand(111111,999999)))->get();
		},
		// token获取错误
		'token_get_error_msg'    => 'token get error.',
		// token验证错误码
		'token_get_error_code'   => '402',
		// token验证错误消息
		'token_valid_error_msg'  => 'token valid error.',
		// token验证错误码
		'token_valid_error_code' => '403',

		'connect_options' => [
			'host'       => '127.0.0.1',
	        'port'       => 6379,
	        'password'   => '',
	        'select'     => 0,
	        'timeout'    => 0,
	        'persistent' => false
		]
	],
	// 限流
	'rate_limit' => [
		// 选择驱动
		'drive'       => 'redis',
		// 时间
		'time'        => 60,
		// 次数
		'limit'       => 600,
		// 错误消息
		'error_msg'   => "your have too many request",
		// 错误码
		'error_code'  => 401,
		// 连接选项
		'connect_options' => [
			'host'       => '127.0.0.1',
	        'port'       => 6379,
	        'password'   => '',
	        'select'     => 0,
	        'timeout'    => 0,
	        'persistent' => false
		]
	]
];