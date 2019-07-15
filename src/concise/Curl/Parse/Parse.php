<?php

namespace Concise\Curl\Parse;

class Parse
{
	protected $content;

	public function __construct ($content)
	{
		$this->content = $content;
	}

	public static function make ($content,$type = '')
	{
		if ($type == 'application/json') {
			return new Json($content);
		} elseif ($type == 'text/xml') {
			return new Xml($content);
		}
		return new static($content);
	}


    /**
     * json转换
     * @param bool $toArr 
     * @return object
     */
    protected function jsonTo ($toArr = true)
    {
        try {
            $json = json_decode($this->content,$toArr);
            if (json_last_error() != JSON_ERROR_NONE) {
                throw new \InvalidArgumentException(json_last_error_msg());
            }
        } catch (\Exception $e) {
             throw $e;
        }
        return $json;
    }
    /**
     * xml转换
     * @param bool $toArr
     * @return object
     */
    protected function xmlTo ($toArr = true)
    {
        libxml_disable_entity_loader(true); 
         
        $xmlstring = simplexml_load_string($this->content, 'SimpleXMLElement', LIBXML_NOCDATA); 
         
        return json_decode(json_encode($xmlstring),$toArr); 
    }

    public function __call ($method,$param)
    {
    	return $this->content;
    }
}