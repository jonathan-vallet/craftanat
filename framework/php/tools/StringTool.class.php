<?php

/**
 * String tool class
 * @subpackage classes
 */
class StringTool
{
	/**
	 * Checks if a string ends with string passed in parameter
	 * @param string $string The original string
	 * @param string $end The string to match the end of the origine
	 * @return boolean true if $string ends with $end, else false
	 */
	public static function endsWith($string, $end)
	{
		return StringTool::substr($string, - StringTool::strlen($end)) === $end;
	}

	/**
	 * Checks if a string starts with string passed in parameter
	 * @param string $string The original string
	 * @param string $start The start string
	 * @return boolean true if $string starts with $start, else false
	 */
	public static function startsWith($string, $start)
	{
		return StringTool::substr($string, 0, StringTool::strlen($start)) === $start;
	}
	
	/**
	 * Returns the part of the $string specified by the $start and $length parameters
	 * @param string $string The input string
	 * @param string $start The start string index
	 * @param int $length The length of the substring
	 * @return string The part of $string between $start and $length parameters
	 */
	public static function substr($string, $start, $length=NULL)
	{
		return ($length === NULL) ? mb_substr($string, $start) : mb_substr($string, $start, $length);
	}

	/**
	 * Get string length
	 * @param string $string The string to get length
	 * @return int The length of the string
	 */
	public static function strlen($string)
	{
		return mb_strlen($string);
	}

	/**
	 * Finds position of the first occurrence of a string in a string
	 * @param string $string The string being checked
	 * @param string $needle The position counted from the beginning of $str
	 * @param int $offset The search offset (default 0)
	 * @return int The position of the first occurrence of $needle in the $string, or false
	 */
	public static function strpos($string, $needle, $offset=0)
	{
		return mb_strpos($string, $needle, $offset);
	}
	
	/**
	 * Checks if a string contains another one
	 * @param string $string The original string
	 * @param string $search The searched string
	 * @return boolean If the $string contains at least one occurence of $search or not
	 */
	public static function contains($string, $search)
	{
		return StringTool::strpos($string, $search) !== false;
	}
	
	/**
	 * Checks if a string contains an integer value
	 * Example:
	 * <code>
	 * StringTool::isInt('42') -> true
	 * StringTool::isInt('042') -> false
	 * StringTool::isInt('42.52') -> false
	 * StringTool::isInt('toto') -> false
	 * </code>
	 * @param string $string The original string
	 * @return bool Returns true if $string contains an integer value, else returns false
	 */
	public static function isInt($string, $checkIsNumeric=true)
	{
		return (int)$string == (float)$string && (string)(int)$string === (string)$string && (!$checkIsNumeric || is_numeric($string));
	}

	/**
	 * Checks if a string contains a float value
	 * @param string $string The original string
	 * @param boolean $checkIsNotInt Specifies if check has to be strict (check that value is not an integer)
	 * @return boolean Returns true if $string contains a float value, else returns false
	 */
	public static function isFloat($string, $checkIsNotInt=true)
	{
		//$floatMatchRegex = ($checkIsNotInt) ? '/^-?([1-9]\d*|0)(\.\d*)?$/' : '/^-?([1-9]\d*|0)\.\d*$/';
		$floatMatchRegex = '/^(-){0,1}([0-9]+)(,[0-9][0-9][0-9])*([.][0-9]){0,1}([0-9]*)$/';
		return is_numeric($string) && preg_match($floatMatchRegex, $string) && (!$checkIsNotInt || !StringTool::isInt($string, false));
	}
	/**
	 * Returns integer value contained in $string
	 * @param string $string The original string
	 * @return int The integer value
	 */
	public static function toInt($string)
	{
		if (!StringTool::isInt($string))
			throw new Exception('String to convert is not an integer value: ' . $string);

		return (int)$string;
	}
	/**
	 * Returns float value contained in $string
	 * @param string $string The original string
	 * @return float The float value
	 */
	public static function toFloat($string)
	{
		if (!StringTool::isFloat($string))
			throw new Exception('String to convert is not a float value: ' . $string);

		return (float)$string; // faster
	}

	/**
	 * Returns string without N last chars
	 * @param string $string The original string
	 * @param string $charsNumber The number of chars to trunc
	 * @return string The modified string
	 */
	public static function truncateLastChars($string, $charsNumber)
	{
		return StringTool::substr($string, 0, StringTool::strlen($string) - $charsNumber);
	}

	/**
	 * Returns string without N first chars
	 * @param string $string The original string
	 * @param string $charsNumber The number of chars to trunc
	 * @return string The modified string
	 */
	public static function truncateFirstChars($string, $charsNumber)
	{
		return StringTool::substr($string, $charsNumber);
	}
	
	/**
	 * Split string into array by regular expression
	 * @param string $pattern regular expression to split the string
	 * @param string $string The string to split
	 * @param int $limit The max substrings returned in array
	 * @return array an array with substrings list
	 */
	public static function split($pattern, $string, $limit=-1)
	{
		return mb_split($pattern, $string, $limit);
	}
	
	/**
	 * Secures a string from database injections
	 * @param string $string The original string
	 * @return string The secured string
	 */
	public static function secureFromDatabaseInjection($string)
	{
		return addslashes($string);
	}
	
	/**
	 * Gets string in lower case
	 * @param string $string the string to lower
	 * @return string the lowered string
	 */
	public static function lower($string)
	{
		return mb_strtolower($string);
	}
	
	/**
	 * Gets string in upper case
	 * @param string $string the string to upper
	 * @return string the uppered string
	 * 	 */
	public static function upper($string)
	{
		return mb_strtoupper($string);
	}

	/**
	 * Returns string with first char in lower case
	 * @param string $string The original string
	 * @return string The modified string
	 */
	public static function lowerFirstChar($string)
	{
		$firstChar = StringTool::substr($string, 0, 1);
		$otherChars = StringTool::substr($string, 1);
		return StringTool::lower($firstChar) . $otherChars;
	}

	/**
	 * Returns string with first char in upper case
	 * @param string $string The original string
	 * @return string The modified string
	 */
	public static function upperFirstChar($string)
	{
		return ucfirst($string);
	}

	/**
	 * Returns string without chars unfit for variable naming
	 * @param string $string The original string
	 * @return string The modified string
	 */
	public static function cleanVarName($string)
	{
		$a = 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿŔŕ';
	    $b = 'aaaaaaaceeeeiiiidnoooooouuuuybsaaaaaaaceeeeiiiidnoooooouuuyybyRr';
	    $string = strtr($string, utf8_decode($a), $b);
		return str_replace(array('.',',','-',':','?','!','/','(',')',' ',"'",'"'), '_' , $string);
	}

	/**
	 * Replaces line breaks with <br /> tags
	 * @param string $string The original string
	 * @return string The modified string
	 */
	public static function nl2br_rep($string)
	{
		return str_replace(array("\r\n", "\r", "\n"), "<br />", $string);
	}

	/**
	 * Replaces <br /> tags with line breaks
	 * @param string $string The original string
	 * @return string The modified string
	 */
	public static function br2nl_rep($string)
	{
		return preg_replace('/\<br(\s*)?\/?\>/i', "\n", $string);
	}

	/**
	 * Generates a random string (for unique filenames)
	 * @param int $length The string length
	 * @return string The created string
	 */
	public static function generateRandomString($length = 10)
	{
		return StringTool::substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
	}
}
?>
