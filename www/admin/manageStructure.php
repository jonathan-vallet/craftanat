<?php
require_once '../../init/initAdmin.inc.php';
$isEditMode = false;

$structureId = RequestTool::getParameter('structureId', RequestTool::PARAM_TYPE_UNSIGNED_INT, false);
$errorList = array();

if($_SERVER['REQUEST_METHOD'] === 'POST') {
	try {
		$structureName = RequestTool::getParameter('name', RequestTool::PARAM_TYPE_FREE, true, null, RequestTool::METHOD_POST);
	} catch(ParameterException $e) {
		$errorList[] = 'Nom incorrect';
	}
	try {
		$structureCategoryId = RequestTool::getParameter('structureCategory', RequestTool::PARAM_TYPE_UNSIGNED_INT, true, null, RequestTool::METHOD_POST);
	} catch(ParameterException $e) {
		$errorList[] = 'Type de structure incorrecte';
	}
	try {
		$componentQuantity = RequestTool::getParameter('componentQuantity', RequestTool::PARAM_TYPE_UNSIGNED_INT, true, null, RequestTool::METHOD_POST);
	} catch(ParameterException $e) {
		$errorList[] = 'Nombre de composants incorrect';
	}
	
	$questionNumber = RequestTool::getParameter('questionNumber', RequestTool::PARAM_TYPE_UNSIGNED_INT, false, 1, RequestTool::METHOD_POST);
	$structureOrder = RequestTool::getParameter('order', RequestTool::PARAM_TYPE_UNSIGNED_INT, false, 1, RequestTool::METHOD_POST);
	$requiredStructureList = RequestTool::getParameter('requiredStructure', RequestTool::PARAM_TYPE_ARRAY, false, array(), RequestTool::METHOD_POST);
	
	if(empty($errorList)) {
		$structure = $structureId !== null ? OsteoFactory::getElement('Structure', $structureId) : new Structure();
		$structure->name =  $structureName;
		$structure->order =  $structureOrder;
		$structure->question_number =  $questionNumber;
		$structure->component_quantity =  $componentQuantity;
		$structure->structure_category_id =  $structureCategoryId;
		if($structureId === null) {
			$structure->add();
		} else {
			$structure->update();
			OsteoFactory::deleteElementList('StructureRequiredStructure', 'structure_id=' . $structureId);
		}

		$requiredStructureList = array_unique($requiredStructureList);
		foreach($requiredStructureList as $requiredStructureId) {
			try {
				$requiredStructure = OsteoFactory::getElement('Structure', $requiredStructureId);
			} catch(ElementDoesNotExistException $e) {
				$errorList[] = 'Strcuture de pré-requis incorrecte';
			}
			
			$structureRequiredStructure = new StructureRequiredStructure();
			$structureRequiredStructure->structure_id = $structure->id;
			$structureRequiredStructure->required_structure_id = $requiredStructureId;
			$structureRequiredStructure->add();
		}

		if(isset($_FILES['image'])) {
			$imageFile = $_FILES['image'];
			$availableExtensionList = array( 'jpg' , 'jpeg' , 'gif' , 'png' );
			$uploadedFileExtension = strtolower(substr(strrchr($imageFile['name'], '.') ,1));
			if (in_array($uploadedFileExtension, $availableExtensionList)) { 
				$destinationFile = 'structures/structure_' . $structure->id . '.' . $uploadedFileExtension;
				$isUploaded = move_uploaded_file($imageFile['tmp_name'], IMAGE_PATH_FROM_ROOT . $destinationFile);
				if($isUploaded) {
					$structure->image = IMAGE_PATH . $destinationFile;
					$structure->update();
				}
			}

			if(empty($errorList)) {
				RequestTool::redirect('/admin/structureList.php');
			}
		}
	}
} else if($structureId !== null) {
	$structure = OsteoFactory::getElement('Structure', $structureId);
}

if(isset($structure)) {
	$smarty->assign('structure', $structure->toArray(Osteo::FORMAT_ADMIN));
}

$structureCategoryList = OsteoFactory::getElementList('StructureCategory');
$structureList = OsteoFactory::getElementList('Structure');

$smarty->assign('isEditMode', $structureId !== null);
$smarty->assign('submitText', $structureId !== null ? 'Mettre à jour' : 'Ajouter');
$smarty->assign('errorList', $errorList);
$smarty->assign('formAction', '/admin/manageStructure.php' . ($structureId !== null ? '?structureId=' . $structureId : ''));

$smarty->assign('javascript', array('admin/manageStructure'));
$smarty->assign('structureCategoryList', Osteo::elementListToArray($structureCategoryList, Osteo::FORMAT_ADMIN));
$smarty->assign('structureList', Osteo::elementListToArray($structureList, Structure::FORMAT_SIMPLE_LIST));
$smarty->assign('page', 'admin/manageStructure');

$smarty->display('admin/layout.tpl');

require_once 'init/end.inc.php';
?>