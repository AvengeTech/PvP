<?php namespace skyblock\challenges\levels\level12;

use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\block\BlockLegacyIds as Block;

use skyblock\challenges\Challenge;

class KillWitherSkeletonsChallenge extends Challenge{

	public function onEvent(Event $event, Player $player) : bool{
		if(!$this->isCompleted()){
			$mob = $event->getMob();
			if($mob->getName() == "Wither Skeleton"){
				$this->progress["skeletons"]["progress"]++;
				if($this->progress["skeletons"]["progress"] >= 10){
					$this->onCompleted($player);
				}
				return true;
			}
		}
		return false;
	}

}