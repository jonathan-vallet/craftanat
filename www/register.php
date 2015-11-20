<?php
require_once '../init/init.inc.php';

$errorList = array();
$name = '';
$login = '';
$email = '';
$isStudent = false;

if($_SERVER['REQUEST_METHOD'] === 'POST') {
	try {
		$login = RequestTool::getParameter('login', RequestTool::PARAM_TYPE_ALPHANUM, true, null, RequestTool::METHOD_POST);
		try {
			OsteoFactory::getElement('Player', null, 'player_login = \'' . $login . '\'');
			$errorList['login'] = 'Login déjà utilisée';
		} catch(ElementNoResultException $e) {
			// Email not used, do nothing
		}
	} catch(ParameterException $e) {
		$errorList['login'] = 'Login incorrect';
	}
	
	try {
		$email = RequestTool::getParameter('email', RequestTool::PARAM_TYPE_EMAIL, true, null, RequestTool::METHOD_POST);
		try {
			OsteoFactory::getElement('Player', null, 'player_email = \'' . $email . '\'');
			$errorList['email'] = 'Adresse e-mail déjà utilisée';
		} catch(ElementNoResultException $e) {
			// Email not used, do nothing
		}
	} catch(ParameterException $e) {
		$errorList['email'] = 'Adresse e-mail incorrecte';
	}
	
	try {
		$password = RequestTool::getParameter('password', RequestTool::PARAM_TYPE_PASSWORD, true, null, RequestTool::METHOD_POST);
		$passwordConfirmation = RequestTool::getParameter('passwordConfirmation', RequestTool::PARAM_TYPE_PASSWORD, true, null, RequestTool::METHOD_POST);
		if($password !== $passwordConfirmation) {
			$errorList['password'] = 'Les mots de passe ne concordent pas';
		}
	} catch(ParameterException $e) {
		$errorList['password'] = 'Mot de passe incorrect';
	}
	
	try {
		$name = RequestTool::getParameter('name', RequestTool::PARAM_TYPE_MESSAGE, true, null, RequestTool::METHOD_POST);
	} catch(ParameterException $e) {
		$errorList['name'] = 'Nom incorrect';
	}

	$isStudent = RequestTool::getParameter('isStudent', RequestTool::PARAM_TYPE_BOOLEAN, false, 0, RequestTool::METHOD_POST);
	
	if(empty($errorList)) {
		$player = new Player();
		$player->login = $login;
		$player->email = $email;
		$player->name = $name;
		$player->password = $password;
		$player->is_student = $isStudent;
		$player->add();
		AuthenticationTool::getInstance()->setConnectedPlayer($player->id);
		RequestTool::redirect('/');
	};
}

//$smarty->assign('javascript', array('manageQuestion'));
//$smarty->assign('css', array('manageElement'));
$smarty->assign('name', $name);
$smarty->assign('login', $login);
$smarty->assign('email', $email);
$smarty->assign('isStudent', $isStudent);
$smarty->assign('errorList', $errorList);
$smarty->assign('page', 'register');

$smarty->display('layout.tpl');

require_once 'init/end.inc.php';
