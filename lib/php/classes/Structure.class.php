<?php
/**
 * Answer element class
 */
class Structure extends Osteo
{
	const FORMAT_PLAYER_STRUCTURE_LIST = 'player_structure_list';
	const FORMAT_SIMPLE_LIST = 'simple_list';
	
	/**
	 * Gets an array representing current element
	 * @param string $format The format to use to convert element to array
	 * @return array The array
	 */
	public function & toArray($format)
	{
		$array = array();
		switch($format)
		{
			case Structure::FORMAT_SIMPLE_LIST: 
				$array['id'] = $this->id;
				$array['name'] = $this->name;
				break;
			case Osteo::FORMAT_ADMIN:
				$array['id'] = $this->id;
				$array['name'] = $this->name;
				$array['questionNumber'] = $this->question_number;
				$array['totalQuestionNumber'] = count($this->getQuestionList());
				$array['craftQuestionNumber'] = count($this->getQuestionList('question_is_in_craft_quizz=1'));
				$category = $this->getParentStructureCategory();
				$array['categoryId'] = $category->id;
				$array['categoryName'] = $category->name;
				$array['componentName'] = $category->getParentComponent()->name;
				$array['order'] = $this->order;
				$array['componentQuantity'] = $this->component_quantity;
				$array['imageUrl'] = $this->image;
				$requiredStructureList = $this->getStructureRequiredStructureList();
				$array['requiredStructureList'] = array();
				$array['requiredStructureIdList'] = array();
				foreach($requiredStructureList as $requiredStructure) {
					$array['requiredStructureList'][] = $requiredStructure->getParentRequired_structure()->toArray(Structure::FORMAT_SIMPLE_LIST);
					$array['requiredStructureIdList'][] = $requiredStructure->required_structure_id;
				}
				break;
			case Structure::FORMAT_PLAYER_STRUCTURE_LIST:
				$array['id'] = $this->id;
				$array['name'] = $this->name;
				$array['order'] = $this->order;
				$array['image'] = $this->image;
				$playerStructure = $this->getPlayerHasStructureList('player_id = ' . AuthenticationTool::getInstance()->getConnectedPlayer()->id);
                $category = $this->getParentStructureCategory();
                $array['categoryId'] = $category->id;
                $array['categoryName'] = $category->name;
                $array['categoryCodeName'] = $category->code_name;
				$array['isCraftable'] = count($this->getQuestionList('question_is_in_craft_quizz=1')) > 0;
				$array['isPlayable'] = count($this->getQuestionList()) > 0;
                
                $array['componentQuantity'] = $this->component_quantity;
                $array['component'] = $category->getParentComponent()->toArray(Structure::FORMAT_PLAYER_STRUCTURE_LIST);
				if(count($playerStructure) > 0) {
					$playerStructure = reset($playerStructure);
					$array['isAvailable'] = true;
					$array['bestScore'] = $playerStructure->best_score;
					$array['isCrafted'] = $playerStructure->is_crafted;
					$array['hasPlayed'] = $playerStructure->best_time > 0;
				} else {
					$array['isAvailable'] = false;
					$array['isCrafted'] =  false;
					// Gets missing structures
				}
				if(!$array['isAvailable']) {
					$requiredStructureList = $this->getStructureRequiredStructureList();
					$array['requiredStructureList'] = array();
					$array['requiredStructureIdList'] = array();
					foreach($requiredStructureList as $requiredStructure) {
						$array['requiredStructureList'][] = $requiredStructure->getParentRequired_structure()->toArray(Structure::FORMAT_SIMPLE_LIST);
						$array['requiredStructureIdList'][] = $requiredStructure->required_structure_id;
					}
				}
								
				break;
		}
		return $array;
	}
}
?>