<?php
require_once '../../init/initAdmin.inc.php';

OsteoFactory::getElementList('StructureCategory');
$structureList = OsteoFactory::getElementList('Structure');

//$smarty->assign('javascript', array('manageQuestion'));
//$smarty->assign('css', array('manageElement'));
$smarty->assign('structureList', Osteo::elementListToArray($structureList, Osteo::FORMAT_ADMIN));
$smarty->assign('page', 'admin/structureList');

$smarty->display('admin/layout.tpl');

require_once 'init/end.inc.php';
?>