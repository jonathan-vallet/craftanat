<?php

/**
 * Date tool class
 * @subpackage classes
 */
class DateTool
{
	// Date separators
	const DATE_TIME_SEPARATOR = ' ';
	const DATE_SEPARATOR = '-';
	const DATE_SLASHED_SEPARATOR = '/';
	const TIME_SEPARATOR = ':';
	const MICROTIME_SEPARATOR = ' ';

	// Generic date formats
	const FORMAT_MYSQL_DATETIME = 'Y-m-d H:i:s';
	const FORMAT_MYSQL_DATE = 'Y-m-d';
	const FORMAT_TIME = 'H:i:s';
	
	const FORMAT_YEAR = 'Y';
	const FORMAT_MONTH = 'm';
	const FORMAT_DAY = 'd';
	const FORMAT_DAY_OF_WEEK = 'w';
	const FORMAT_HOUR = 'H';
	const FORMAT_MINUTE = 'i';
	const FORMAT_SECOND = 's';
	const FORMAT_DAY_NUMBER = 'N';

	const FORMAT_SLASHED_DATE = 'd/m/Y';

	// Time intervals
	const INTERVAL_MINUTE = 60;
	const INTERVAL_HOUR = 3600;
	const INTERVAL_DAY = 86400;
	const INTERVAL_WEEK = 604800;
	const INTERVAL_MONTH = 2592000;
	const INTERVAL_YEAR = 31536000;

	/**
	 * Gets time string
	 * @param string $format The string format
	 * @param int $timestamp The timestamp
	 * @return string Current time string if $timestamp parameter is not set, else mysql TIMESTAMP corresponding to $timestamp parameter
	 */
	public static function getTimeString($format=DateTool::FORMAT_MYSQL_DATETIME, $timestamp=null)
	{
		return ($timestamp !== NULL) ? gmdate($format, $timestamp) :  gmdate($format);
	}
	
	/**
	 * Gets timestamp (in seconds)
	 * @param int $year Year
	 * @param int $month Month (from 1 to 12)
	 * @param int $day Day (from 1 to 31)
	 * @param int $hour Hour (from 0 to 23)
	 * @param int $minute Hour (from 0 to 59)
	 * @param int $second Hour (from 0 to 59)
	 * @return int Current timestamp if no parameters have been set, else timestamp corresponding to parameters
	 */
	public static function getTimestamp($year=null, $month=null, $day=null, $hour=null, $minute=null, $second=null)
	{
		if ($hour !== NULL || $minute !== NULL || $second !== NULL || $month !== NULL || $day !== NULL || $year !== NULL)
			return gmmktime($hour, $minute, $second, $month, $day, $year);
		else
			return time();
	}

	/**
	 * Converts a time string to a timestamp
	 * @param string $string The time string
	 * @param string $format The string format
	 * @return int The timestamp
	 */
	public static function stringToTimestamp($string, $format=DateTool::FORMAT_MYSQL_DATETIME)
	{
		if (!is_string($string))
			throw new Exception('Unable to convert time string to timestamp : "'.$string.'" is not a string');
		$var = DateTime::createFromFormat($format, $string);
		if ($var instanceof DateTime)
			return $var->getTimestamp();
		LogTool::getInstance()->logWarning("DateTime::createFromFormat returned errors.");
		LogTool::getInstance()->logDebug("debug ".serialize(DateTime::getLastErrors()));
		return false;
	}

	/**
	 * Converts a timestamp to a time string
	 * @param int $timestamp The timestamp (default is NULL for now)
	 * @param string $format The string format
	 * @return string The time string
	 */
	public static function timestampToString($timestamp, $format=DateTool::FORMAT_MYSQL_DATETIME)
	{
		if (!StringTool::isInt($timestamp))
			throw new Exception('Unable to convert timestamp to time string : "'.$timestamp.'" is not an integer');
	
		return DateTool::getTimeString($format, $timestamp);
	}

	/**
	 * Gets the month list
	 * @return array The list of the month
	 */
	public static function getMonthList()
	{
		// TODO: translate it to current locale
		return array(
			1 => 'janvier',
			2 => 'février',
			3 => 'mars',
			4 => 'avril',
			5 => 'mai',
			6 => 'juin',
			7 => 'juillet',
			8 => 'août',
			9 => 'septembre',
			10 => 'octobre',
			11 => 'novembre',
			12 => 'décembre',
		);
	}
}
?>