<?php

return [
	'app_namespace'  => 'App',
	'app_debug'      => true,

	'return_type'    => 'json',
	'json_format'    =>  true,

	'date_time_zone' => 'PRC',

	'error_handle'   => [
		'title'   => '哎呀~页面出错啦',
		'output'  => 'html',

		'message' => 'Whoops! There was an error.',

		'custom_error_handle' => ''
	]
];