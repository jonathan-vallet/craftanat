<?php

/**
 * Database connection model
 * @subpackage classes
 */
abstract class DatabaseConnectionModel extends SingletonModel
{
	/**
	 * The database host name
	 * @var string
	 */
	protected $host;

	/**
	 * The database username
	 * @var string
	 */
	protected $username;

	/**
	 * The database password
	 * @var string
	 */
	protected $password;

	/**
	 * The database name
	 * @var string
	 */
	protected $dbName;

	/**
	 * Database connection links
	 * @var resource
	 */
	private $dbLink;

	/**
	 * The current transaction status
	 * @var boolean TRUE if connection needs transactional mode, FALSE else (default)
	 */
	private static $isTransactionEnabled = FALSE;

	/**
	 * The instantiated connection list
	 * @var object array
	 */
	private static $databaseConnectionList = array();

	/**
	 * Connects to database
	 */
	abstract protected function connect();

	/**
	 * Disconnects from database
	 */
	abstract protected function disconnect();

	/**
	 * Generic request execution method
	 * @param string $request The request string
	 * @return resource The request results or an exception if any error occures
	 */
	abstract protected function & request($request);

	/**
	 * Select type request execution method
	 * @param string $request The request string
	 * @param int $resultType The type of array that is to be fetched
	 * @param boolean $returnFieldNames Specifies if resultset field name array has to be returned
	 * @return array The found rows array
	 */
	abstract public function & selectRequest($request, $resultType=null, $returnFieldNames=false);

	/**
	 * Insert type request execution method
	 * @param string $request The request string
	 * @return int The inserted element id
	 */
	abstract public function insertRequest($request);

	/**
	 * Update type request execution method
	 * @param string $request The request string
	 * @return int The number of affected rows
	 */
	abstract public function updateRequest($request);

	/**
	 * Delete type request execution method
	 * @param string $request The request string
	 * @return int The number of affected rows
	 */
	abstract public function deleteRequest($request);

	/**
	 * Gets the last affected row count for current dbLink
	 * @return int The number of affected rows
	 */
	abstract public function getAffectedRowCount();

	 /**
	 * Starts a transaction (to be used BEFORE commit OR rollback function)
	 */
	abstract public function startTransaction();

	/**
	 * Commits current transactions
	 */
	abstract public function commit();

	/**
	 * Cancels current transactions
	 */
	abstract public function rollback();

	/**
	 * Get the host for the db connection
	 */
	abstract public function getDBConnectionHost();

	/**
	 * Get the username for the db connection
	 */
	abstract public function getDBConnectionUserName();

	/**
	 * Get the password for the db connection
	 */
	abstract public function getDBConnectionPassword();

	/**
	 * Get the database name for the db connection
	 */
	abstract public function getDBConnectionDBName();

	/**
	 * Gets current database name
	 * @return string The database name
	 */
	public function getDatabaseName()
	{
		return $this->dbName;
	}
	/**
	 * Class constructor. Starts a transaction if transactional mode activated.
	 */
	public function __construct()
	{
		// adds current connection to instantiated connection list
		array_push(DatabaseConnectionModel::$databaseConnectionList, $this);

		// starts a transaction if transactional statement required
		if (DatabaseConnectionModel::isTransactionEnabled())
			$this->startTransaction();
	}

	/**
	 * Enables transactional mode for all DB connections. Starts a transaction for instantiated connections.
	 */
	public static function setTransaction()
	{
		//0If  transaction is already enabled, don't need to do anything
		if (DatabaseConnectionModel::isTransactionEnabled())
			return;

		DatabaseConnectionModel::$isTransactionEnabled = TRUE;
		// browses all instantiated connections
		foreach (DatabaseConnectionModel::$databaseConnectionList as $databaseConnection)
			$databaseConnection->startTransaction();
	}

	/**
	 * Disables transactional mode for all DB connections. Called after commit or rollback.
	 */
	private static function unsetTransaction()
	{
		DatabaseConnectionModel::$isTransactionEnabled = FALSE;
	}

	/**
	 * Returns TRUE if a transactional mode is enabled, FALSE else
	 * @return boolean TRUE if transactional mode is enabled, FALSE else
	 */
	public static function isTransactionEnabled()
	{
		return DatabaseConnectionModel::$isTransactionEnabled;
	}

	/**
	 * Commits transaction on all instantiated connections & disables transactional statement
	 */
	public static function commitTransaction()
	{
		// browses all instantiated connections
		foreach (DatabaseConnectionModel::$databaseConnectionList as $databaseConnection)
			$databaseConnection->commit();

		// disables transactional mode
		DatabaseConnectionModel::unsetTransaction();
	}

	/**
	 * Rollbacks transaction on all instantiated connections & disables transactional statement
	 */
	public static function rollbackTransaction()
	{
		// browses all instantiated connections
		foreach (DatabaseConnectionModel::$databaseConnectionList as $databaseConnection)
			$databaseConnection->rollback();

		// disables transactional mode
		DatabaseConnectionModel::unsetTransaction();
	}
}
?>