<?php namespace skyblock\challenges\levels\level9;

use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\block\BlockLegacyIds as Block;

use skyblock\challenges\Challenge;

class KillSkeletonsChallenge extends Challenge{

	public function onEvent(Event $event, Player $player) : bool{
		if(!$this->isCompleted()){
			$mob = $event->getMob();
			if($mob->getName() == "Skeleton"){
				$this->progress["skeletons"]["progress"]++;
				if($this->progress["skeletons"]["progress"] >= 15){
					$this->onCompleted($player);
				}
				return true;
			}
		}
		return false;
	}

}