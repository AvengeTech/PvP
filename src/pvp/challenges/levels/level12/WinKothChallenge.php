<?php namespace skyblock\challenges\levels\level12;

use pocketmine\event\Event;
use pocketmine\player\Player;

use skyblock\challenges\Challenge;

class WinKothChallenge extends Challenge{

	public function onEvent(Event $event, Player $player) : bool{
		if(!$this->isCompleted()){
			$this->onCompleted($player);
			return true;
		}
		return false;
	}

}