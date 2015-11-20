<?php

/**
 * Array tool class
 * @subpackage classes
 */
class ArrayTool
{
	/**
	 * Checks if the given key or index exists in the array
	 * @param string $key Value to check
	 * @param array $array An array with keys to check
	 */
	public static function array_key_exists($key, $array)
	{
		// Optimized version of array_key_exists : isset will be faster when value is not null (most of the cases)
		return isset($array[$key]) || array_key_exists($key, $array);
	}
	
	/**
	 * Checks if the given value exists in the array (case-insensitive)
	 * @param string $value Value to check
	 * @param array $array An array with values to check
	 */
	public static function in_arrayi($value, $array)
	{
		return in_array(strtolower($value), array_map('strtolower', $array));
	}
	public static function array_searchi($value, $array)
	{
		return array_search(strtolower($value), array_map('strtolower', $array));
	}
	
	public static function getRandomValue($array)
	{
		$randomIndex = array_rand($array);
		return $array[$randomIndex];
	}
	
	/**
	 * Adds an auto-incremented ID column to a 2D array
	 * @param array $array An array
	 */
	public static function addIdColumn($array)
	{
		array_walk($array, function(&$a) {
			static $i = 1;
			$a = array('id'=>$i)+$a;
			$i++;
		});
		return $array;
	}
	
	/**
	 * Removes useless spaces in an array's values
	 * (e.g. : array_walk($temp, 'ArrayTool::trimArrayValues'))
	 * @param &$value An array's cell (by reference)
	 */
	public static function trimArrayValues(&$value)
	{
		$value = trim($value);
	}
	
	/**
	 * Replaces NULL in an array's values
	 * (e.g. : array_walk($temp, 'ArrayTool::replaceNullArrayValues'))
	 * @param &$value An array's cell (by reference)
	 * @param $rep A replacement value (default : 0)
	 */
	public static function replaceNullArrayValues(&$value, $rep = 0)
	{
		$value = $value === null ? $rep : $value;
	}
	
	/**
	 * Removes NULL in an array's values
	 * @param $array An array
	 */
	public static function removeNullArrayValues($array)
	{
		$newArray = array();
		foreach($array as $value) {
			if($value !== null)
				$newArray[] = $value;
		}
		return $newArray;
	}
}
?>
