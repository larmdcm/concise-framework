<?php
return [
    // 绑定地址
	'bind_host'  	  		=> '0.0.0.0',
	// 绑定端口
	'bind_port'  	 		=> 8811,
	// 是否开启静态处理
	'enable_static_handler' => false,
	// 静态处理目录,为空选择框架目录public下
	'document_root'         => '',
	// worker开启数量
	'worker_num' 	 		=> 1,
	// task_worker_num开启数量
	'task_worker_num' 		=> 1,
	//文件类型和Content-Type对应关系
	'content_type'          => [
		'xml'   => 'application/xml,text/xml,application/x-xml',
        'json'  => 'application/json,text/x-json,application/jsonrequest,text/json',
        'js'    => 'text/javascript,application/javascript,application/x-javascript',
        'css'   => 'text/css',
        'rss'   => 'application/rss+xml',
        'yaml'  => 'application/x-yaml,text/yaml',
        'atom'  => 'application/atom+xml',
        'pdf'   => 'application/pdf',
        'text'  => 'text/plain',
        'png'   => 'image/png',
        'jpg'   => 'image/jpg',
        'jpeg'  => 'image/jpeg',
        'pjpeg' => 'image/pjpeg',
        'gif'   => 'image/gif',
        'webp'  => 'image/webp',
        'csv'   => 'text/csv',
        'html'  => 'text/html,application/xhtml+xml,*/*'
	],
	// 可下载文件
	'download_type' => [
		'xls'   => 'application/x-xls,application/vnd.ms-excel',
        'tgz'   => '',
        'zip'   => '',
	],
	// 允许可跨域的url列表
	'access_url' => "*"
];