<?php
/**
 * QuestionCategory element class
 */
class QuestionCategory extends Osteo
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
				$array['name'] = $this->name;
				break;
		}

		return $array;
	}
}
?>