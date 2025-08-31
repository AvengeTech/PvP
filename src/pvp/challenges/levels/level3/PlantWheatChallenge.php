<?php namespace skyblock\challenges\levels\level3;

use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\block\BlockLegacyIds as Block;

use skyblock\challenges\Challenge;

class PlantWheatChallenge extends Challenge{

	public function onEvent(Event $event, Player $player) : bool{
		if(!$this->isCompleted()){
			$block = $event->getBlock();
			if($block->getId() == Block::WHEAT_BLOCK){
				$this->progress["planted"]["progress"]++;
				if($this->progress["planted"]["progress"] >= 10){
					$this->onCompleted($player);
				}
				return true;
			}
		}
		return false;
	}

}