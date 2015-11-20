<?php
require_once '../init/init.inc.php';

if(!$isPlayerConnected) {
	RequestTool::redirect('/');
}

// Checks if quizz is initialized
$player = $authenticationInstance->getConnectedPlayer();
$structureId = RequestTool::getParameter('structureId', RequestTool::PARAM_TYPE_UNSIGNED_INT, false, null);
if($structureId !== null || !$player->current_quizz) {
	try {
		$structureId = RequestTool::getParameter('structureId', RequestTool::PARAM_TYPE_UNSIGNED_INT);
		$structure = OsteoFactory::getElement('Structure', $structureId);
	} catch (Exception $e) {
		// Incorrect parameter or invalid structure: do nothing
		RequestTool::redirect('/');
	}
	
	$isCraft = RequestTool::getParameter('craft', RequestTool::PARAM_TYPE_BOOLEAN, false, false);
	$questionList = OsteoFactory::getElementList('Question', ($isCraft ? 'question_is_in_craft_quizz=1 AND ' : '') . 'structure_id=' . $structureId, 'RAND() LIMIT ' . $structure->question_number);
	$quizz = array('structureId' => $structureId, 'questionList' => array());
	foreach($questionList as $question) {
		$quizz['questionList'][] = array('questionId' => $question->id, 'answer' => null);
	}
	$quizz['isCraft'] = $isCraft;
	$quizz['startDate'] = DateTool::getTimestamp();
	
	$player->current_quizz = json_encode($quizz);
	$player->update();
	$currentQuestionIndex = 0;
	$currentQuestion = reset($questionList);
} else if($player->current_quizz) {
	$quizz = json_decode($player->current_quizz, true);

	foreach($quizz['questionList'] as $questionNumber => $quizzQuestion) {
		if($quizzQuestion['answer'] === null) {
			$currentQuestion = OsteoFactory::getElement('Question', $quizzQuestion['questionId']);
			$currentQuestionIndex = $questionNumber;
			break;
		}
	}
	
	// No questionwith no answer: test is over
	if(!isset($currentQuestion)) {
		RequestTool::redirect('/result.php');
	}
} else {
	RequestTool::redirect('/');
}

// Form submitted: check question answer
if($_SERVER['REQUEST_METHOD'] === 'POST') {
	$answerList = $currentQuestion->getAnswerList();
	switch($currentQuestion->question_category_id) {
		//QCM
		case 1:
			$isAnswerCorrect = true;
			$correctAnswerNumber = 0;
			foreach($answerList as $answer) {
				if($answer->is_correct)
					++$correctAnswerNumber;
			}
			if($correctAnswerNumber === 1) {
				$answerValue = RequestTool::getParameter('answer', RequestTool::PARAM_TYPE_UNSIGNED_INT, false, false, RequestTool::METHOD_POST);
			}
			foreach($answerList as $answer) {
				if($correctAnswerNumber > 1) {
					$answerValue = RequestTool::getParameter('answer' . $answer->x, RequestTool::PARAM_TYPE_BOOLEAN, false, false, RequestTool::METHOD_POST);
					if($answer->is_correct != $answerValue) {
						$isAnswerCorrect = false;
						break;
					}
				} else {
					if($answer->is_correct && $answer->x !== $answerValue) {
						$isAnswerCorrect = false;
						break;
					}					
				}
			}
			break;
		case 2:
			$isAnswerCorrect = true;
			foreach($answerList as $answer) {
				$answerValue = RequestTool::getParameter('answer' . $answer->x, RequestTool::PARAM_TYPE_FREE, false, false, RequestTool::METHOD_POST);
				if($answer->text != $answerValue) {
					$isAnswerCorrect = false;
					break;
				}
			}
			break;
		case 3:
			$isAnswerCorrect = true;
			$answerNumber = 1;
			foreach($answerList as $answer) {
				$answerValue = RequestTool::getParameter('answer' . $answerNumber++, RequestTool::PARAM_TYPE_FREE, false, false, RequestTool::METHOD_POST);
				if($answer->text != $answerValue) {
					$isAnswerCorrect = false;
					break;
				}
			}
			break;
		case 4:
			$isAnswerCorrect = false;
			$answerValue = RequestTool::getParameter('answer', RequestTool::PARAM_TYPE_FREE, false, false, RequestTool::METHOD_POST);
			$answerData = explode('/', $answerValue);
			if(count($answerData) === 2) {
				foreach($answerList as $answer) {
					$distance = sqrt(($answer->x - $answerData[0]) * ($answer->x - $answerData[0]) + ($answer->y - $answerData[1]) * ($answer->y - $answerData[1]));
					if($distance <= ($answer->text + 5)) {
						$isAnswerCorrect = true;
						break;
					}
				}						
			}
			break;
	}
	
	$quizz['questionList'][$currentQuestionIndex]['answer'] = $isAnswerCorrect;
	$player->current_quizz = json_encode($quizz);
	$player->update();
	RequestTool::redirect('/play.php');
}

$smarty->assign('currentQuestionNumber', $currentQuestionIndex + 1);
$smarty->assign('totalQuestionNumber', count($quizz['questionList']));
$smarty->assign('percentCompleted', floor(($currentQuestionIndex) / count($quizz['questionList']) * 100));
$smarty->assign('question', $currentQuestion->toArray(Question::FORMAT_GAME));
$smarty->assign('page', 'game');
$smarty->display('layout.tpl');

require_once 'init/end.inc.php';
?>