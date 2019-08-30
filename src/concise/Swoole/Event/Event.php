<?php

namespace Concise\Swoole\Event;

trait Event
{
	/**
	 * task任务进入
	 * @param  object $server   
	 * @param  integer $taskId   
	 * @param  integer $workerId 
	 * @param  array $data     
	 * @return mixed      
	 */
	public function onTask ($server, $taskId, $workerId, $data)
	{
	}
	/**
	 * 任务处理完毕返回
	 * @param  object $server 
	 * @param  integer $taskId 
	 * @param  array $data   
	 * @return mixed         
	 */
	public function onFinish ($server, $taskId, $data)
	{
	}
	/**
	 * 连接关闭
	 * @param  object $ws 
	 * @param  integer $fd 
	 * @return mixed    
	 */
	public function onClose ($ws,$fd)
	{
	}

    /**
     * 监听ws连接事件
     * @param $ws
     * @param $request
     */
    public function onOpen($ws, $request) 
    {
    }
    /**
     * 监听ws消息事件
     * @param $ws
     * @param $frame
     */
    public function onMessage($ws, $frame) 
    {
    }
	/**
	 * worker启动
	 * @param  object $server   
	 * @param  integer $workerId 
	 * @return mixed          
	 */
	public function onWorkerStart ($server,$workerId)
	{
	}

	/**
	 * 监听请求
	 * @param  object $request
	 * @param  object $response 
	 * @return mixed         
	 */
	public function onRequest ($request, $response)
	{
	}
}