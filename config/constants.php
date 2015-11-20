<?php
// Global constants
$constantList = array(
// Environement level list 
	'ENV_LEVEL_DEV' => 'DEV',
	'ENV_LEVEL_PREPROD' => 'PREPROD',
	'ENV_LEVEL_PROD' => 'PROD'
);

$configFolderPath = dirname(__FILE__);
$documentRoot = mb_substr($configFolderPath, 0, mb_strlen($configFolderPath) - 6);

// Constants per environement
switch($_SERVER['HTTP_HOST'])
{
	case 'localhost':
    case '127.0.0.1':
    case 'www.craftanat.jvallet.dev':
		// Sets "root" dir from this location, to avoid projecs wich root is not dreamaz/www folder
		$constantList = array_merge($constantList, array(
		// SGBD Constants
			'SGBD_HOST' => 'localhost',
			'SGBD_USER' => 'root',
			'SGBD_PASSWORD' => '',
			'SGBD_DB' => 'craftanat',		
		// Logs constants
			'LOG_MIN_LEVEL' => 0, // debug level
			'LOG_DIR' => $documentRoot . 'logs',
			'LOG_FOPEN_MODE' => 'ab',
			'IS_FILE_LOG_ENABLED' => true,
			'ENV_LEVEL' => $constantList['ENV_LEVEL_DEV'],
		// Smarty constants
			'SMARTY_TEMPLATES_DIR' => $documentRoot . 'templates',
			'SMARTY_TEMPLATES_C_DIR' => $documentRoot . 'templates_c',
			'SMARTY_CACHE_DIR' => $documentRoot . 'cache',
		// PATH
			'ROOT_DIR' => mb_substr($documentRoot, 0, -1),
			'WWW_DIR' => $documentRoot . 'www/',
			'IMAGE_PATH' => 'http://' . $_SERVER['HTTP_HOST'] . '/images/',
			'CSS_DIR' => 'http://' . $_SERVER['HTTP_HOST'] . '/css/',
			'JS_DIR' => 'http://' . $_SERVER['HTTP_HOST'] . '/js/',
			'SOUND_DIR' => 'http://' . $_SERVER['HTTP_HOST'] . '/sounds/',
			'IMAGE_PATH_FROM_ROOT' => $documentRoot . 'www/images/',
			'MINIFY_JS' => false,
			'MINIFY_CSS' => false,
		));
		break;
    case 'craftanat.adfab.fr':
        // Sets "root" dir from this location, to avoid projecs wich root is not dreamaz/www folder
        $constantList = array_merge($constantList, array(
        // SGBD Constants
            'SGBD_HOST' => 'localhost',
            'SGBD_USER' => 'root',
            'SGBD_PASSWORD' => '',
            'SGBD_DB' => 'craftanat',
        // Logs constants
            'LOG_MIN_LEVEL' => 0, // debug level
            'LOG_DIR' => $documentRoot . 'logs',
            'LOG_FOPEN_MODE' => 'ab',
            'IS_FILE_LOG_ENABLED' => true,
            'ENV_LEVEL' => $constantList['ENV_LEVEL_DEV'],
        // Smarty constants
            'SMARTY_TEMPLATES_DIR' => $documentRoot . 'templates',
            'SMARTY_TEMPLATES_C_DIR' => $documentRoot . 'templates_c',
            'SMARTY_CACHE_DIR' => $documentRoot . 'cache',
        // PATH
            'ROOT_DIR' => mb_substr($documentRoot, 0, -1),
            'WWW_DIR' => $documentRoot . 'www/',
            'IMAGE_PATH' => 'http://' . $_SERVER['HTTP_HOST'] . '/images/',
            'CSS_DIR' => 'http://' . $_SERVER['HTTP_HOST'] . '/css/',
            'JS_DIR' => 'http://' . $_SERVER['HTTP_HOST'] . '/js/',
            'SOUND_DIR' => 'http://' . $_SERVER['HTTP_HOST'] . '/sounds/',
            'IMAGE_PATH_FROM_ROOT' => $documentRoot . 'www/images/',
            'MINIFY_JS' => false,
            'MINIFY_CSS' => false,
        ));
        break;
	default:
		throw new Exception('host ' . $_SERVER['HTTP_HOST'] .' not recognized');
}
?>