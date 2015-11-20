<?php
class Player extends Osteo 
{
	const FORMAT_PAGE = 'page';
	
	public function add() {
		// Sets encrypted password
		$this->salt = rand(10000, 99999);
		$this->password = $this->encryptPassword($this->password);

		parent::add();

		// Sets first game
		$playerStructure = new PlayerHasStructure();
		$playerStructure->player_id = $this->id;
		$playerStructure->structure_id = OsteoFactory::getElement('Structure', null, 'structure_category_id=7')->id;
		$playerStructure->add();
	}
	
	public function encryptPassword($password) {
		return sha1($this->salt . $password . '0sThé0');
	}
	
	public function & toArray($format)
	{
		$array = array();
		switch($format)
		{
			case Player::FORMAT_PAGE:
				$array['id'] = $this->id;
				$array['name'] = $this->name;
				$array['isNew'] = $this->is_new;
				$array['isAdmin'] = $this->is_admin;
				break;
		}

		return $array;
	}
}
?>