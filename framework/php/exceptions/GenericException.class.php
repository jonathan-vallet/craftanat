<?php

/**
 * Generic exception classes
 */
abstract class GenericException extends Exception
{
	/**
	 * Class constructor
	 * @param string $message The exception's detailed message. This message will appear in server logs only.
	 * @param int $code The exception code
	 * @param Exception $previous The previous exception used for the exception chaining
	 */
	public function __construct($message='', $code=0, $previous=NULL)
	{
		parent::__construct($message, $code, $previous);
	}
	
	/**
	 * Returns a localized error message corresponding to current exception
	 * @return string The localized exception message
	 */
	public function getUserLocalizedMessage()
	{
		return TranslationTool::getInstance()->translate(StringTool::upper(preg_replace('/(?<=[a-z])([A-Z])/', '_$1', get_class($this))));
	}
}
?>
