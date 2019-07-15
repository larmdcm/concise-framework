<?php

namespace Concise\Http\Rest\Doc;

use Concise\Cache\Cache;
use Concise\Http\Rest\Rest;
use Concise\Container\Container;

trait RestDocRequestRealization
{
	public function read ()
	{
		$data    = json_decode(Cache::get(self::SAVE_KEY),true);
		$newData = []; 
		foreach ($data as $k => $v) {
			array_push($newData,['name' => $v['name'],'url' => $k]);
		}
		return Rest::correct($newData);
	}

	public function get ()
	{
		$this->keyName = Container::get('request')->post('url','');
		if (empty($this->keyName)) {
			return Rest::fail([],1001,"key name not exists!");
		}
		$data = json_decode(Cache::get(self::SAVE_KEY),true);
		if (empty($data)) {
			return Rest::fail([],1001,"key name is not defined!");
		}
		return Rest::correct($data[$this->keyName]);
	}

	public function detail ()
	{
		$this->postData = Container::get('request')->post();
		$url      = substr($this->config['request_url'], -1) != '/' 
						  ? $this->config['request_url'] . '/' . $this->postData['url'] : $this->config['request_url'] . $this->postData['url'];
        $url      = str_replace("\r","",$url);
        $url      = str_replace("\n","",$url);

		$method   = $this->postData['method'];
		$postData = $this->postData['data'];
		$accessTokenName = $this->config['rest_api_request']['access_token_name'];
		$accessToken = isset($postData[$accessTokenName]) ? $postData[$accessTokenName] : '';

		unset($postData[$accessTokenName]);

		$start    = microtime(true);
		$result   = is_callable($this->config['request_handle']) 
		? call_user_func_array($this->config['request_handle'], [$method,$url,$postData,[$accessTokenName => $accessToken]]) 
		: [];
		$end      = microtime(true);
		return Rest::correct([
			'retData'      => $result,
			'responseTime' => round($end - $start,3),
			'dateTime'     => date('Y-m-d H:i:s',time())
		]);
	}
}