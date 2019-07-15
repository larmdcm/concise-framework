<?php

namespace Concise\Curl\Request;

class Xml extends Request
{
	protected $contentType = 'text/xml';

	public function setOpt ()
	{
        curl_setopt($this->ch, CURLOPT_URL, $this->url);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($this->ch, CURLOPT_POSTFIELDS,$this->data);
		$this->setCurlOptHeader();
		return $this;
	}
}