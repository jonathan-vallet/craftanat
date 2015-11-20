<?php
/**
 * Mysqli database connection class
 * @subpackage classes
 */
abstract class MysqliDatabaseConnectionModel extends DatabaseConnectionModel
{
	const ARRAY_TYPE_ASSOC = MYSQLI_ASSOC;
	const ARRAY_TYPE_NUM = MYSQLI_NUM;
	const ARRAY_TYPE_BOTH = MYSQLI_BOTH;

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
		return isset($this->dbLink) && $this->dbLink !== NULL && mysqli_stat($this->dbLink) !== NULL;
	}

	/**
	 * Connects to database
	 */
	protected function connect($transactionCharacterSet='UTF8')
	{
		LogTool::getInstance()->logDebug('Connecting ' . $this->username . ' to ' . get_class() . ' ' . $this->dbName . '@' . $this->host . ' database.');

		// Database host connection
		$this->dbLink = mysqli_connect($this->host, $this->username, $this->password, $this->dbName);
		if (!$this->dbLink)
			throw new DatabaseConnectionException(get_class() . ' : Unable to connect ' . $this->username . ' to ' . $this->dbName . '@' . $this->host . ' database');

		// Database Transaction Character Set definition
		$this->request('SET NAMES ' . $transactionCharacterSet);
	}

	/**
	 * Disconnects from database
	 */
	protected function disconnect()
	{
		// Database disconnection
		mysqli_close($this->dbLink);
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

		return mysqli_real_escape_string($this->dbLink, $string);
	}

	/**
	 * Generic request execution method
	 * @param string $request The request string
	 * @return resource The request results or an exception if any error occures
	 */
	protected function & request($request)
	{
		$timer = TimerTool::getInstance();
		$timer->tick('Executing request ('.$this->dbName.') : '.$request, 'ms', 0, FALSE, LogTool::COLOR_GREEN);
		$timer->incrementCount();

		// Request execution
		$result = mysqli_query($this->dbLink, $request);
		if (!$result)
		{
			$mysqlErrNo = mysqli_errno($this->dbLink);
			$mysqlErrMsg = mysqli_error($this->dbLink);

			// Mysql connection lost : reconnect and retry query
			if (!$this->isConnected())
			{
				$logInstance = LogTool::getInstance();
				$logInstance->logWarning('Connection lost (Error #' . $mysqlErrNo . ' : ' . $mysqlErrMsg . ') during request execution, will reconnect and retry it : ' . $request);
				$this->connect();
				$logInstance->logWarning('Retrying request (' . $this->dbName . ') : ' . $request);
				$result = mysqli_query($this->dbLink, $request);
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
			$fieldDataList = mysqli_fetch_fields($result);
			$fieldNameList = array();
			foreach($fieldDataList as $fieldData)
				$fieldNameList[] = $fieldData->name;
		}

		// Browses result rows and adds it to the result array
		if ($fetchResult === TRUE)
		{
			$returnValue = array();
			while ($row = mysqli_fetch_array($result, $resultType))
				$returnValue[] = $row;

			// Free results ressource
			mysqli_free_result($result);
		}
		else
			$returnValue = $result;

		if ($returnFieldNames === TRUE)
		{
			$finalResult = array($returnValue, $fieldNameList);
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
		return mysqli_fetch_array($mysqlResource, $resultType);
	}

	/**
	 * Free all memory associated with the result identifier result
	 * @param object $mysqlResource The mysql returned resource result
	 * @return boolean TRUE on success or FALSE on failure
	 */
	public static function free_result($mysqlResource)
	{
		return mysqli_free_result($mysqlResource);
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
		$id = mysqli_insert_id($this->dbLink);

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
	 * Create type request execution method
	 * @param string $request The request string
	 * @return boolean TRUE on success or FALSE on failure
	 */
	public function createRequest($request)
	{
		// Generic request execution
		return $this->request($request);
	}

	/**
	 * Imports data, creating a table and inserting rows
	 * @param string $tablename The destination table's name
	 * @param array $head The column headers
	 * @param array $data The data to import
	 * @param string $mismatchtable Optional table's name to store type mismatch alerts
	 * @return boolean TRUE on success or FALSE on failure
	 */
	public function importRequest($tablename = null, $head = null, $data = null, $mismatchtable = null)
	{
		if ( $tablename === null || $head === null || $data === null ) { // mandatory params
			return false;
		}
		
		try
		{
			$logInstance = LogTool::getInstance();
			$coltype = array(); // tells int, float or varchar for each column
			$nadata = array(); // stores type mismatch alerts in a second table
			$nb_lines_to_check = 5;
			$sql = 'CREATE TABLE IF NOT EXISTS '.$tablename.' (';
			if($mismatchtable) $sql2 = 'CREATE TABLE IF NOT EXISTS '.$mismatchtable.' (';
			$nb_col_to_check = (array_key_exists(0, $head) ? count($head) - 1 : count($head));
			// if columns begin at #1, there's no id and we won't create one ;
			// if they begin at #0, it probably means there's an id, so let's handle it
			if(array_key_exists(0, $head)) { // " && $head[0] == 'id' " unnecessary
				$coltype[$head[0]] = 'int(11)';
				$sql .= '`'.$head[0].'` '.$coltype[$head[0]].', ';
			}
			for($i = 1; $i <= $nb_col_to_check; $i++) {
				$colindex = StringTool::cleanVarName($head[$i]);
				if($colindex == SGBD_SDF_MOLSTRUCCOL) { 
					$coltype[$colindex] = 'text';
				}
				else {
					$nb_flt = 0;
					$nb_int = 0;
					$nb_str = 0;
					// try the first $nb_lines_to_check lines of data
					for($j = 0; $j < $nb_lines_to_check; $j++) {
						//$logInstance->logDebug("j : ".$j." - colindex : ".$colindex);
						if(array_key_exists($j, $data)) { // "false" if less than 5 lines in data
							$temp = $data[$j][$colindex];
							if(StringTool::isFloat($temp)) $nb_flt++;
							elseif(StringTool::isInt($temp)) $nb_int++;
							else $nb_str++;
						}
					}
					if($nb_flt > 0 && ($nb_flt + $nb_int) >= ($nb_lines_to_check - 1)) // we tolerate 1 line with wrong type
						$coltype[$colindex] = 'float';
					elseif($nb_int >= ($nb_lines_to_check - 1))
						$coltype[$colindex] = 'int(11)';
					else
						$coltype[$colindex] = 'text'; // varchar too short sometimes
					if($mismatchtable)
						$sql2 .= '`'.$head[$i].'` varchar(50), '; // store mismatches directly in this table (not just 0/1/null)
				}
				$sql .= '`'.$head[$i].'` '.$coltype[$colindex].', ';
				
			}
			// the line below gets rid of the comma
			$sql = substr($sql,0,strlen($sql)-2);
			$sql .= ') ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';
			
			$ok = $this->createRequest($sql);
			if(!$ok) return false;
			
			// ensure it's empty
			$sql = 'TRUNCATE TABLE '.$tablename.';';
			$ok = $this->deleteRequest($sql);
			
			if($mismatchtable) {
				$sql2 = substr($sql2,0,strlen($sql2)-2);
				$sql2 .= ') ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';
				$ok = $this->createRequest($sql2);
				$sql2 = 'TRUNCATE TABLE '.$mismatchtable.';';
				$ok = $this->deleteRequest($sql2);
			}
			
			// now insert data
			$logInstance->setSilentMode();
			$entry = array();
			if($mismatchtable) $entry2 = array();
			foreach( $data as $key => $row ) {
				$sql = 'INSERT INTO '.$tablename.' VALUES (\'';
				foreach( $row as $field => $value ) {
					
					if(($coltype[$field] == 'float' && !StringTool::isFloat($value) && !StringTool::isInt($value)) || ($coltype[$field] == 'int(11)' && !StringTool::isInt($value) && !StringTool::isFloat($value))) {
						if($mismatchtable) {
							$entry2[] = ($value == "") ? "NULL" : "1:".$value; // store mismatches directly in this table, with "1:" prefix
						}
						$value = "NULL";
					} elseif($value !== "" && !is_null($value)) {
						if($mismatchtable && $field != SGBD_SDF_MOLSTRUCCOL) $entry2[] = 0;
					} else {
						$value = "NULL";
						if($mismatchtable) $entry2[] = "NULL";
					}
					$entry[] = $value;
				}
				$sql .= implode("','", $entry);
				$sql .= '\');';
				$sql = str_replace('\'NULL\'', 'NULL' , $sql);
				$entry = array();
				$ok = $this->insertRequest($sql);
				
				if($mismatchtable) {
					$sql2 = 'INSERT INTO '.$mismatchtable.' VALUES (\'';
					$sql2 .= implode("','", $entry2);
					$sql2 .= '\');';
					$sql2 = str_replace('\'NULL\'', 'NULL' , $sql2);
					$ok = $this->insertRequest($sql2);
					$entry2 = array();
				}
			}
			$logInstance->unsetSilentMode();
			return true;
		}
		catch (ParameterException $e)
		{
			return false;
		}
	}

	/**
	 * Checks if table exists
	 * @param string $tablename The table's name
	 * @return boolean TRUE on exist or FALSE on non-exist
	 */
	public function doesTableExist($tablename)
	{
		$val = mysqli_query($this->dbLink, 'select 1 from `'.$tablename.'`');
		return ($val !== FALSE ? true : false);
	}

	/**
	 * Gets the last affected row count for current dbLink
	 * @return int The number of affected rows
	 */
	public function getAffectedRowCount()
	{
		return mysqli_affected_rows($this->dbLink);
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