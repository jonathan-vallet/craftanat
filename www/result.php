<?php
require_once '../init/init.inc.php';

if(!$isPlayerConnected) {
	RequestTool::redirect('/');
}

// Checks if quizz is initialized
$player = $authenticationInstance->getConnectedPlayer();
if(!$player->current_quizz) {
	RequestTool::redirect('/');
}

$quizz = json_decode($player->current_quizz, true);
$correctAnswerNumber = 0;
foreach($quizz['questionList'] as $quizzQuestion) {
	if($quizzQuestion['answer'] === true) {
		++$correctAnswerNumber;	
	}
}
$time = DateTool::getTimestamp() - $quizz['startDate'];

$player->current_quizz = null;
$player->update();

OsteoFactory::getElementList('Structure');
$currentStructure = OsteoFactory::getElement('Structure', $quizz['structureId']);

$percentCompleted = $correctAnswerNumber / count($quizz['questionList']);

$isSuccess = false;
$isCrafted = false;
if($percentCompleted >= 0.6) {
	$isSuccess = true;
    if(!$quizz['isCraft']) {
        // Gives player some loot
        $gainQuantity = ceil($percentCompleted * $currentStructure->component_quantity * 0.5 * rand(40, 100) / 100);
        $component = $currentStructure->getParentStructureCategory()->getParentComponent();
    	try {
    		$playerComponent = OsteoFactory::getElement('PlayerHasComponent', null, 'player_id=' . $player->id . ' AND component_id=' . $component->id);
    		$playerComponent->quantity = $playerComponent->quantity + $gainQuantity;
    		$playerComponent->update();
    	} catch (ElementNoResultException $e) {
    		$playerComponent = new PlayerHasComponent();
    		$playerComponent->player_id = $player->id;
    		$playerComponent->component_id = $component->id;
    		$playerComponent->quantity = $gainQuantity;
    		$playerComponent->add();
    	}
    	$smarty->assign('gainQuantity', $gainQuantity);
    	$smarty->assign('gainComponent', $component->name);
    	$smarty->assign('gainComponentCode', $component->code_name);
    } else {
		$playerStructure = OsteoFactory::getElement('PlayerHasStructure', null, 'player_id = ' . $player->id . ' AND structure_id = ' . $quizz['structureId']);
		$playerStructure->is_crafted = true;
		$playerStructure->update();
		$isCrafted = true;
        
        // Removes component from player inventory
        $playerComponent = OsteoFactory::getElement('PlayerHasComponent', null, 'player_id = ' . $player->id . ' AND component_id = ' . $currentStructure->getParentStructureCategory()->component_id);
        $playerComponent->quantity -= $currentStructure->component_quantity;
        $playerComponent->update();
	}
}

// Updates score if success
$playerStructure = OsteoFactory::getElement('PlayerHasStructure', null, 'player_id = ' . $player->id . ' AND structure_id = ' . $quizz['structureId']);
$hasAlreadyPlayed = ($playerStructure->best_score / count($quizz['questionList'])) < 0.6;
$previousBestScore = $playerStructure->best_score;
$previousBestTime = $playerStructure->best_time;
$isBestScore = false;
$isBestTime = false;
if($correctAnswerNumber >= $playerStructure->best_score && !$quizz['isCraft']) {
	if($correctAnswerNumber > $playerStructure->best_score)
		$isBestScore = true;
	$playerStructure->best_score = $correctAnswerNumber;
	if($correctAnswerNumber > $previousBestScore || ($correctAnswerNumber === $previousBestScore && $time < $previousBestTime)) {
		$playerStructure->best_time = $time;
		$isBestTime = true;
	}
	$playerStructure->update();
}

//Checks if structures are unlocked
if($isSuccess) {
	$relatedStructureList = OsteoFactory::getElementList('StructureRequiredStructure', 'required_structure_id=' . $quizz['structureId']);
	foreach($relatedStructureList as $relatedStructure) {
		$structure = OsteoFactory::getElement('Structure', $relatedStructure->structure_id);
		$requiredStructureList = OsteoFactory::getElementList('StructureRequiredStructure', 'structure_id=' . $structure->id . ' AND required_structure_id !=' . $quizz['structureId']);

		$isUnlocked = true;
		foreach($requiredStructureList as $requiredStructure) {
			try {
				OsteoFactory::getElement('PlayerHasStructure', null, 'player_id=' . $player->id . ' AND structure_id=' . $requiredStructure->required_structure_id);
			}
			catch(ElementNoResultException $e) {
				LogTool::getInstance()->logWarning('no unlock');
				$isUnlocked = false;
				break;
			}
		}
		if($isUnlocked) {
		    try {
                $newPlayerStructure = new PlayerHasStructure();
                $newPlayerStructure->player_id = $player->id;
                $newPlayerStructure->structure_id = $relatedStructure->structure_id;
                $newPlayerStructure->add();
		    } catch (DatabaseDuplicateEntryException $e) {
		        // Already in database
		    }
		}
	}
}

if(!$quizz['isCraft']) {
	$questionDataList = array();
	foreach($quizz['questionList'] as $questionData) {
		$question = OsteoFactory::getElement('Question', $questionData['questionId']);
		$questionDataList[] = $question->toArray(Question::FORMAT_RESULT, $questionData['answer']);
	}
	$smarty->assign('questionList', $questionDataList);
}

$smarty->assign('isCraft', $quizz['isCraft']);
$smarty->assign('isSuccess', $isSuccess);
$smarty->assign('previousBestScore', $previousBestScore);
$smarty->assign('previousBestTime', $previousBestTime);
$smarty->assign('isBestScore', $isBestScore);
$smarty->assign('isBestTime', $isBestTime);
$smarty->assign('correctAnswerNumber', $correctAnswerNumber);
$formattedTime = DateTool::timestampToString($time, DateTool::FORMAT_MINUTES);
if(StringTool::startsWith($formattedTime, '00m')) 
	$formattedTime = StringTool::truncateFirstChars($formattedTime, 4);
if(StringTool::startsWith($formattedTime, '0')) 
	$formattedTime = StringTool::truncateFirstChars($formattedTime, 1);
$smarty->assign('totalTime', $formattedTime);
$smarty->assign('totalQuestionNumber', count($quizz['questionList']));
$smarty->assign('page', 'score');
$smarty->display('layout.tpl');

require_once 'init/end.inc.php';
?>