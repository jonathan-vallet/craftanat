<?php
// ends transaction
DatabaseFactory::commit();

if(isset($isPlayerConnected) && $isPlayerConnected) {
	$player = $authenticationInstance->getConnectedPlayer();
	$player->is_new = 0;
	$player->update();
}
?>