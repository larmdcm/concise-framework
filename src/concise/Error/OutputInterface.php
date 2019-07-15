<?php
namespace Concise\Error;

interface OutputInterface
{
	public function output($data,$type = '',$code = 500,$header = []);
}