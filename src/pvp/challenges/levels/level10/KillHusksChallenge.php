<?php namespace skyblock\challenges\levels\level10;

use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\block\BlockLegacyIds as Block;

use skyblock\challenges\Challenge;

class KillHusksChallenge extends Challenge{

	public function onEvent(Event $event, Player $player) : bool{
		if(!$this->isCompleted()){
			$mob = $event->getMob();
			if($mob->getName() == "Husk"){
				$this->progress["husks"]["progress"]++;
				if($this->progress["husks"]["progress"] >= 20){
					$this->onCompleted($player);
				}
				return true;
			}
		}
		return false;
	}

}