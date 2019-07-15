<?php

namespace Concise\Curl\Parse;

class Xml extends Parse
{
	public function toArray ()
	{
		return $this->xmlTo(true);
	}

	public function toObject ()
	{
		return $this->xmlTo(false);
	}
}