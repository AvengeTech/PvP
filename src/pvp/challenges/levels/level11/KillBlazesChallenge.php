<?php namespace skyblock\challenges\levels\level11;

use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\block\BlockLegacyIds as Block;

use skyblock\challenges\Challenge;

class KillBlazesChallenge extends Challenge{

	public function onEvent(Event $event, Player $player) : bool{
		if(!$this->isCompleted()){
			$mob = $event->getMob();
			if($mob->getName() == "Blaze"){
				$this->progress["blazes"]["progress"]++;
				if($this->progress["blazes"]["progress"] >= 15){
					$this->onCompleted($player);
				}
				return true;
			}
		}
		return false;
	}

}