<?php
// Starts session before header is sent
session_start();
header('Content-type:application/json');

require_once 'globals/globals.php';

// Gets class_loader
require_once('class_loader.inc.php');
// Gets error handler
require_once('error_handler.inc.php');

$language = 'fr';

$t = TranslationTool::getInstance($language);

SystemTool::$context = SystemTool::CONTEXT_AJAX;

// starts transaction
DatabaseFactory::startTransaction();

$authenticationInstance = AuthenticationTool::getInstance();
if(!$authenticationInstance->isPlayerConnected())
	throw new PlayerNotConnectedException();

if(!RequestTool::isAjaxRequest())
	throw new Exception();
?>