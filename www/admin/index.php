<?php
require_once '../../init/initAdmin.inc.php';

$smarty->assign('page', 'admin/index');
$smarty->display('admin/layout.tpl');

require_once 'init/end.inc.php';
?>