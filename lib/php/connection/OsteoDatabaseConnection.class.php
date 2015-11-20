<?php
/**
 * Main database connection class
 * @subpackage classes
 */
class OsteoDatabaseConnection extends MysqlDatabaseConnectionModel
{
	/**
	 * Unique instance (singleton) retrieval method
	 * @return CommonDatabaseConnection The CommonDatabaseConnection unique instance
	 */
	public static function getInstance($param=null)
	{
		return self::getClassInstance(get_class(), $param);
	}

	/**
	 * Get the host for the db connection
	 */
	public function getDBConnectionHost()
	{
		return constant('SGBD_HOST');
	}

	/**
	 * Get the username for the db connection
	 */
	public function getDBConnectionUserName()
	{
		return constant('SGBD_USER');
	}

	/**
	 * Get the password for the db connection
	 */
	public function getDBConnectionPassword()
	{
		return constant('SGBD_PASSWORD');
	}

	/**
	 * Get the database name for the db connection
	 */
	public function getDBConnectionDBName()
	{
		return constant('SGBD_DB');
	}
}
?>
