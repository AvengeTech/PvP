<?php namespace skyblock\challenges\levels\level1;

use pocketmine\event\Event;
use pocketmine\player\Player;

use skyblock\challenges\Challenge;

class IslandExpandChallenge extends Challenge{

	public function onEvent(Event $event, Player $player) : bool{
		if(!$this->isCompleted()){
			$this->onCompleted($player);
			return true;
		}
		return false;
	}

}