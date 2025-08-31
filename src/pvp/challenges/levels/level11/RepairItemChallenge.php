<?php namespace skyblock\challenges\levels\level11;

use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\item\ItemIds as Item;

use skyblock\challenges\Challenge;

class RepairItemChallenge extends Challenge{

	public function onEvent(Event $event, Player $player) : bool{
		if(!$this->isCompleted()){
			$this->onCompleted($player);
			return true;
		}
		return false;
	}

}