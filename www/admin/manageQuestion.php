<?php
require_once '../../init/initAdmin.inc.php';
$isEditMode = false;

$questionId = RequestTool::getParameter('questionId', RequestTool::PARAM_TYPE_UNSIGNED_INT, false);
$errorList = array();
if($_SERVER['REQUEST_METHOD'] === 'POST') {
	try {
		$questionText = RequestTool::getParameter('question', RequestTool::PARAM_TYPE_FREE, true, null, RequestTool::METHOD_POST);
	} catch(ParameterException $e) {
		$errorList[] = 'Question incorrecte';
	}
	try {
		$structureId = RequestTool::getParameter('structure', RequestTool::PARAM_TYPE_UNSIGNED_INT, true, null, RequestTool::METHOD_POST);
	} catch(ParameterException $e) {
		$errorList[] = 'Structure incorrecte';
	}
	if(empty($errorList)) {
		$question = $questionId !== null ? OsteoFactory::getElement('Question', $questionId) : new Question();
		$question->text =  $questionText;
		$question->question_category_id = RequestTool::getParameter('questionCategoryId', RequestTool::PARAM_TYPE_UNSIGNED_INT, true, null, RequestTool::METHOD_POST);
		
		$question->is_in_craft_quizz = RequestTool::getParameter('isInCraftQuizz', RequestTool::PARAM_TYPE_BOOLEAN, false, 0, RequestTool::METHOD_POST);
		$question->structure_id = $structureId;
		if($questionId === null) {
			$question->add();
		} else {
			$question->update();
			OsteoFactory::deleteElementList('Answer', 'question_id=' . $questionId);
		}

		for($answerIndex = 1; $answerIndex <= 20; ++$answerIndex) {
			try {
				$answerText = RequestTool::getParameter('answer' . $answerIndex, RequestTool::PARAM_TYPE_FREE, false, null, RequestTool::METHOD_POST);
			} catch(ParameterException $e) {
				$errorList[] = 'Réponse ' . $answerIndex . ' incorrecte';
			}

			if(empty($errorList) && $answerText) {
				$answer = new Answer();
				if($question->question_category_id === 3) {
					$answerData = explode('/', RequestTool::getParameter('answerData' . $answerIndex, RequestTool::PARAM_TYPE_FREE, false, null, RequestTool::METHOD_POST));
					$answer->x = $answerData[0];
					$answer->y = $answerData[1];
					$answer->is_correct = $answerData[2];
					$answer->text =  $answerText;
				} else if($question->question_category_id === 4) {
					$imagePosition = explode('/', $answerText);
					$answer->x = $imagePosition[0];
					$answer->y = $imagePosition[1];
					$answer->text =  RequestTool::getParameter('answerRadius' . $answerIndex, RequestTool::PARAM_TYPE_UNSIGNED_INT, false, 0, RequestTool::METHOD_POST);
				} else {
					$answer->text =  $answerText;
					$answer->is_correct = RequestTool::getParameter('answer' . $answerIndex . 'Correct', RequestTool::PARAM_TYPE_BOOLEAN, false, 0, RequestTool::METHOD_POST);
					$answer->x = $answerIndex;
				}
				$answer->question_id = $question->id;
				$answer->add();
			} else {
				break;
			}
		}

		if(isset($_FILES['image'])) {
			$imageFile = $_FILES['image'];
			$availableExtensionList = array( 'jpg' , 'jpeg' , 'gif' , 'png' );
			$uploadedFileExtension = strtolower(substr(strrchr($imageFile['name'], '.') ,1));
			if (in_array($uploadedFileExtension, $availableExtensionList)) { 
				$destinationFile = 'questions/question_' . $question->id . '.' . $uploadedFileExtension;
				$isUploaded = move_uploaded_file($imageFile['tmp_name'], IMAGE_PATH_FROM_ROOT . $destinationFile);
				if($isUploaded) {
					$question->image = IMAGE_PATH . $destinationFile;
					$question->update();
				}
			}
		}
		
		if(empty($errorList)) {
			RequestTool::redirect('/admin/questionList.php');
		}
	}
} else if($questionId !== null) {
	$question = OsteoFactory::getElement('Question', $questionId);
}

if(isset($question)) {
	$smarty->assign('question', $question->toArray(Osteo::FORMAT_ADMIN));
}

$questionCategoryList = OsteoFactory::getElementList('QuestionCategory');
$strucutreList = OsteoFactory::getElementList('Structure');

$smarty->assign('isEditMode', $questionId !== null);
$smarty->assign('submitText', $questionId !== null ? 'Mettre à jour' : 'Ajouter');
$smarty->assign('errorList', $errorList);
$smarty->assign('formAction', '/admin/manageQuestion.php' . ($questionId !== null ? '?questionId=' . $questionId : ''));

$smarty->assign('javascript', array('admin/manageQuestion'));
$smarty->assign('css', array('manageElement'));
$smarty->assign('questionCategoryList', Osteo::elementListToArray($questionCategoryList, Osteo::FORMAT_ADMIN));
$smarty->assign('structureList', Osteo::elementListToArray($strucutreList, Osteo::FORMAT_ADMIN));
$smarty->assign('page', 'admin/manageQuestion');

$smarty->display('admin/layout.tpl');

require_once 'init/end.inc.php';
?>