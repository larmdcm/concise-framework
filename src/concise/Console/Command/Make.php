<?php

namespace Concise\Console\Command;

use Concise\Console\Console;
use Concise\Exception\ConsoleException;
use Concise\Foundation\Config;
use Concise\Container\Container;

class Make extends Console
{
	const EXT = 'tpl';

	public function handle ()
	{
		$method = array_keys($this->args)[0];

		if (method_exists($this,$method)) {
			return call_user_func_array([$this,$method],[$this->args[$method]]);
		}
		throw new ConsoleException($method . " is not exists!","not command");
	}

	/**
	 * 创建控制器模板
	 * @param  string $putPath 
	 * @return mixed
	 */
	public function controller ($putPath)
	{
		$path = $this->parsePath($putPath);
		return $this->createTemplate(sprintf("Controller/%s%sController.php",empty($path['path']) ? "" : $path['path'] . "/",$path['name']),'Controller',$path);
	}

	/**
	 * 创建模型模板
	 * @param  string $putPath 
	 * @return mixed
	 */
	public function model ($putPath)
	{
		$path = $this->parsePath($putPath);
		return $this->createTemplate(sprintf("Model/%s%s.php",empty($path['path']) ? "" : $path['path'] . "/",$path['name']),'Model',$path);
	}

	/**
	 * 创建请求模板
	 * @param  string $putPath 
	 * @return mixed
	 */
	public function request ($putPath)
	{
		$path = $this->parsePath($putPath);
		return $this->createTemplate(sprintf("Request/%s%sRequest.php",empty($path['path']) ? "" : $path['path'] . "/",$path['name']),'Request',$path);
	}

	/**
	 * 创建中间件模板
	 * @param  string $putPath 
	 * @return mixed
	 */
	public function middleware ($putPath)
	{
		$path = $this->parsePath($putPath);
		return $this->createTemplate(sprintf("Middleware/%s%s.php",empty($path['path']) ? "" : $path['path'] . "/",$path['name']),'Middleware',$path);
	}

	/**
	 * 创建命令行模板
	 * @param  string $putPath 
	 * @return mixed
	 */
	public function console ($putPath)
	{
		$path = $this->parsePath($putPath);
		return $this->createTemplate(sprintf("Command/%s%s.php",empty($path['path']) ? "" : $path['path'] . "/",$path['name']),'Command',$path);
	}

	/**
	 * 创建模板
	 * @param  string $type 
	 * @param  string $path 
	 * @param  array $options 
	 * @return mixed       
	 */
	protected function createTemplate ($path,$type = '',$options = [])
	{
		$namespace   = lcfirst(Config::get('app_namespace','App'));
		$appPath     = Container::get('env')->get('app_path');

		$putPath   	 = $appPath . '/' . ltrim($path,'/');

		$tplPath 	 =  __DIR__ . '/Make/Tpl/'. ucfirst($type) . '.' . self::EXT;


		if (!is_file($tplPath)) {
			throw new ConsoleException("template is not exists:" . $tplPath,$this->command);
		}

		is_dir(dirname($putPath)) || mkdir(dirname($putPath));
		$data = ['namespace' => sprintf("%s\%s%s;\r\n",ucfirst($namespace),$type,empty($options['path']) ? '' : '\\' . $options['path']),'className' => $options['name']];
	
		$data['namespace'] = str_replace("/", "\\", $data['namespace']);
		ob_start();
		extract($data);
		include $tplPath;
		$content = ob_get_contents();
		$content = "<?php\r\n" . $content;
		ob_clean();
		if (is_file($putPath)) {
			throw new ConsoleException(sprintf("%s:%s is exists",$options['name'],$type),$this->command);
		}
		if (!file_put_contents($putPath, $content)) {
			throw new ConsoleException(sprintf("%s:%s is create fail",$options['name'],$type),$this->command);
		}
		return $this->out(sprintf("%s:%s template create success",$options['name'],$type));
	}

	/**
	 * 路径解析
	 * @param  string $path 
	 * @return array  
	 */
	protected function parsePath ($path)
	{
		$paths 	  = explode('/',$path);
		$name     = ucfirst(array_pop($paths));
		return ['name' => $name,'path' => implode('/',$paths)];
	}
}