<?php
/**
 * Params and request tool class
 */
class RequestTool
{
	// Request methods
	const METHOD_GET = 'GET';
	const METHOD_POST = 'POST';
	const METHOD_POST_OR_GET = 'POST_OR_GET';

	// Parameters regular expressions
	const PARAM_TYPE_INT = '^-?[0-9]+$';
	const PARAM_TYPE_UNSIGNED_INT = '^[0-9]+$';
	const PARAM_TYPE_FLOAT = '^-?[0-9]+\.*[0-9]*$';
	const PARAM_TYPE_UNSIGNED_FLOAT = '^[0-9]+\.*[0-9]*$';
	const PARAM_TYPE_BOOLEAN = '^[0|1]$';
	const PARAM_TYPE_ALPHA = '^[\w_\s]+$';
	const PARAM_TYPE_MESSAGE = '^[0-9\w_\s\'\.,\"\:\;\?\-\!]+$';
	const PARAM_TYPE_ALPHANUM = '^[0-9\w_]+$';
	const PARAM_TYPE_EMAIL = '^([a-zA-Z0-9&+_-](?:\.?[a-zA-Z0-9&+_-]+)*)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([a-zA-Z0-9\-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$';
	const PARAM_TYPE_PATH = '^[\w_/\.]+$';
	const PARAM_TYPE_PASSWORD = '^.*$';
	const PARAM_TYPE_SLASHED_DATE = '^[0-3][0-9]/[0-1][0-9]/[0-9]{2,4}$';
	const PARAM_TYPE_SLASHED_DATETIME = '^[0-3][0-9]/[0-1][0-9]/[0-9]{2,4} [0-2][0-9]:[0-5][0-9]:[0-5][0-9]$';
	const PARAM_TYPE_MYSQL_DATE = '^[0-9]{2,4}-[0-1][0-9]-[0-3][0-9]$';
	const PARAM_TYPE_MYSQL_DATETIME = '^[0-9]{2,4}-[0-1][0-9]-[0-3][0-9] [0-2][0-9]:[0-5][0-9]:[0-5][0-9]$';
	const PARAM_TYPE_FREE = '[^.]+';

	const PARAM_TYPE_COLOR = '^#?([0-9a-fA-F]){6}$';
	const PARAM_TYPE_ARRAY = '';

	/**
	 * Returns request parameter value
	 * @param string $paramName The parameter name
	 * @param string $type The parameter type (RequestTool::PARAM_TYPE_INT, RequestTool::PARAM_TYPE_STRING, etc)
	 * @param boolean $isMandatory Specifies if the parameter is mandatory (raises an exception if not set)
	 * @param string $defaultValue The default value returned if parameter is not set and not mandatory
	 * @param string $method The method used to pass parameters in the request (GET or POST)
	 * @return string The parameter value
	 */
	public static function & getParameter($paramName, $type, $isMandatory=true, $defaultValue=NULL, $method=RequestTool::METHOD_GET)
	{
		// If method is not specified, try both
		if ($method === RequestTool::METHOD_POST_OR_GET)
		{
			try
			{
				// Tries POST method, considers param as mandatory.
				return RequestTool::getParameter($paramName, $type, TRUE, $defaultValue, RequestTool::METHOD_POST);
			}
			catch (Exception $e)
			{
				// If nothing is found with POST method, as it is mandatory, an exception as raised. Try to find it with GET method.
				return RequestTool::getParameter($paramName, $type, $isMandatory, $defaultValue, RequestTool::METHOD_GET);
			}
		}

		// Get parameter list
		$paramList = RequestTool::getParameterList($method);
		
		// Checks if parameter is set
		if (ArrayTool::array_key_exists($paramName, $paramList)) {
			if ($type == RequestTool::PARAM_TYPE_ARRAY) {
				$paramValue = $paramList[$paramName];
			} else {
				if (StringTool::strlen($paramList[$paramName]) > 0)
					$paramValue = $paramList[$paramName];
			}
		}

		// Parameter is not set
		if (!isset($paramValue))
		{
			// Parameter is mandatory, raising exception
			if ($isMandatory)
				throw new ParameterException('"' . $paramName . '" request ' . $method . ' parameter is mandatory and not set');
			// else returns default value
			return $defaultValue;
		}

		// if magic quotes activated by server configuration, stripslashes
		if (get_magic_quotes_gpc())
			$paramValue = stripslashes($paramValue);

		// Checks if parameter value format is correct
		RequestTool::checkParameterValue($paramValue, $type);

		return $paramValue;
	}

