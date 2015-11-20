<?php
/**
 * Answer element class
 */
class Answer extends Osteo
{
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
			case Osteo::FORMAT_ADMIN:
				$array['id'] = $this->id;
				$array['text'] = $this->text;
				$array['isCorrect'] = $this->is_correct;
				$array['x'] = $this->x;
				$array['y'] = $this->y;
				break;
		}

		return $array;
	}
}
?>