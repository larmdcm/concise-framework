<?php

return [
	 // 是否开启日志记录
	 'is_record'  => true,
		 // 目录格式
	 'dir_format' => function () {
	 	 return date('Y-m');
	 },
	 // 文件名格式
	 'file_format' => function () {
	 	 return date('d');
	 },
	 'ext' 		  => 'log'
]