	/**
	 * Returns request parameters list
	 * @param string $method The method used to pass parameters in the request (GET or POST)
	 * @return array The parameter list
	 */
	public static function getParameterList($method=RequestTool::METHOD_GET)
	{
		return $method == RequestTool::METHOD_GET ? $_GET : $_POST;
	}
	
	/**
	 * Checks type of a parameter value and throws an exception if value doesn't match type
	 * @param mixed $paramValue The parameter value (passed by reference)
	 * @param string $type The parameter type (RequestTool::PARAM_TYPE_INT, RequestTool::PARAM_TYPE_STRING, etc).
	 * @return mixed The parameter value (eventually modified)
	 */
	public static function checkParameterValue(&$paramValue, $type)
	{
		// Checks value type
		if ($type != RequestTool::PARAM_TYPE_ARRAY && !mb_ereg($type, $paramValue)) // param must match its type regex
		{
			throw new ParameterException('Invalid parameter format: "' . $paramValue . '" must be a ' . $type . '');
		}

		// Converts value to numeric
		switch($type)
		{
			case RequestTool::PARAM_TYPE_UNSIGNED_INT:
			case RequestTool::PARAM_TYPE_INT:
				$paramValue = StringTool::toInt($paramValue);
				break;
			case RequestTool::PARAM_TYPE_UNSIGNED_FLOAT:
			case RequestTool::PARAM_TYPE_FLOAT:
				$paramValue = StringTool::toFloat($paramValue);
				break;
			case RequestTool::PARAM_TYPE_BOOLEAN:
				$paramValue = (1 == $paramValue) ? true : false;
				break;
			case RequestTool::PARAM_TYPE_ARRAY:
				$paramValue = (is_array($paramValue) ? $paramValue : false);
				break;
		}

		return $paramValue;
	}

	/**
	 * Redirects current page to a new url
	 * @param string $url The new url
	 * @param string $method The redirection method (GET or POST)
	 */
	public static function redirect($url, $method=RequestTool::METHOD_GET)
	{
		require_once 'init/end.inc.php';
		switch ($method)
		{
			case RequestTool::METHOD_GET:
				header('Location: ' . $url);
				break;

			case RequestTool::METHOD_POST:
				// Todo: simulate a post form with input, and auto submit to redirect post data
				break;
		}
		exit;
	}

	/**
	 * Gets current request complete url
	 * @param boolean $includeGetParamList if GET params have to be added to URI
	 * @return string The url
	 */
	public static function getCurrentURL($includeGetParamList=true)
	{
		$url = 'http' . (!ArrayTool::array_key_exists('HTTPS', $_SERVER) || $_SERVER['HTTPS'] == 'off' ? '' : 's') . '://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];

		// Gets only string before the "?" if get params are ignored
		if (!$includeGetParamList)
			list($url) = StringTool::split('\?', $url);

		return $url;
	}
	
	/**
	 * Checks if current page is called by an ajax request
	 * @return boolean if the page is an ajax request or not
	 */
	public static function isAjaxRequest()
	{
		return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
	}
	
	/**
	 * Returns ip address used for the request
	 * @return string the ip adress
	 */
	public static function getIp()
	{
		return $_SERVER['REMOTE_ADDR'];
	}
}
?>
