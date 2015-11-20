<?php

/**
 * Comparison tool class
 * @subpackage classes
 */
class ComparisonTool
{
	const LESS_THAN = 'LESS_THAN';
	const LESS_THAN_OR_EQUAL = 'LESS_THAN_OR_EQUAL';
	const EQUAL = 'EQUAL';
	const DIFFERENT = 'DIFFERENT';
	const MORE_THAN = 'MORE_THAN';
	const MORE_THAN_OR_EQUAL = 'MORE_THAN_OR_EQUAL';
	
	/**
	 * Checks if a value is equal, lesser, greater... than another compared withan operator
	 * @param string|number $value1 the value to compare
	 * @param string the comparison operator
	 * @param string|number $value2 the value to compare to
	 * @return boolean if $value1 comparison with $value2 is valid or not
	 */
	public static function compare($value1, $operator, $value2)
	{
		switch($operator)
		{
			case ComparisonTool::LESS_THAN:
				return $value1 < $value2;
				break;
			case ComparisonTool::LESS_THAN_OR_EQUAL:
				return $value1 <= $value2;
				break;
			case ComparisonTool::EQUAL:
				return $value1 == $value2;
				break;
			case ComparisonTool::DIFFERENT:
				return $value1 != $value2;
				break;
			case ComparisonTool::MORE_THAN:
				return $value1 > $value2;
				break;
			case ComparisonTool::MORE_THAN_OR_EQUAL:
				return $value1 >= $value2;
				break;
			default:
				throw new Exception('Invalid operator ' . $operator);
				break;
		}
	}
}
?>
