<?php
return [
	// 所使用驱动
	'drive'			  => '',
	// 是否开启模板编译缓存,设为false则每次都会重新编译
	'tpl_cache'   	  => true,
	// 模板起始路径
	'view_path'       => Concise\Foundation\Facade\Env::get('view_path'),
	'tpl_begin'   	  => '{{',
	'tpl_end'     	  => '}}',
	'tpl_raw_begin'   => '{!!',
	'tpl_raw_end'  	  => '!!}',
	'view_cache_path' => Concise\Foundation\Facade\Env::get('runtime_path') . '/temp', // 模板缓存目录
	// 模板文件后缀
	'view_suffix' 	  => 'php',
];