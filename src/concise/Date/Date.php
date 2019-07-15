<?php

namespace Concise\Date;

class Date
{
	protected $dateTimeZone;

	// 构造函数初始化
	public function __construct ($dateTimeZone = 'PRC')
	{
		$this->setDateTimeZone($dateTimeZone);
	}

	/**
	 * 设置默认时区
	 * @param  string $dateTimeZone 
	 * @return object
	 */
	public function setDateTimeZone ($dateTimeZone = 'PRC')
	{
		$dateTimeZone 		= empty($dateTimeZone) ? 'PRC' : $dateTimeZone;
		$this->dateTimeZone = $dateTimeZone;
		date_default_timezone_set($this->dateTimeZone);
		return $this;
	}

	/**
	 * 获取设置默认时区
	 * @return string
	 */
	public function getDateTimeZone ()
	{
		return !empty($this->dateTimeZone) ? $this->dateTimeZone : date_default_timezone_get();
	}
	/**
	 * 获取明天时间戳
	 * @param  integer $time 
	 * @return integer 
	 */
	public function getTomTime ($time = 0)
	{
		$tom = strtotime(date('Y-m-d 23:59:59',time())) + 1;
		return $time == 0 ? $tom : $tom + 3600 * $time;
	}
	/**
	 * 秒数转换时分秒
	 * @param  integer $times 
	 * @return string     
	 */
	public function secToTime ($times = 0)
	{
		$result = '00:00:00';
		if ($times > 0) {
	        $hour 	= floor($times/3600);  
	        $minute = floor(($times-3600 * $hour)/60);  
	        $second = floor((($times-3600 * $hour) - 60 * $minute) % 60);  
	        $result = $hour.':'.$minute.':'.$second;  
		} 
        return $result;  
	}

	/**
	 * 获取本月第一天和最后一天
	 * @return array
	 */
	public function getMonth ()
	{
		$beginDate    = date('Y-m-01 H:i:s', strtotime(date("Y-m-d")));
		$beginDateEnd = date('Y-m-d', strtotime("$beginDate +1 month -1 day"));
		return [strtotime($beginDate),strtotime($beginDateEnd) + 24 * 3600 - 1];
	}
	/**
	 * 获取上个月第一天和最后一天
	 * @return array
	 */
	public function gerPrevMonth ()
	{
		$month    = date('Y-m-01 H:i:s', strtotime('-1 month'));
		$monthEnd = date('Y-m-t', strtotime('-1 month'));
		return [strtotime($month),strtotime($monthEnd) + 24 * 3600 - 1];
	}
	/**
	 * 获取本周第一天和最后一天时间
	 * @return array
	 */
	public function getWeek ()
	{
		$first = self::getItemWeek(1)[0];
		$last  = self::getItemWeek(7)[1];
		return [$first,$last];
	}
	/**
	 * 获取指定月份
	 * @param  integer $month 
	 * @return array
	 */
	public function getItemMonth ($month = 1)
	{
		$beginDate    = date('Y-'. $month .'-01 H:i:s', strtotime(date("Y-m-d")));
		$beginDateEnd = date('Y-m-d', strtotime("$beginDate +1 month -1 day"));
		return [strtotime($beginDate),strtotime($beginDateEnd) + 24 * 3600 - 1];
	}
	/**
	 * 获取本月指定哪天的24小时
	 * @param  integer $day 
	 * @return array 
	 */
	public function getItemDayTime ($day)
	{
		$year  = date("Y");
	    $month = date("m");
	    $day   = date($day);
	    $start = mktime(0,0,0,$month,$day,$year);//当天开始时间戳
	    $end   = mktime(23,59,59,$month,$day,$year);//当天结束时间戳
	    return [$start,$end];
	}
	/**
	 * 获取指定星期的24小时
	 * @param  integer $weekDay 
	 * @return array
	 */
	public function getItemWeek ($weekDay = 1)
	{ 
		$timestr    = time();
	    $nowDay     = date('w',$timestr);
	    if ($nowDay == 0)
	    {
	    	$nowDay = 7;
	    }
	    //获取一周的第一天
	    $sundayStr  = $timestr - ($nowDay * 60 * 60 * 24) + 24 * 3600;
	    $sundayStr  = date('Y-m-d',$sundayStr);
	    $sundayStr  = strtotime($sundayStr);
	    $week = $sundayStr + (($weekDay - 1) * 24 * 3600);
	    return [$week,$week + 24 * 3600 - 1];
	}

	/**
	 * 获取今天的开始和结束时间
	 * @return array
	 */
	public function getToDayTime ()
	{
		$currentTime = date('Y-m-d');
		$startTime = strtotime($currentTime);
		$endTime   = strtotime($currentTime) + 24 * 3600 - 1;
		return [$startTime,$endTime];
	}
	/**
	 * 格式化秒
	 * @param integer $time
	 * @return string
	 */
	public function secToTimeTo ($time)
	{
		 $output = '';
		  foreach (array(86400 => ':', 3600 => ':', 60 => ':', 1 => '') as $key => $value) {
		    if ($time >= $key)  $output .= (strlen(floor($time/$key)) >= 2 ? floor($time/$key) : '0' . floor($time/$key)) . $value;
		  	 $time %= $key;
		  }
		  return $output;
	}
}