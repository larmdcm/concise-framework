<?php

namespace Concise\Session;

use Concise\Foundation\Config;
use Concise\Foundation\Arr;
use Concise\Container\Container;
USE Concise\Exception\ClassNotFoundException;

class Session
{
	/**
	 * 是否初始化
	 * @var null
	 */
	protected $init = null;

	/**
	 * 构造函数
	 * @return void
	 */
	public function __construct ()
	{
		if (is_null($this->init)) {
			$this->init();
		}
	}

	/**
	 * 初始化
	 * @param  array $config 配置项
	 * @return boolean
	 */
	public function init ($config = [])
	{
		// 读取配置
	 	$config = array_merge(Config::scope('session')->get('',[]),$config);

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

        if (isset($config['drive']) && !empty($config['drive'])) {
	    	  // 读取session驱动
            $class = false !== strpos($config['drive'], '\\') ? $config['drive'] : '\\Concise\\Session\\Drive\\' . ucwords($config['drive']);
            // 检查驱动类
            if (!class_exists($class) || !session_set_save_handler(new $class($config))) {
                throw new ClassNotFoundException('error session handler:' . $class, $class);
            }
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
     * session自动启动或者初始化
     * @return void
     */
    public function boot()
    {
        if (is_null($this->init)) {
            $this->init();
        } elseif (false === $this->init) {
            if (PHP_SESSION_ACTIVE != session_status()) {
                session_start();
            }
            $this->init = true;
        }
    }
	/**
	 * 获取
	 * @param  string $name    
	 * @param  string $default 
	 * @return boolean         
	 */
	public function get ($name = '',$default = '')
	{
        empty($this->init) && $this->boot();
		return Arr::get($_SESSION,$name,$default);
	}
	/**
	 * 设置
	 * @param string $name  
	 * @param mixed $value
	 * @return boolean 
	 */
	public function set ($name,$value = '')
	{
		empty($this->init) && $this->boot();
		return Arr::set($_SESSION,$name,$value);
	}

	/**
	 * 删除
	 * @param  string $name 
	 * @return bool       
	 */
	public function delete ($name)
	{
		empty($this->init) && $this->boot();
		return Arr::delete($_SESSION,$name);
	}

	/**
	 * 获取session是否存在
	 * @param  string  $name 
	 * @return boolean 
	 */
	public function has ($name)
	{
		empty($this->init) && $this->boot();
		return Arr::has($_SESSION,$name);
	}

	/**
	 * 清除session
	 * @return void
	 */
	public function clear ($scope = '')
	{
		empty($this->init) && $this->boot();
		if (empty($scope)) {
			$_SESSION = [];
		} else {
			$_SESSION[$scope] = [];
		}
	}

	/**
	 * 销毁session
	 * @return void
	 */
	public function destroy ()
	{
		if (!empty($_SESSION)) {
			$_SESSION = [];
		}
		session_unset();
		session_destroy();
		$this->init = null;
	}

	/**
	 * 启动session
	 * @return void
	 */
	public function start ()
	{
		if (!$this->init) {
			session_start();
			$this->init = true;
		}
	}

   /**
     * 重新生成session_id
     * @param  bool $delete 是否删除关联会话文件
     * @return void
     */
    public function regenerate($delete = false)
    {
        session_regenerate_id($delete);
    }

    /**
     * 暂停session
     * @return void
     */
    public function pause()
    {
        // 暂停session
        session_write_close();
        $this->init = false;
    }

	/**
	 * 是否初始化
	 * @return boolean
	 */
	public function isInit ()
	{
		return $this->init;
	}
}