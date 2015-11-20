<?php
// Sets error reporting level
error_reporting(E_ALL);

/**
 * Error handler
 * @param int $errno Contains the level of the error raised, as an integer
 * @param int $errstr Contains the error message, as a string
 * @param int $errfile Contains the filename that the error was raised in, as a string
 * @param int $errline Contains the line number the error was raised at, as an integer
 */
function errorHandler($errno, $errstr, $errfile, $errline)
{
	// the error has been supressed with an "@"
	if (error_reporting() === 0)
		return FALSE;

	$errorMessage = 'Error ('.$errno.') : "'.$errstr.'" in '.$errfile.' (line '.$errline.')';

	switch ($errno)
	{
		case E_WARNING:
		case E_USER_WARNING:
		case E_NOTICE:
			throw new InternalWarningException($errorMessage);
			break;
		default:
			throw new InternalErrorException($errorMessage);
			break;
	}

	return TRUE;
}
set_error_handler('errorHandler', E_ALL);


/**
 * Exception handler
 * @param Exception $exception The exception raised
 */
function exceptionHandler($exception)
{
	try
	{
		$exceptionMessage = NULL;
		
		// If a transaction is in progress, rollback it
		if (DatabaseFactory::isTransactionInProgress())
			DatabaseFactory::rollback();

		// logs exception before doing anything else, for logs to be filled even if an exception will occur during the exception treatment
		$logInstance = LogTool::getInstance();
		// debug exception in web context

		if ($exception instanceof GenericWarningException)
		{
			// logs warning
			$logInstance->logException($exception, LogTool::WARNING_LEVEL);
		}
		// other exceptions
		else
		{
			// logs error
			$logInstance->logException($exception);
		}
		
		$exceptionClass = get_class($exception);
		
		if ($exception instanceof GenericException)
		{
			// Displays message
			if(TranslationTool::isTranslationAllowed())
				$exceptionMessage = $exception->getUserLocalizedMessage();
			else
				$exceptionMessage = $exception->getMessage();	
		}
		else
		{
			// If error is not managed, displays generic message
			$exceptionMessage = 'GENERIC_EXCEPTION';
		}
	
		if(SystemTool::isAjaxContext())
			echo json_encode(array('error' => $exceptionMessage), true);			
		else
			echo $exceptionMessage;
	}
	catch (Exception $e)
	{
		echo $e->getMessage();
		try
		{
			LogTool::getInstance()->logException($e);
			echo($exceptionClass.' raised : '.$exceptionMessage);
			echo('+ '.get_class($e).' while handling exception : '.$e->getMessage());

			exit(LogTool::KO_RETURN_CODE);
		}
		catch (Exception $e2)
		{
			try
			{
				LogTool::getInstance()->logException($e2);
				echo($exceptionClass.' raised (unable to display exception details)');
				exit(LogTool::KO_RETURN_CODE);
			}
			catch (Exception $e3)
			{
				echo('unable to display exception details');
				exit;
			}
		}
	}
}
set_exception_handler('exceptionHandler');
?>
