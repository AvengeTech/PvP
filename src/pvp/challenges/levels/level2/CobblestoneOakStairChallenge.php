<?php namespace skyblock\challenges\levels\level2;

use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\block\BlockLegacyIds as Block;

use skyblock\challenges\Challenge;

class CobblestoneOakStairChallenge extends Challenge{

	public function onEvent(Event $event, Player $player) : bool{
		if(!$this->isCompleted()){
			foreach($event->getOutputs() as $output){
				if($output->getId() == Block::COBBLESTONE_STAIRS){
					$count = $output->getCount();
					$this->progress["cobblestone"]["progress"] += $count;
					if($this->progress["cobblestone"]["progress"] >= 4){
						$this->progress["cobblestone"]["progress"] = 4;
						if($this->progress["wood"]["progress"] >= 4){
							$this->onCompleted($player);
						}
					}
					return true;
				}
				if($output->getId() == Block::WOODEN_STAIRS){
					$count = $output->getCount();
					$this->progress["wood"]["progress"] += $count;
					if($this->progress["wood"]["progress"] >= 4){
						$this->progress["wood"]["progress"] = 4;
						if($this->progress["cobblestone"]["progress"] >= 4){
							$this->onCompleted($player);
						}
					}
					return true;
				}
			}
		}
		return false;
	}

}