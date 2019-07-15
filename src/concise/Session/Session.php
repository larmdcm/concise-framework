<?php

namespace Concise\Session;

use Concise\Foundation\Config;
use Concise\Foundation\Arr;
use Concise\Container\Container;

class Session
{
	protected $init = false;

	public function __construct ($config = [])
	{
		if (!$this->init) {
			$this->init();
		}
	}

	public function init ($config = [])
	{
		// 读取配置
		if (empty($config)) {
		 	$config = Config::scope('session')->get('',[]);
		}

		$isDoStart = false;
		     // 启动session
        if (!empty($config['auto_start']) && PHP_SESSION_ACTIVE != session_status()) {
            ini_set('session.auto_start', 0);
            $isDoStart = true;
        }

        if (isset($config['var_session_id']) && isset($_REQUEST[$config['var_session_id']])) {
            session_id($_REQUEST[$config['var_session_id']]);
        } elseif (isset($config['id']) && !empty($config['id'])) {
            session_id($config['id']);
        }

        if (isset($config['name'])) {
            session_name($config['name']);
        }

        if (isset($config['path'])) {
            session_save_path($config['path']);
        }

        if (isset($config['domain'])) {
            ini_set('session.cookie_domain', $config['domain']);
        }

        if (isset($config['expire'])) {
            ini_set('session.gc_maxlifetime', $config['expire']);
            ini_set('session.cookie_lifetime', $config['expire']);
        }

        if (isset($config['secure'])) {
            ini_set('session.cookie_secure', $config['secure']);
        }

        if (isset($config['httponly'])) {
            ini_set('session.cookie_httponly', $config['httponly']);
        }

        if (isset($config['use_cookies'])) {
            ini_set('session.use_cookies', $config['use_cookies'] ? 1 : 0);
        }

        if (isset($config['cache_limiter'])) {
            session_cache_limiter($config['cache_limiter']);
        }

        if (isset($config['cache_expire'])) {
            session_cache_expire($config['cache_expire']);
        }

        if (isset($config['type']) && !empty($config['type'])) {
        	
        }

        if ($isDoStart) {
        	session_start();
        	$this->init = true;
        } else {
        	$this->init = false;
        }

        return $this->init;
	}

	/**
	 * 获取
	 * @param  string $name    
	 * @param  string $default 
	 * @return string         
	 */
	public function get ($name = '',$default = '')
	{
		return Arr::get($_SESSION,$name,$default);
	}
	/**
	 * 设置
	 * @param string $name  
	 * @param mixed $value
	 * @return object 
	 */
	public function set ($name,$value = '')
	{
		Arr::set($_SESSION,$name,$value);
		return $this;
	}
	/**
	 * 删除
	 * @param  string $name 
	 * @return object       
	 */
	public function delete ($name)
	{
		is_null($name) ? session_destroy() && $_SESSION = [] : Arr::delete($_SESSION,$name);
		return $this;
	}

	public function isInit ()
	{
		return $this->init;
	}
}