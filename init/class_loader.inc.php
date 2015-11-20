<?php
// Manually includes this class used in class_loader
require_once('tools/StringTool.class.php');

class Autoloader
{
	public static function register()
	{
		// Register autoload method
		spl_autoload_register(array(__CLASS__, 'rpgAutoload'));
	}

	public static function rpgAutoload($className)
	{
		switch(true)
		{
			case StringTool::endsWith($className, 'Tool'):
				$file = 'tools' . DIRECTORY_SEPARATOR .$className .'.class.php';
				break;
			case StringTool::endsWith($className, 'Tracking'):
				$file = 'tracking' . DIRECTORY_SEPARATOR .$className .'.class.php';
				break;
				case StringTool::endsWith($className, 'Connection'):
				$file = 'connection' . DIRECTORY_SEPARATOR .$className .'.class.php';
				break;
			case StringTool::endsWith($className, 'Model'):
			case StringTool::endsWith($className, 'Factory'):
				$file = 'models' . DIRECTORY_SEPARATOR .$className .'.class.php';
				break;
			case StringTool::endsWith($className, 'Exception'):
				$file = 'exceptions' . DIRECTORY_SEPARATOR .$className .'.class.php';
				break;
			case StringTool::startsWith($className, 'Smarty'):
				// Smarty class, uses smarty autoloader
				return;
				break;
			default:
				// Include corresponding file
				$file = 'classes' . DIRECTORY_SEPARATOR . $className .'.class.php';
		}
		try
		{
			require_once($file);
		}
		catch (Exception $e)
		{
		}
	}
}

Autoloader::register();
?>
