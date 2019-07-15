<?php

namespace Concise\Curl\Request;

class Json extends Request
{
	protected $contentType = 'application/json';

	public function setOpt ()
	{
        curl_setopt($this->ch, CURLOPT_URL, $this->url);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($this->ch, CURLOPT_POSTFIELDS, is_array($this->data) ? json_encode($this->data) : $this->data);
		$this->setCurlOptHeader();
		return $this;
	}
}