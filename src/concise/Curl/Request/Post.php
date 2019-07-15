<?php

namespace Concise\Curl\Request;

class Post extends Request
{
	public function setOpt ()
	{
		$this->setCurlOptHeader();
        curl_setopt($this->ch, CURLOPT_URL, $this->url);
		curl_setopt($this->ch, CURLOPT_POSTFIELDS, is_array($this->data) ? http_build_query($this->data) : $this->data);
		curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'POST');
		return $this;
	}
}