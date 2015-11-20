<?php
require_once '../../init/initAdmin.inc.php';

OsteoFactory::getElementList('QuestionCategory');
$structureList = OsteoFactory::getElementList('Structure');
$structureId = RequestTool::getParameter('structureId', RequestTool::PARAM_TYPE_UNSIGNED_INT, false, null);
$questionList = OsteoFactory::getElementList('Question', $structureId !== null ? 'structure_id=' . $structureId : null);

//$smarty->assign('javascript', array('manageQuestion'));
//$smarty->assign('css', array('manageElement'));
$smarty->assign('structureList', Osteo::elementListToArray($structureList, Osteo::FORMAT_ADMIN));
$smarty->assign('questionList', Osteo::elementListToArray($questionList, Osteo::FORMAT_ADMIN));
$smarty->assign('currentUrl', RequestTool::getCurrentURL(false));
$smarty->assign('page', 'admin/questionList');

$smarty->display('admin/layout.tpl');

require_once 'init/end.inc.php';
?>