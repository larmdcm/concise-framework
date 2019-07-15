<?php

namespace  Concise\Http\Rest\Doc\Parse;

class DocParser
{

    private $mainRegex = '/(\/\*\*.*?\*\s(api)?.*?\*\/\s*(public|private|protected)?\s*function\s+.*?\s*?\()/s';

    
    protected $savePath;
    
    protected $name = 'api';
    
    public $url = 'http://localhost';
    
    protected $controllerChange = true;
    
    protected $controllerTimes = 1;
    
    protected $methodChange = true;
    
    protected $methodTimes = 2;


    /**
     * 设置项目名称
     * @param string $name 项目名称
     * @return void
     */
    public function setName($name){
        $this->name = $name;
    }

    /**
     * 设置是否开启驼峰转匈牙利
     * @param bool $controller 文件名 true/false
     * @param bool $method 方法名 true/false
     * @return void
     */
    public function setChange($controller=true,$method=true){
        $this->controllerChange = $controller;
        $this->methodChange = $method;
    }

    /**
     * 驼峰转匈牙利转换条件 (出现几次大写字母才转换)
     * @param integer $controller 文件名
     * @param integer $method 方法名
     * @return void
     */
    public function setTimes($controller=1,$method=2){
        $this->controllerTimes = $controller;
        $this->methodTimes = $method;
    }

    /**
     * 大驼峰命名法转匈牙利命名法
     * @param string $str 字符串
     * @param integer $times 出现几次大写字母才转换,默认1次
     * @return string
     */
    private function humpToLine($str,$times=1){
        if(preg_match_all('/[A-Z]/',$str) >= $times){
            $str = preg_replace_callback('/([A-Z]{1})/',function($matches){
                return '_'.strtolower($matches[0]);
            },$str);
            if($str[0]=='_'){
                $str = substr_replace($str,'',0,1);
            }
            return $str;
        }
        return $str;
    }

    /**
     * 递归法获取文件夹下文件
     * @param string $path 路径
     * @param array $fileList 结果保存的变量
     * @param bool $all 可选,true全部,false当前路径下,默认true.
     */
    private function getFileList($path, &$fileList = [], $all = true)
    {
        if (!is_dir($path)) {
            $fileList = [];
            return;
        }
        $data = scandir($path);
        foreach ($data as $one) {
            if ($one == '.' or $one == '..') {
                continue;
            }
            $onePath = $path . DIRECTORY_SEPARATOR . $one;
            $isDir = is_dir($onePath);
            $extName = substr($one, -4, 4);
            if ($isDir == false and $extName == '.php') {
                $fileList[] = $onePath;
            } elseif ($isDir == true and $all == true) {
                $this->getFileList($onePath, $fileList, $all);
            }
        }
    }

    /**
     * 获取代码文件中所有可以生成api的注释
     * @param string $data 代码文件内容
     */
    private function catchEvery($data)
    {
        if (!preg_match_all($this->mainRegex, $data, $matches)) {
            return [];
        } else {
            return $matches[1];
        }
    }

    /**
     * 解析每一条可以生成API文档的注释成数组
     * @param string $data 注释文本 catchEvery返回的每个元素
     * @param string $fileName 文件名
     * @return array
     */
    public function parse($data,$fileName = '')
    {
        $fileName = basename($fileName,'.php');
        $return = [];
        preg_match_all('/(public|private|protected)?\s*function\s+(?<funcName>.*?)\(/', $data, $matches);
        $return['funcName'] = !empty($matches['funcName'][0]) ? $matches['funcName'][0] : '';
        preg_match_all('/\/\*\*\s+\*\s+(?<methodName>.*?)\s+\*/s', $data, $matches);
        $return['methodName'] = !empty($matches['methodName'][0]) ? $matches['methodName'][0] : '';
        preg_match_all('/\s+\*\s+\@method\s+(?<requestName>.*)?.*/', $data, $matches);
        $return['requestName'] = !empty($matches['requestName'][0]) ? $matches['requestName'][0] : '';
        preg_match_all('/\s+\*\s+\@url\s+(?<requestUrl>.*)?.*/', $data, $matches);
        $return['requestUrl'] = !empty($matches['requestUrl'][0]) ? $matches['requestUrl'][0] : '';

        preg_match_all('/\s+\*\s+\@desc\s+(?<desc>.*)?.*/', $data, $matches);
        $return['desc'] = !empty($matches['desc'][0]) ? $matches['desc'][0] : '';
       


 
        if($this->controllerChange == true){
            $return['requestUrl'] = str_replace('{controller}',$this->humpToLine($fileName,$this->controllerTimes),$return['requestUrl']);
        }
        if($this->methodChange == true){
            $return['requestUrl'] = str_replace('{action}',$this->humpToLine($return['funcName'],$this->methodTimes),$return['requestUrl']);
        }
        $return['requestUrl'] = str_replace('{url}',$this->url,$return['requestUrl']);

        preg_match_all('/\s+\*\s+@param\s+(.*?)\s+(.*?)\s+(.*?)\s+(.*?)\s+(.*?)\s/', $data, $matches);

        if(empty($matches[1])){
            $return['param'] = [];
        }else{
            for($i=0;$i<count($matches[1]);$i++){
                $type = !empty($matches[1][$i]) ? $matches[1][$i] : '';
                $var = !empty($matches[2][$i]) ? $matches[2][$i] : '';
                $about = !empty($matches[3][$i]) ? $matches[3][$i] : '';
                $is_need = !empty($matches[4][$i]) ? $matches[4][$i] : '';
                $default = !empty($matches[5][$i]) ? $matches[5][$i] : '';
                $return['param'][] = [
                    'type' => $type,
                    'var' => $var,
                    'about' => $about,
                    'is_need' => $is_need,
                    'default' => $default,
                ];
            }
        }




        preg_match_all('/\s+\*\s+@return\s+(.*?)\s+(.*?)\s+(.*?)\s/', $data, $matches);
        $return['return'] = [];
        if(empty($matches[1])){
            $return['return'] = [];
        }else{
            for($i=0;$i<count($matches[1]);$i++){
                $type = !empty($matches[1][$i]) ? $matches[1][$i] : '';
                $var = !empty($matches[2][$i]) ? $matches[2][$i] : '';
                $about = !empty($matches[3][$i]) ? $matches[3][$i] : '';
                if(strpos($about,'*/') !== false){
                    $about = $var;
                    $var = '';
                }


                if($var!='*/' and $var!=''){
                    // echo "<script>console.log('{$fileName}-{$return['funcName']}-{$var}')</script>";
                    $return['return'][] = [
                        'type' => $type,
                        'var' => $var,
                        'about' => $about,
                    ];
                }

            }
        }
        return $return;

    }
}
