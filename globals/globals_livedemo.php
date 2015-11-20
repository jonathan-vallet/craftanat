<?php
$rootDir = dirname(dirname(__FILE__));
define('ENV_LEVEL_DEV', 'DEV');
define('ENV_LEVEL_PREPROD', 'PREPROD');
define('ENV_LEVEL_PROD', 'PROD');
define('SGBD_HOST', 'localhost');
define('SGBD_USER', 'craftanat');
define('SGBD_PASSWORD', 'veirUnlunt9');
define('SGBD_DB', 'craftanat');
define('LOG_MIN_LEVEL', 10);
define('LOG_DIR', $rootDir.'/logs');
define('LOG_FOPEN_MODE', 'ab');
define('IS_FILE_LOG_ENABLED', true);
define('ENV_LEVEL', 'PREPROD');
define('SMARTY_TEMPLATES_DIR', $rootDir.'/templates');
define('SMARTY_TEMPLATES_C_DIR', $rootDir.'/templates_c');
define('SMARTY_CACHE_DIR', $rootDir.'/cache');
define('ROOT_DIR', $rootDir);
define('WWW_DIR', $rootDir.'/www/');
define('IMAGE_PATH', '/images/');
define('CSS_DIR', '/css/');
define('JS_DIR', '/js/');
define('SOUND_DIR', '/sounds/');
define('IMAGE_PATH_FROM_ROOT', $rootDir.'/www/images/');
define('MINIFY_JS', false);
define('MINIFY_CSS', false);
?>
