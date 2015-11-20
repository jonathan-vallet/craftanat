<?php

/**
 * Singleton design pattern abstract class
 * Here is an inline example:
 * <code>
 * <?php
 * $myUniqueInstance = SingletonModel::getInstance();
 * $myUniqueInstance->myMethod();
 * ?>
 * </code>
 * @subpackage classes
 */
abstract class SingletonModel
{
	/**
	 * Class instances array
	 * @var array
	 */
	protected static $classInstances = array();
	
	/**
	 * Unique instance retrieval method
	 * @param mixed $param The class constructor param
	 * @return object The unique instance
	 */
	//abstract public static function getInstance($param=null);
	public static function getInstance($param=null)
	{
		throw new Exception('getInstance method has to be implemented by SingletonModel\'s child classes!');
	}
	
	/**
	 * Unique class instance retrieval method
	 * @param string $class The class name
	 * @param mixed $param The class constructor param
	 * @return object The class unique instance
	*/
	protected static function getClassInstance($class, $param)
	{
		// Constructs class instance if necessary
		if (!array_key_exists($class, SingletonModel::$classInstances))
			SingletonModel::$classInstances[$class] = new $class($param);

		// Else returns existing class instance
		return SingletonModel::$classInstances[$class];
	}
}
?>
