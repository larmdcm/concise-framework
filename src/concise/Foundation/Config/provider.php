<?php

return [
	'errors' => [
		'class'     => 'Concise\Foundation\Provider\ErrorServiceProvider',
		'arguments' => [],
		'singleton' => true
	],
	'mapCsrfToken' => [
		'class' 	=> 'Concise\Foundation\Provider\CsrfTokenMapServiceProvider',
		'arguments' => [],
		'singleton' => true
	]
];