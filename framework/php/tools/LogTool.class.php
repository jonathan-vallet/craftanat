<?php

/**
 * Logs tool class
 * @subpackage classes
 */
class LogTool extends SingletonModel
{
	// KO exit code
	const KO_RETURN_CODE = -2;
	const OK_RETURN_CODE = 0;
	
	// Log levels
	const DEBUG_LEVEL = 0;
	const NOTICE_LEVEL = 5;
	const WARNING_LEVEL = 10;
	const ERROR_LEVEL = 20;

	// Log line colors
	const COLOR_RED = "\033[0;31m";
	const COLOR_GREEN = "\033[1;32m";
	const COLOR_YELLOW = "\033[1;33m";
	const COLOR_WHITE = "\033[0;02m";
	const COLOR_BLUE = "\033[1;34m";
	const COLOR_PURPLE = "\033[1;35m";
	const COLOR_CYAN = "\033[1;36m";
	
	// File format
	const FORMAT_HTML = 'html';
	const FORMAT_TEXT = 'text';
	
	// log header array fields
	const DATE_FIELD = 'date';
	const TYPE_FIELD = 'type';
	const REMOTE_IP_FIELD = 'remoteIp';
	const HOST_FIELD = 'host';
	const URI_FIELD = 'uri';

	const DATETIME_FORMAT = 'H:i:s';

	private $detailedMode = FALSE; // detailed mode
	private $silentMode = FALSE; // silent mode
	
	/**
	 * Current prefix (can be a single string or an array)
	 * @var string/array
	 */
	public static $currentPrefix;

	/**
	 * Current treatment
	 * @var string
	 */
	public static $currentTreatment;

	/**
	 * Current log file date (needed to know when to rotate on next day log file)
	 * @var string
	 */
	private $currentLogDate;

	/**
	 * The log file resource
	 * @var resource
	 */
	private $logFile;

	/**
	 * Unique instance (singleton) retrieval method
	 * @return LogTool The LogTool unique instance
	 */
	public static function getInstance($param=null)
	{
		return self::getClassInstance(get_class(), $param);
	}

	/**
	 * Class constructor
	 */
	public function __construct()
	{
		// Opens log file
		$this->openLogFile();
	}

	/**
	 * Class destructor
	 */
	public function __destruct()
	{
		// Closes log file
		if ($this->logFile)
			$this->closeLogFile();
	}

	/**
	 * Opens log file
	 */
	private function openLogFile()
	{
		// Opens log file
		$logFilePath = $this->getLogFilePath();
		// Use @ to avoid error while opening log file, no error has to be reported if  fopen fails
		$this->logFile = fopen($logFilePath, LOG_FOPEN_MODE);
	}

	/**
	 * Closes log file
	 */
	private function closeLogFile()
	{
		// Closes log file
		if ($this->logFile)
			fclose($this->logFile);
	}

	/**
	 * Gets current log file path
	 * @return string Current log file path
	 */
	public function getLogFilePath()
	{
		$this->currentLogDate = DateTool::getTimeString(DateTool::FORMAT_MYSQL_DATE);
		
		// Checks which context is used
		if(SystemTool::isScriptProcess())
		{
			// process is an engine, get its name from file used
			$logFileName = basename($_SERVER['PHP_SELF'], '.php');	
		}
		else
			$logFileName = 'web';

		$logFilePath = LOG_DIR . '/' . $this->currentLogDate . '-' . $logFileName . '.log';
		return $logFilePath;
	}

	/**
	 * Switches to detailed mode
	 */
	public function setDetailedMode()
	{
		$this->detailedMode = TRUE;
	}

	/**
	 * Returns to normal mode
	 */
	public function unsetDetailedMode()
	{
		$this->detailedMode = FALSE;
	}
	
	/**
	 * Switches to silent mode
	 */
	public function setSilentMode()
	{
		$this->silentMode = TRUE;
	}

	/**
	 * Switches back to normal mode
	 */
	public function unsetSilentMode()
	{
		$this->silentMode = FALSE;
	}
	
