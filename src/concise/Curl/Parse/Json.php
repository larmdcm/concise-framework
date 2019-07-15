<?php

namespace Concise\Curl\Parse;

class Json extends Parse
{
	public function toArray ()
	{
		return $this->jsonTo(true);
	}

	public function toObject ()
	{
		return $this->jsonTo(false);
	}
}