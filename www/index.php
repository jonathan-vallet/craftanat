<?php
require_once '../init/init.inc.php';

//$smarty->assign('javascript', array('manageQuestion'));
//$smarty->assign('css', array('manageElement'));
$smarty->assign('page', 'index');
if(AuthenticationTool::getInstance()->isPlayerConnected()) {
	OsteoFactory::getElementList('Component');
	$structureList = OsteoFactory::getElementList('Structure', null, 'structure_category_order, structure_order ASC', 'structure LJ player_has_structure, structure_category IJ structure');
	$smarty->assign('structureList', Osteo::elementListToArray($structureList, Structure::FORMAT_PLAYER_STRUCTURE_LIST));
	$componentList = OsteoFactory::getElementList('Component');
	$smarty->assign('componentList', Osteo::elementListToArray($componentList, Component::FORMAT_PLAYER_COMPONENT_LIST));
}

$scoreDataList = Structure::getDatabaseConnection()->selectRequest('SELECT player_name, SUM(is_crafted) AS crafted, SUM(best_score) AS score, SUM(best_time) AS time FROM player INNER JOIN player_has_structure ON player.player_id = player_has_structure.player_id GROUP BY player.player_id ORDER BY crafted DESC, score DESC, time ASC LIMIT 10');
$scoreList = array();
foreach ($scoreDataList as $scoreData) {
    if(!$scoreData['time']) {
        $formattedTime = '-';
    } else {
        $formattedTime = DateTool::timestampToString($scoreData['time'], DateTool::FORMAT_MINUTES);
        if(StringTool::startsWith($formattedTime, '00m')) 
            $formattedTime = StringTool::truncateFirstChars($formattedTime, 4);
        if(StringTool::startsWith($formattedTime, '0')) 
            $formattedTime = StringTool::truncateFirstChars($formattedTime, 1);
    }
    
    $scoreList[] = array(
        'player_name' => $scoreData['player_name'],
        'crafted' => $scoreData['crafted'],
        'score' => $scoreData['score'],
        'time' => $formattedTime,
    );
}
$smarty->assign('scoreList', $scoreList);
$smarty->display('layout.tpl');

require_once 'init/end.inc.php';
?>