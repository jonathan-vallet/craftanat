<?php
// Starts session before header is sent
@session_start();

error_reporting(E_ALL);
ini_set('display_errors', 'On');
ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . __DIR__ . '/..' . PATH_SEPARATOR . __DIR__ . '/../framework/php' . PATH_SEPARATOR . __DIR__ . '/../lib/php' . PATH_SEPARATOR . PATH_SEPARATOR . __DIR__ . '/../lib/smarty');

if($_SERVER['HTTP_HOST'] === 'www.craftanat.fr')
    require_once 'globals/globals_livedemo.php';
else 
    require_once 'globals/globals.php';

// init Smarty
require_once 'smarty.inc.php';

// Gets class_loader
require_once('class_loader.inc.php');
// Gets error handler
require_once('error_handler.inc.php');

// starts transaction
DatabaseFactory::startTransaction();

$authenticationInstance = AuthenticationTool::getInstance();
$isPlayerConnected = $authenticationInstance->isPlayerConnected();
if(!$isPlayerConnected) {
	RequestTool::redirect('/');
}
$adminUser = $authenticationInstance->getConnectedPlayer();
if(!$adminUser->is_admin) {
	RequestTool::redirect('/');
}

$smarty->assign('adminUser', $adminUser);
?>