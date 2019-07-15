<?php

namespace Concise\Config;

interface IConfig
{
	public function set (string $key,$value) : bool;
	public function get (string $key,$default);
	public function has(string $key) : bool;
	public function clear() : bool;
	public function delete(string $key) : bool;
	public function parse(string $path) : array;
}