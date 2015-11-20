<?php
/**
 * Question element class
 */
class Question extends Osteo
{
	const FORMAT_GAME = 'game';
	const FORMAT_RESULT = 'result';
	
	/**
	 * Gets an array representing current element
	 * @param string $format The format to use to convert element to array
	 * @return array The array
	 */
	public function & toArray($format, $data = null)
	{
		$array = array();
		switch($format)
		{
			case Osteo::FORMAT_ADMIN:
				$array['id'] = $this->id;
				$array['text'] = $this->text;
				$array['imageUrl'] = $this->image;
				$array['isInCraftQuizz'] = $this->is_in_craft_quizz;
				$structure = $this->getParentStructure();
				$array['structureId'] = $this->structure_id;
				$array['structureName'] = $structure->name;
				$questionCategory = $this->getParentQuestionCategory();
				$array['categoryId'] = $questionCategory->id;
				$array['categoryName'] = $questionCategory->name;
				$answerList = $this->getAnswerList();
				$array['answerList'] = Osteo::elementListToArray($answerList, Osteo::FORMAT_ADMIN);
				break;
			case Question::FORMAT_GAME:
				$array['id'] = $this->id;
				$array['text'] = $this->text;
				$array['imageUrl'] = $this->image;
				$array['categoryId'] = $this->question_category_id;
				$answerList = $this->getAnswerList();
				$array['answerList'] = Osteo::elementListToArray($answerList, Osteo::FORMAT_ADMIN);
				// Additional information for question type
				switch($this->question_category_id) {
					// QCM
					case 1:
						$correctQuestionNumber = 0;
						foreach($answerList as $answer) {
							if($answer->is_correct) {
								++$correctQuestionNumber;
							}
						}
						$array['correctQuestionNumber'] = $correctQuestionNumber;
						break;
					// Texte à trou
					case 2: 
						$array['text'] = preg_replace('/<champ([0-9])+>/', '<input type="text" name="answer$1" class="form-control" autocomplete="off" />', $this->text);
						break;
				}
				break;
			case Question::FORMAT_RESULT:
				$array['id'] = $this->id;
				$array['text'] = $this->text;
				$array['imageUrl'] = $this->image;
				$array['categoryId'] = $this->question_category_id;
				$answerList = $this->getAnswerList();
				$array['answerList'] = Osteo::elementListToArray($answerList, Osteo::FORMAT_ADMIN);

				$array['answerCorrect'] = $data;
				// Additional information for question type
				switch($this->question_category_id) {
					// QCM
					case 1:
						$correctQuestionNumber = 0;
						foreach($answerList as $answer) {
							if($answer->is_correct) {
								++$correctQuestionNumber;
							}
						}
						$array['correctQuestionNumber'] = $correctQuestionNumber;
						break;
					// Texte à trou
					case 2:
						$answerReplace = array();
						$answerMatch = array();
						$fieldNumber = 1;
						foreach($answerList as $index => $answer) {
							$answerReplace[] = '<span>' . $answer->text . '</span>';
							$answerMatch[] = '<champ' . ($fieldNumber++) . '>';
							echo '<champ' . ($index + 1) . '>';;
						}
						$array['text'] = str_replace($answerMatch, $answerReplace, $this->text);
						break;
				}
				break;
		}
		return $array;
	}
}
?>