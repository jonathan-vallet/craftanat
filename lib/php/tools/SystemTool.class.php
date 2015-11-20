<?php
/**
 * String utility class
 * @subpackage classes
 */
class SystemTool
{
	const CONTEXT_SCRIPT = 'script';
	const CONTEXT_ENGINE = 'engine';
	const CONTEXT_WEB = 'web';
	const CONTEXT_AJAX = 'ajax';

	public static $context = NULL;
	private static $uniqueId = NULL;

	/**
	 * Checks if the process is running in CLI mode (script launched by php
	 * @return boolean If the process is running in CLI mode or not
	 */
	public static function isScriptProcess()
	{
		return php_sapi_name() === 'cli';
	}

	/**
	 * Checks if the running process is an engine
	 * @return boolean if the running process is an engine or not
	 */
	public static function isEngineProcess()
	{
		return SystemTool::isScriptProcess() && SystemTool::$context == SystemTool::CONTEXT_ENGINE;
	}

	/**
	 * Checks if the process is running in Web mode
	 * @return boolean The process is running in Web mode
	 */
	public static function isWebProcess()
	{
		return !SystemTool::isScriptProcess();
	}

	/**
	 * Checks if current page is an ajax page
	 * @param boolean $checkIsWebProcess Specifies if methods has to use isWebProcess method
	 * @return boolean Current page is an ajax page
	 */
	public static function isAjaxContext()
	{
		return SystemTool::isWebProcess() && SystemTool::$context == SystemTool::CONTEXT_AJAX;
	}
}
?>
