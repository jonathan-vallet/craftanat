<?php
/**
 * Answer element class
 */
class Component extends Osteo
{
	const FORMAT_PLAYER_COMPONENT_LIST = 'component_list';
	
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
			case Component::FORMAT_PLAYER_COMPONENT_LIST:
            case Structure::FORMAT_PLAYER_STRUCTURE_LIST:
				$array['id'] = $this->id;
                $array['name'] = $this->name;
                $array['codeName'] = $this->code_name;
				$playerComponent = $this->getPlayerHasComponentList('player_id = ' . AuthenticationTool::getInstance()->getConnectedPlayer()->id);
				if(count($playerComponent) > 0) {
					$playerComponent = reset($playerComponent);
					$array['quantity'] = $playerComponent->quantity;
				} else {
					$array['quantity'] = 0;
				}
				break;
		}

		return $array;
	}
}
?>