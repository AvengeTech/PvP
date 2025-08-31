<?php namespace skyblock\challenges\levels\level4;

use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\block\BlockLegacyIds as Block;

use skyblock\challenges\Challenge;

class CraftStoneBricksChallenge extends Challenge{

	public function onEvent(Event $event, Player $player) : bool{
		if(!$this->isCompleted()){
			foreach($event->getOutputs() as $output){
				if($output->getId() === Block::STONE_BRICKS){
					$count = $output->getCount();
					$this->progress["bricks"]["progress"] += $count;
					if($this->progress["bricks"]["progress"] >= 64){
						$this->progress["bricks"]["progress"] = 64;
						$this->onCompleted($player);
					}
					return true;
				}
			}
		}
		return false;
	}

}