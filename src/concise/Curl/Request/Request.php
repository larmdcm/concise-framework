<?php

namespace Concise\Curl\Request;

use Concise\Curl\Parse\Parse;

class Request
{
	protected $ch;

	protected $url;

	protected $data;

	protected $header = [];

	protected $urlParam;

	protected $info;

	protected $content;

	  /**
     * 编码列表
     * @var array
     */
    public $encodes = ["ASCII","UTF-8","GBK","BIG5","Unicode"];

	/**
	 * contentType
	 * @var string
	 */
	protected $contentType;
	/**
	 * 输出编码
	 * @var string
	 */
	protected $charset = 'utf-8';


	public function __construct ($url,$data = [],$header = [])
	{
		if (!extension_loaded('curl')) {
			 throw new \BadFunctionCallException('not support: curl');
		}
		// 初始化句柄
		$this->ch = curl_init();
		// 获取url解析参数
		$urlParam = parse_url($url);
		// 设置请求参数
		$this->url      = $url;
		$this->urlParam = $urlParam;
		$this->data     = $data;
        // 设置参数
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        if (strtolower($this->urlParam['scheme']) == 'https') {
    	   curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false); // https请求不验证证书
           curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, false);//  https请求不验证hosts 
        }
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION,true); // 抓取跳转后的页面 
        curl_setopt($this->ch,CURLOPT_HEADER,false);

        $this->setTimeOut();
        
        if (!empty($header)) {
			$this->header = array_merge($this->header,$header);
		}
	}

	public function setCurlOptHeader ()
	{
		$header = $this->getHeader(true);

		if (!empty($header)) {
			curl_setopt($this->ch, CURLOPT_HTTPHEADER, $header);
		}
		return $this;
	}

	/**
	 * 设置头部信息
	 * @param array $header 
	 * @return object
	 */
	public function header ($name,$value = '')
	{
		if (!empty($value)) {
			$this->header[$name] = $value;
		} else {
			$this->header = array_merge($this->header,$name);
		}
		return $this;
	}

	public function getHeader ($toArr = false)
	{
		if ($toArr === false) {
			return $this->header;
		}
		if (empty($this->header)) {
			return [];
		}
		$header = [];

		foreach ($this->header as $k => $v) {
			$header[] = $k . (!is_null($v) ? ': ' . $v : '');
		}
		return $header;
	}

	/**
	 * 设置页面输出类型
	 * @param string $contentType
	 * @param string $charset    
	 * @return object
	 */
	public function contentType ($contentType,$charset = 'utf-8')
	{
		$this->charset 		= $charset ? $charset : $this->charset;
		$this->contentType  = $contentType ? $contentType : $this->contentType;
		$this->header['Content-Type'] = $this->contentType . ';' . 'charset=' . $this->charset;
		return $this;
	}

	public function setTimeOut ($time = 3)
	{
		curl_setopt($this->ch,CURLOPT_TIMEOUT,$time);
		return $this;
	}

	public function setOpt ()
	{
		return $this;
	}

	public function send ()
	{
		$this->setOpt();
        // 执行获取结果
        $result = curl_exec($this->ch);
        // 如果错误返回错误信息
        if (curl_errno($this->ch)) throw new \Exception(curl_error($this->ch));
        // 响应信息
        $this->info = curl_getinfo($this->ch);
        // 关闭
        curl_close($this->ch);
        $this->content = $result;
        return $this;
	}

    /**
     * 编码转换
     * @param  string $encoding 编码
     * @return string
     */
    public function encode ($encoding = 'utf-8',$deconding = '')
    {
        $encode = $deconding ?: mb_detect_encoding($this->content,$this->encodes);
        $this->content = iconv($encode, $encoding . '//IGNORE', $this->content);
        return $this;
    }

	public function read ()
	{
		return $this->content;
	}

	public function toArray ()
	{
		$contentType = explode(';', $this->info['content_type'])[0];
		return Parse::make($this->content,$contentType)->toArray();
	}

	public function toObject ()
	{
		$contentType = explode(';', $this->info['content_type'])[0];
		return Parse::make($this->content,$contentType)->toObject();
	}
}