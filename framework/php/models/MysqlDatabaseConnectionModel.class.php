<?php
/**
 * Mysql database connection class
 * @subpackage classes
 */
abstract class MysqlDatabaseConnectionModel extends DatabaseConnectionModel
{
	const ARRAY_TYPE_ASSOC = MYSQL_ASSOC;
	const ARRAY_TYPE_NUM = MYSQL_NUM;
	const ARRAY_TYPE_BOTH = MYSQL_BOTH;

	/**
	 * The global configuration name
	 * @var string
	 */
	protected $globalConfName = NULL;

	/**
	 * Class constructor
	 */
	public function __construct()
	{
		// Builds current database connection's configuration (host, user, password and db name) from globals
		$this->host = $this->getDBConnectionHost();
		$this->username = $this->getDBConnectionUserName();
		$this->password = $this->getDBConnectionPassword();
		$this->dbName = $this->getDBConnectionDBName();

		// Database connection
		$this->connect();

		parent::__construct();
	}

	/**
	 * Class destructor
	 */
	public function __destruct()
	{
		// Database disconnection
		if ($this->isConnected())
			$this->disconnect();
	}

	/**
	 * Checks if current mysql connection is alive
	 * @return bool Returns TRUE if connection is alive, else returns FALSE
	 */
	public function isConnected()
	{
		return isset($this->dbLink) && $this->dbLink !== NULL && mysql_stat($this->dbLink) !== NULL;
	}

	/**
	 * Connects to database
	 */
	protected function connect($transactionCharacterSet='UTF8')
	{
		LogTool::getInstance()->logDebug('Connecting ' . $this->username . ' to ' . get_class() . ' ' . $this->dbName . '@' . $this->host . ' database.');

		// Database host connection
		$this->dbLink = @mysql_connect($this->host, $this->username, $this->password);
		if (!$this->dbLink)
			throw new DatabaseConnectionException(get_class() . ' : Unable to connect ' . $this->username . ' to ' . $this->dbName . '@' . $this->host . ' database');

		// Database instance selection
		if (!mysql_select_db($this->dbName, $this->dbLink))
			throw new DatabaseConnectionException(get_class() . ' : Unable to select ' . $this->dbName . '@' . $this->host . ' database instance');

		// Database Transaction Character Set definition
		$this->request('SET NAMES ' . $transactionCharacterSet);
	}

	/**
	 * Disconnects from database
	 */
	protected function disconnect()
	{
		// Database disconnection
		mysql_close($this->dbLink);
	}

	/**
	 * Escapes special characters in a string for use in a SQL statement
	 * @param string $string The string to escape
	 * @return string The escaped string
	 */
	public function realEscapeString($string)
	{
		// Connect to database (if needed)
		if (!$this->isConnected()) 
			$this->connect();

		return mysql_real_escape_string($string, $this->dbLink);
	}

	/**
	 * Generic request execution method
	 * @param string $request The request string
	 * @return resource The request results or an exception if any error occures
	 */
	protected function & request($request)
	{
		$timer = TimerTool::getInstance();
		$timer->tick('Executing request ('.$this->dbName.') : '.$request, 'ms', 0, FALSE);
		$timer->incrementCount();

		// Request execution
		$result = mysql_unbuffered_query($request, $this->dbLink);
		if (!$result)
		{
			$mysqlErrNo = mysql_errno($this->dbLink);
			$mysqlErrMsg = mysql_error($this->dbLink);

			// Mysql connection lost : reconnect and retry query
			if (!$this->isConnected())
			{
				$logInstance = LogTool::getInstance();
				$logInstance->logWarning('Connection lost (Error #' . $mysqlErrNo . ' : ' . $mysqlErrMsg . ') during request execution, will reconnect and retry it : ' . $request);
				$this->connect();
				$logInstance->logWarning('Retrying request (' . $this->dbName . ') : ' . $request);
				$result = mysql_unbuffered_query($request, $this->dbLink);
				if (!$result)
					throw DatabaseException::getSpecificException('Error while executing query (retry): "' . $request . '" (Error #' . $mysqlErrNo . ' : ' . $mysqlErrMsg . ')', $mysqlErrNo);
			}
			else
				throw DatabaseException::getSpecificException('Error while executing following query : "'.$request.'" (Error #'.$mysqlErrNo.' : '.$mysqlErrMsg.')', $mysqlErrNo);
		}
		$timer->tick('Finished executing request', 'ms', 0, TRUE);
		return $result;
	}

