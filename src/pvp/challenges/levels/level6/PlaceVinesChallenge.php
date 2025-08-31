<?php namespace skyblock\challenges\levels\level6;

use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\block\BlockLegacyIds as Block;

use skyblock\challenges\Challenge;

class PlaceVinesChallenge extends Challenge{

	public function onEvent(Event $event, Player $player) : bool{
		if(!$this->isCompleted()){
			$block = $event->getBlock();
			if($block->getId() == Block::VINES){
				$this->progress["placed"]["progress"]++;
				if($this->progress["placed"]["progress"] >= 8){
					$this->onCompleted($player);
				}
				return true;
			}
		}
		return false;
	}

}