<?php namespace skyblock\challenges\levels\level8;

use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\block\BlockLegacyIds as Block;

use skyblock\challenges\Challenge;

class KillSpidersChallenge extends Challenge{

	public function onEvent(Event $event, Player $player) : bool{
		if(!$this->isCompleted()){
			$mob = $event->getMob();
			if($mob->getName() == "Spider"){
				$this->progress["spiders"]["progress"]++;
				if($this->progress["spiders"]["progress"] >= 20){
					$this->onCompleted($player);
				}
				return true;
			}
		}
		return false;
	}

}