<?php

namespace Concise\Curl\Request;

class Patch extends Request
{
	public function setOpt ()
	{
        curl_setopt($this->ch, CURLOPT_URL, $this->url);
		curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "PATCH");
		curl_setopt($this->ch, CURLOPT_POSTFIELDS, is_array($this->data) ? http_build_query($this->data) : $this->data);
		$this->setCurlOptHeader();
		return $this;
	}
}