	/**
	 * Traces a log message
	 * @param string $message The message
	 * @param int $logLevel The log level
	 * @color string $color The color of the message in the log
	 */
	private function log($message, $logLevel = LOG_MIN_LEVEL, $color=NULL)
	{
		// Tests log level
		if (!IS_FILE_LOG_ENABLED || !$this->logFile)
			return;

		if ($this->detailedMode || ($logLevel >= LOG_MIN_LEVEL && (!$this->silentMode || $logLevel >= LogTool::WARNING_LEVEL)))
		{
			$logHeaderList = array();
	
			// Sets headers
			// Log type
			if ($logLevel >= LogTool::ERROR_LEVEL)
				$logHeaderList[LogTool::TYPE_FIELD] = 'ERROR';
			else if ($logLevel >= LogTool::WARNING_LEVEL)
				$logHeaderList[LogTool::TYPE_FIELD] = 'WARNING';
			else if ($logLevel >= LogTool::NOTICE_LEVEL)
				$logHeaderList[LogTool::TYPE_FIELD] = 'NOTICE';
			else
				$logHeaderList[LogTool::TYPE_FIELD] = 'DEBUG';
	
			// Remote ip
			if (ArrayTool::array_key_exists('REMOTE_ADDR', $_SERVER))
				$logHeaderList[LogTool::REMOTE_IP_FIELD] = $_SERVER['REMOTE_ADDR'];
			else
				$logHeaderList[LogTool::REMOTE_IP_FIELD] = '';
	
			// Current uri/script
			$logHeaderList[LogTool::URI_FIELD] = basename($_SERVER['PHP_SELF']);
	
			// Current time
			$logHeaderList[LogTool::DATE_FIELD] = DateTool::getTimeString(DateTool::FORMAT_TIME);
	
			// host
			$logHeaderList[LogTool::HOST_FIELD] = php_uname('n');
	
			// computes header
			$logHeader = '[' . $logHeaderList[LogTool::DATE_FIELD] . '] ' . $logHeaderList[LogTool::TYPE_FIELD] . ' [' . ($logHeaderList[LogTool::REMOTE_IP_FIELD] ? $logHeaderList[LogTool::REMOTE_IP_FIELD] . ' > ' : '') . $logHeaderList[LogTool::HOST_FIELD] . '] ' . $logHeaderList[LogTool::URI_FIELD];
	
			// computes log line
			$logLine = $color . $logHeader.': '.str_replace(PHP_EOL, PHP_EOL . $logHeader . ': ', $message) . PHP_EOL;
	
			// Opens new log file if date has changed
			if (DateTool::getTimeString(DateTool::FORMAT_MYSQL_DATE) != $this->currentLogDate)
			{
				$this->closeLogFile();
				$this->openLogFile();
			}
	
			// Finally write the line in log file
			fwrite($this->logFile, $logLine);
		}
	}

	/**
	 * Logs a notice message
	 * @param string $message The warning message
	 * @color string $color The color of the message in the log
	 */
	public function logNotice($message, $color=LogTool::COLOR_CYAN)
	{
		// Logs message
		$this->log($message, LogTool::NOTICE_LEVEL, $color);
	}

	/**
	 * Logs a debug message
	 * @param string $message The debug message
	 * @color string $color The color of the message in the log
	 */
	public function logDebug($message, $color=LogTool::COLOR_WHITE)
	{
		// Logs message
		$this->log($message, LogTool::DEBUG_LEVEL, $color);
	}

	/**
	 * Logs a warning message
	 * @param string $message The warning message
	 * @color string $color The color of the message in the log
	 */
	public function logWarning($message, $color=LogTool::COLOR_YELLOW)
	{
		// Logs message
		$this->log($message, LogTool::WARNING_LEVEL, $color);
	}

	/**
	 * Logs an error message
	 * @param string $message The error message
	 * @color string $color The color of the message in the log
	 */
	public function logError($message, $color=LogTool::COLOR_RED)
	{
		// Logs message
		$this->log($message, LogTool::ERROR_LEVEL, $color);
	}

	/**
	 * Logs an exception
	 * @param Exception $exception The raised exception
	 * @param int $logLevel The log level
	 */
	public function logException($exception, $logLevel=LogTool::ERROR_LEVEL)
	{
		// Logs exception
		$message = get_class($exception) . ' raised : ' . $exception->getMessage() . PHP_EOL;
		$message .= $exception->getTraceAsString();

		// Previous exception log
		$previousException = $exception->getPrevious();
		if ($previousException !== NULL)
			$message .= PHP_EOL . 'Previous exception :';

		$this->log($message, $logLevel, LogTool::COLOR_RED);

		// Logs previous exceptions recusively
		if ($previousException !== NULL)
			$this->logException($previousException, $logLevel);
	}

	/**
	 * Gets the trace & log infos from not-framework functions. For dev purpose only.
	 * @param int $logLevel The log level
	 */
	public function logTrace($color=LogTool::COLOR_CYAN)
	{
		if ($this->detailedMode)
		{
			$traceList = debug_backtrace();
			$logBuffer = '';
			foreach ($traceList as $trace)
				if (!isset($trace['function']) || $trace['function'] != 'logTrace')
				{
					if ($logBuffer)
						$logBuffer .= PHP_EOL;
					$logBuffer .= (isset($trace['file']) ? basename($trace['file']) : 'Unknown file') . 
						'line ' . (isset($trace['line']) ? $trace['line'] : 'unknown') .
						': ' .
						(isset($trace['class']) ? $trace['class'] . '::' : '') .
						(isset($trace['function']) ? $trace['function'] : 'unknown') . ' function';
				}

			$this->log($logBuffer, LogTool::DEBUG_LEVEL, $color);
		}
	}
}
?>
