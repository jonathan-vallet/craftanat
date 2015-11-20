<?php
/**
 * Authentication class
 */
class AuthenticationTool extends SingletonModel
{
	const SESSION_PLAYER_ID = 'player_id';

	static protected $connectedPlayer;
	static protected $selectedMazode;

	/**
	 * Unique instance (singleton) retrieval method
	 * @return AuthenticationTool The AuthenticationTool unique instance
	 */
	public static function getInstance($param=null)
	{
		return self::getClassInstance(get_class(), $param);
	}

	/**
	 * Checks if player is connected
	 * @return boolean If the player is connected or not
	 */
	public function isPlayerConnected()
	{
		return SessionTool::getInstance()->issetParameter(AuthenticationTool::SESSION_PLAYER_ID);
	}	
	
	/**
	 * Makes sure that a user is logged, throws a PlayerNotConnectedException otherwise.
	 */
	public function assertPlayerConnected()
	{
		// Player is not logged
		if (!$this->isPlayerConnected())
			throw new PlayerNotConnectedException('Player is not connected');
	}
	
	/**
	 * Gets connected player
	 * @param boolean $assertConnected Specifies if player has to be connected (throw excpeptoin if not 
	 * @return Player The logged Player
	 */
	public function getConnectedPlayer($assertConnected=true)
	{
		if (!isset(AuthenticationTool::$connectedPlayer))
		{
			if ($assertConnected)
				AuthenticationTool::getInstance()->assertPlayerConnected();

			AuthenticationTool::$connectedPlayer = OsteoFactory::getElement('Player', SessionTool::getInstance()->getParameter(AuthenticationTool::SESSION_PLAYER_ID));
		}

		return AuthenticationTool::$connectedPlayer;
	}
	
	/**
	 * Connects player id in param to the game
	 * @param int $playerId the player id
	 */
	public function setConnectedPlayer($playerId)
	{
		SessionTool::getInstance()->setParameter(AuthenticationTool::SESSION_PLAYER_ID, $playerId);
		//LoginTracking::addTracking($playerId);
	}

	/**
	 * Logouts layer
	 */
	public function logout()
	{
		if(!$this->isPlayerConnected())
			return;

		//LoginTracking::addLogoutTracking(AuthenticationTool::SESSION_PLAYER_ID);
		SessionTool::getInstance()->emptySession();
	}
	
	public static function getLoginErrorMessage()
	{
		return TranslationTool::getInstance()->translate('Login ou mot de passe incorrect');
	}
}
?>
