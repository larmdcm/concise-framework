<?php

namespace Concise\Curl\Request;

class Get extends Request
{
	public function setOpt ()
	{
		// 请求地址
		if (strpos($this->url,"?") !== false) {
			$this->url .= http_build_query($this->data);
		} else {
			$this->url .= '?' . http_build_query($this->data);
		}
        curl_setopt($this->ch, CURLOPT_URL, $this->url);
		curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'GET');
		$this->setCurlOptHeader();
		return $this;
	}
}