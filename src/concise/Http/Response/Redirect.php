<?php

namespace Concise\Http\Response;

use Concise\Http\Response;

class Redirect extends Response
{

	public function __construct ($data = '',$code = 302,$header = [])
	{
		parent::__construct($data,$code,$header);
	}

	/**
	 * 输出json格式数据
	 * @param  mixed $data 
	 * @return mixed
	 */
	public function output ($data)
	{
		$this->header['Location'] = $this->getTargetUrl();
		return;
	}

    /**
     * 获取跳转地址
     * @access public
     * @return string
     */
    public function getTargetUrl()
    {
        return $this->data;
    }
}