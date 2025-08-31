<?php namespace skyblock\challenges\levels\level11;

use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\item\ItemIds as Item;

use skyblock\challenges\Challenge;

class LevelUpChallenge extends Challenge{

	public function onEvent(Event $event, Player $player) : bool{
		if(!$this->isCompleted()){
			$newlevel = $event->getNewLevel();
			if($newlevel !== null){
				if($newlevel != $this->progress["level"]["progress"]){
					$this->progress["level"]["progress"] = $newlevel;
					if($newlevel > 20){
						$this->progress["level"]["progress"] = 20;
						$this->onCompleted($player);
					}
					return true;
				}
			}
		}
		return false;
	}

}