<?php

namespace Concise\Swoole\Event;

trait Event
{
	/**
	 * 事件列表
	 * @var array
	 */
	protected static $events = [];

	/**
	 * 事件绑定
	 * @param  string $event    
	 * @param  mixed $callback 
	 * @return object           
	 */
	public function on ($event,$callback)
	{
		if (strtolower(substr($event, 0,2)) !== 'on') {
			$event =  'on' . $event;
		}
		static::$events[strtolower($event)] = $callback;
		return $this;
	}

	/**
	 * 通知事件
	 * @param  string $event 
	 * @param  array $params 
	 * @return mixed
	 */
	public function trigger ($event,$params)
	{
		if (strtolower(substr($event, 0,2)) !== 'on') {
			$event =  'on' . $event;
		}
		$event = strtolower($event);
		if (isset(static::$events[$event])) {
			return call_user_func_array(static::$events[$event],$params);
		}
		return $this;
	}
}