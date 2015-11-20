<?php
require_once '../init/init.inc.php';

$errorList = array();
$login = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
	try {
		$login = RequestTool::getParameter('login', RequestTool::PARAM_TYPE_ALPHANUM, true, null, RequestTool::METHOD_POST);
	} catch(ParameterException $e) {
		$errorList['login'] = AuthenticationTool::getLoginErrorMessage();
	}
	
	try {
		$password = RequestTool::getParameter('password', RequestTool::PARAM_TYPE_PASSWORD, true, null, RequestTool::METHOD_POST);
	} catch(ParameterException $e) {
		$errorList['login'] = AuthenticationTool::getLoginErrorMessage();
	}
	
	if(empty($errorList)) {
		try {
			$player = OsteoFactory::getElement('Player', null, 'player_login=\'' . $login . '\'');
			if($player->password !== $player->encryptPassword($password)) {
				throw new ElementNoResultException();
			}
			
			AuthenticationTool::getInstance()->setConnectedPlayer($player->id);
			RequestTool::redirect('/');
		} catch (ElementNoResultException $e) {
			$errorList['login'] = AuthenticationTool::getLoginErrorMessage();
		}
	};
}

//$smarty->assign('javascript', array('manageQuestion'));
//$smarty->assign('css', array('manageElement'));
$smarty->assign('login', $login);
$smarty->assign('errorList', $errorList);
$smarty->assign('page', 'login');

$smarty->display('layout.tpl');

require_once 'init/end.inc.php';
