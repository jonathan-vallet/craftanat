<?php
/**
 * Answer element class
 */
class StructureRequiredStructure extends Osteo
{
	public function & toArray($format)
	{
		$array = array();
		switch($format)
		{
			case Osteo::FORMAT_ADMIN:
				$array['id'] = $this->id;
				$array['name'] = $this->name;
				$requiredStructureList = $this->getStructureRequiredStructureList();
				break;
		}

		return $array;
	}
}
?>