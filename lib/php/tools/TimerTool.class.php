<?php
/**
 * PHP Timer used for optimization
 * @subpackage classes
 */
class TimerTool extends SingletonModel
{
	/**
	 * Timer start time in micro seconds
	 * @var int
	 */
	protected $startTime;

	/**
	 * Timer last tick time in micro seconds
	 * @var int
	 */
	protected $lastTickTime;

	/**
	 * Cumulative time for PHP / MySQL
	 */
	protected $phpCumulativeTime = 0;
	protected $mysqlCumulativeTime = 0;

	/**
	 * Unique instance (singleton) retrieval method
	 * @return TimerTool The TimerTool unique instance
	 */
	public static function getInstance($param=null)
	{
		return self::getClassInstance(get_class(), $param);
	}

	/**
	 * Timer constructor
	 */
	public function __construct()
	{
		// init start microtime
		$this->start();
	}

	public static function getMicrotimeFloat()
	{
		list($usec, $sec) = explode(' ', microtime());
		return (float)$usec + (float)$sec;
	}

	/**
	 * Starts the timer
	 */
	public function start($initTime=NULL)
	{
		$this->startTime = ($initTime !== NULL) ? $initTime : TimerTool::getMicrotimeFloat();
		$this->phpCumulativeTime = 0;
		$this->mysqlCumulativeTime = 0;
		$this->lastTickTime = NULL;
		$this->countValue = 0;
	}

	/**
	 * Returns time since timer start or timer tick in microseconds
	 * @param $fromLastTick : returns time since last tick if TRUE, since start if FALSE
	 */
	public function getTimeMicroseconds($fromLastTick=FALSE)
	{
		return $this->getTimeSeconds($fromLastTick) * 1000;
	}

	/**
	 * Returns time since timer start or timer tick in microseconds
	 * @param $fromLastTick : returns time since last tick if TRUE, since start if FALSE
	 */
	public function getTimeSeconds($fromLastTick=FALSE)
	{
		if (($fromLastTick) && $this->lastTickTime)
			return TimerTool::getMicrotimeFloat() - $this->lastTickTime;
		else
			return TimerTool::getMicrotimeFloat() - $this->startTime;
 	}

 	/*
 	 * Write a debug message in log file, containing time elapsed since timer start and / or timer last tick ; keep current microtime in $this->lastTickTime variable
 	 * @param $unit = 's' for seconds, 'ms' for microseconds
 	 * @param $logDebugText = text to write in log file
 	 * @param $roundPrecision = The optional number of decimal digits to round to, defaults to 0
 	 * @param $isMySQLTime: if set to TRUE, will add time since last tick to cumulative mysql time ; if not, will add time to cumulative PHP time
 	 * @color string $color The color of the message in the log
 	 */
 	public function tick($logDebugText='', $unit='ms', $roundPrecision=0, $isMySQLTime=FALSE, $color=LogTool::COLOR_BLUE)
 	{
 		$logInstance = LogTool::getInstance();
 		if ($unit == 's')
 			$getTimeFunction = 'getTimeSeconds';
 		else
 			$getTimeFunction = 'getTimeMicroseconds';

 		// Write debug message
 		$logInstance->logDebug((isset($this->countValue) ? '[Increment = '.$this->countValue.']' : '').
			'Timer: '.round($this->$getTimeFunction(), $roundPrecision).' '.
			$unit.($this->lastTickTime? ' ('.round($this->$getTimeFunction(TRUE), $roundPrecision).' '.$unit.' since last tick)':'').
			' : '.$logDebugText, $color);

 		// Adds to cumulative timers
 		if ($isMySQLTime)
 		{
 			$this->mysqlCumulativeTime += $this->getTimeMicroseconds(TRUE);
 			LogTool::getInstance()->logTrace($color);
 		}
 		else
 			$this->phpCumulativeTime += $this->getTimeMicroseconds(TRUE);

 		// Remember current microTime
 		$this->lastTickTime = TimerTool::getMicrotimeFloat();
 	}

 	/**
 	 * Increment the current counter
 	 */
 	public function incrementCount()
 	{
 		++$this->countValue;
 	}

 	/**
 	 * Get the cumulative PHP Time
 	 * @return int The cumulative PHP timer in microseconds
 	 */
 	public function getPhpCumulativeTime()
 	{
 		return $this->phpCumulativeTime;
 	}

 	/**
 	 * Get the cumulative MySQL Time
 	 * @return int The cumulative MySQL timer in microseconds
 	 */
 	public function getMysqlCumulativeTime()
 	{
 		return $this->mysqlCumulativeTime;
 	}

 	/**
 	 * Get the start Time
 	 * @return int The timer start time in microseconds
 	 */
 	public function getStartTime()
 	{
 		return $this->startTime;
 	}

 	/**
 	 * Get the counter value
 	 * @return int The counter value
 	 */
 	public function getCounter()
 	{
 		return $this->countValue;
 	}
}

?>
