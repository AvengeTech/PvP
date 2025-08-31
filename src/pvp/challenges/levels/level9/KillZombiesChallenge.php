<?php namespace skyblock\challenges\levels\level9;

use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\block\BlockLegacyIds as Block;

use skyblock\challenges\Challenge;

class KillZombiesChallenge extends Challenge{

	public function onEvent(Event $event, Player $player) : bool{
		if(!$this->isCompleted()){
			$mob = $event->getMob();
			if($mob->getName() == "Zombie"){
				$this->progress["zombies"]["progress"]++;
				if($this->progress["zombies"]["progress"] >= 20){
					$this->onCompleted($player);
				}
				return true;
			}
		}
		return false;
	}

}