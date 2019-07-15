<?php

namespace Concise\Exception;

class RouteNotFoundException extends HttpException
{
	public function __construct()
    {
        parent::__construct(404, 'Route Not Found');
    }
}