<?php
/**
 * Session tool class
 */
class SessionTool extends SingletonModel
{
	/**
	 * Unique instance (singleton) retrieval method
	 * @param mixed $param The class constructor param
	 * @return SessionTool The SessionTool unique instance
	 */
	public static function getInstance($param=NULL)
	{
		return self::getClassInstance(get_class(), $param);
	}

	/**
	 * Class destructor
	 */
	public function __destruct()
	{
		session_write_close();
	}

	/**
	 * Returns session parameters list
	 * @return array The parameter list
	 */
	public function getParameterList()
	{
		return $_SESSION;
	}

	/**
	 * Checks if a session parameter is set
	 * @param string $paramName The parameter name
	 * @return boolean Returns true if the parameter is set, else returns false
	 */
	public function issetParameter($paramName)
	{
		return ArrayTool::array_key_exists($paramName, $this->getParameterList());
	}

	/**
	 * Returns session parameter value
	 * @param string $paramName The parameter name
	 * @param boolean $isMandatory Specifies if the parameter is mandatory (raises an exception if not set)
	 * @param mixed $defaultValue The default value returned if parameter is not set and not mandatory
	 * @return string The parameter value
	 */
	public function getParameter($paramName, $isMandatory=true, $defaultValue=null)
	{
		// Parameter is set, return it
		if ($this->issetParameter($paramName))
		{
			$paramList = $this->getParameterList();
			return $paramList[$paramName];
		}

		// Parameter is not set and is mandatory
		if ($isMandatory)
			throw new Exception('Session parameter "' . $paramName . '" is not set');
			
		// Parameter is not set and not mandatory
		return $defaultValue;
	}

	/**
	 * Sets a session parameter
	 * @param string $paramName The parameter name
	 * @param mixed $paramValue The parameter value
	 */
	public function setParameter($paramName, $paramValue)
	{
		$_SESSION[$paramName] = $paramValue;
	}

	/**
	 * Unsets a session parameter
	 * @param string $paramName The parameter name
	 */
	public function unsetParameter($paramName)
	{
		if ($this->issetParameter($paramName))
			unset($_SESSION[$paramName]);
	}

	/**
	 * Empties session
	 */
	public function emptySession()
	{
		$_SESSION = array();
	}
}
?>
