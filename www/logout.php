<?php
require_once '../init/init.inc.php';

AuthenticationTool::getInstance()->logout();
RequestTool::redirect('/');

require_once 'init/end.inc.php';
