<?php

/**
 * Generic element class
 */
abstract class Osteo extends Element
{
	const DATABASE_CONNECTION = 'OsteoDatabaseConnection'; 
	
	const FORMAT_ADMIN = 'admin';
	
	/**
	 * Gets the database connection used by current element
	 * @return DatabaseConnection instance
	 */
	public static function getDatabaseConnection()
	{
		$connectionClass = self::DATABASE_CONNECTION;
		return $connectionClass::getInstance();
	}
}
?>