	/**
	 * Select type request execution method
	 * @param string $request The request string
	 * @param int $resultType The type of array that is to be fetched
	 * @param boolean $returnFieldNames Specifies if resultset field name array has to be returned
	 * @param boolean $fetchResult If set to TRUE, result will be fetched, else MyQSL resource object will be returned
	 * @return array The found rows array + resultset field name array if needed
	 */
	public function & selectRequest($request, $resultType=MYSQL_ASSOC, $returnFieldNames=FALSE, $fetchResult=TRUE)
	{
		// Generic request execution
		$result = $this->request($request);

		// Builds fields name array (if needed)
		if ($returnFieldNames === TRUE)
		{
			$nbFields = mysql_num_fields($result);
			$fieldNames = array();
			for ($i = 0; $i < $nbFields; $i++)
				$fieldNames[] = mysql_field_name($result, $i);
		}

		// Browses result rows and adds it to the result array
		if ($fetchResult === TRUE)
		{
			$returnValue = array();
			while ($row = mysql_fetch_array($result, $resultType))
				$returnValue[] = $row;

			// Free results ressource
			mysql_free_result($result);
		}
		else
			$returnValue = $result;

		if ($returnFieldNames === TRUE)
		{
			$finalResult = array($returnValue, $fieldNames);
			return $finalResult;
		}
		else
			return $returnValue;
	}

	/**
	 * Fetch a result row as an associative array, a numeric array, or both
	 * @param object $mysqlResource The mysql returned resource result
	 * @param string $resultType The type of array that is to be fetched
	 * @return An array of strings that corresponds to the fetched row, or FALSE if there are no more rows
	 */
	public static function fetch_array($mysqlResource, $resultType=MYSQL_ASSOC)
	{
		return mysql_fetch_array($mysqlResource, $resultType);
	}

	/**
	 * Free all memory associated with the result identifier result
	 * @param object $mysqlResource The mysql returned resource result
	 * @return boolean TRUE on success or FALSE on failure
	 */
	public static function free_result($mysqlResource)
	{
		return mysql_free_result($mysqlResource);
	}

	/**
	 * Insert type request execution method
	 * @param string $request The request string
	 * @return int The inserted element id
	 */
	public function insertRequest($request)
	{
		// Generic request execution
		$this->request($request);

		// catch id before commit
		$id = mysql_insert_id($this->dbLink);

		// Returns last inserted id
		return $id;
	}

	/**
	 * Update type request execution method
	 * @param string $request The request string
	 * @return int The number of affected rows
	 */
	public function updateRequest($request)
	{
		// Generic request execution
		$this->request($request);

		// Returns the number of affected rows
		return $this->getAffectedRowCount();
	}

	/**
	 * Delete type request execution method
	 * @param string $request The request string
	 * @return int The number of affected rows
	 */
	public function deleteRequest($request)
	{
		// Generic request execution
		$this->request($request);

		// Returns the number of affected rows
		return $this->getAffectedRowCount();
	}

	/**
	 * Gets the last affected row count for current dbLink
	 * @return int The number of affected rows
	 */
	public function getAffectedRowCount()
	{
		return mysql_affected_rows($this->dbLink);
	}

	/**
	 * Optimizes a table
	 * @param string The table name
	 */
	public function optimizeTable($tableName)
	{
		$this->request('OPTIMIZE TABLE ' . $tableName);
	}

	/**
	 * Starts a transaction (to be used BEFORE commit OR rollback function)
	 */
	public function startTransaction()
	{
		$this->request('START TRANSACTION');
	}

	/**
	 * Commits current transactions
	 */
	public function commit()
	{
		$this->request('COMMIT');
	}

	/**
	 * Cancels current transactions
	 */
	public function rollback()
	{
		$this->request('ROLLBACK');
	}
}
?>