<?php

return [
	'errors' => [
		'class'     => 'Concise\Ioc\Provider\ErrorServiceProvider',
		'arguments' => [],
		'singleton' => true
	],
	'mapCsrfToken' => [
		'class' 	=> 'Concise\Ioc\Provider\CsrfTokenMapServiceProvider',
		'arguments' => [],
		'singleton' => true
	]
];