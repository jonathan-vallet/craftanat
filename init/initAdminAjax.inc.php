<?php
// Starts session before header is sent
session_start();
require_once 'globals/globals.php';
require_once 'core/functions.php';

// init Smarty
require_once 'smarty.inc.php';

// Gets class_loader
require_once('class_loader.inc.php');
// Gets error handler
require_once('error_handler.inc.php');

SystemTool::$context = SystemTool::CONTEXT_AJAX;

// starts transaction
DatabaseFactory::startTransaction();

$authenticationInstance = AuthenticationTool::getInstance();
$isPlayerConnected = $authenticationInstance->isPlayerConnected();

// TODO: Checks that player has access to the admin part
?>