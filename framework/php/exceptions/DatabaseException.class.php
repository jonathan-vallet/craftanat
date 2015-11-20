<?php
/**
 * Database exceptions class
 * @subpackage classes
 */
class DatabaseException extends GenericErrorException
{
	const ERROR_CODE_COLUMN_CANNOT_BE_NULL = 1048;
	const ERROR_CODE_UNKNOWN_COLUMN = 1054;
	const ERROR_CODE_DUPLICATE_ENTRY = 1062;
	const ERROR_CODE_UNKNOWN_TABLE = 1146;
	const ERROR_CODE_LOCK_WAIT_TIMEOUT = 1205;
	const ERROR_CODE_DEADLOCK = 1213;

	public static function getSpecificException($errorMessage, $errorCode)
	{
		switch($errorCode)
		{
			case DatabaseException::ERROR_CODE_DUPLICATE_ENTRY;
				return new DatabaseDuplicateEntryException($errorMessage);

			case DatabaseException::ERROR_CODE_UNKNOWN_COLUMN;
				return new DatabaseUnknownColumnException($errorMessage);

			case DatabaseException::ERROR_CODE_UNKNOWN_TABLE;
				return new DatabaseUnknownTableException($errorMessage);

			case DatabaseException::ERROR_CODE_COLUMN_CANNOT_BE_NULL;
				return new DatabaseColumnCannotBeNullException($errorMessage);

			case DatabaseException::ERROR_CODE_DEADLOCK;
				return new DatabaseDeadlockException($errorMessage);

			case DatabaseException::ERROR_CODE_LOCK_WAIT_TIMEOUT;
				return new DatabaseLockWaitTimeoutException($errorMessage);

			default:
				return new DatabaseException($errorMessage);
		}
	}
}
?>
