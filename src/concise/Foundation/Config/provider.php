<?php

return [
	'errors' => [
		'class'     => 'Concise\Foundation\Provider\ErrorServiceProvider',
		'arguments' => [],
		'singleton' => true
	],
	'csrfToken' => [
		'class' 	=> 'Concise\Foundation\Provider\CsrfTokenMapServiceProvider',
		'arguments' => [],
		'singleton' => true
	],
	'middlewareService' => [
		'class' => 'Concise\Foundation\Provider\MiddlewareServiceProvider',
		'arguments' => [],
		'singleton' => true
	],
	'mapAnnotation' => [
		'class' 	=> 'Concise\Foundation\Provider\AnnotationServiceProvider',
		'arguments' => [],
		'singleton' => true
	],
	'applicationService' => [
		'class' 	=> 'Concise\Foundation\Provider\ApplicationServiceProvider',
		'arguments' => [],
		'singleton' => true
	]
];