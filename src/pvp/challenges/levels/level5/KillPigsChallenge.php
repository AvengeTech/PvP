<?php namespace skyblock\challenges\levels\level5;

use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\block\BlockLegacyIds as Block;

use skyblock\challenges\Challenge;

class KillPigsChallenge extends Challenge{

	public function onEvent(Event $event, Player $player) : bool{
		if(!$this->isCompleted()){
			$mob = $event->getMob();
			if($mob->getName() == "Pig"){
				$this->progress["pigs"]["progress"]++;
				if($this->progress["pigs"]["progress"] >= 10){
					$this->onCompleted($player);
				}
				return true;
			}
		}
		return false;
	}

}