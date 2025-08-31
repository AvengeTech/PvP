<?php namespace skyblock\challenges\levels\level3;

use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\block\BlockLegacyIds as Block;

use skyblock\challenges\Challenge;

class CraftFenceChallenge extends Challenge{

	public function onEvent(Event $event, Player $player) : bool{
		if(!$this->isCompleted()){
			foreach($event->getOutputs() as $output){
				if($output->getId() === Block::FENCE){
					$count = $output->getCount();
					$this->progress["fences"]["progress"] += $count;
					if($this->progress["fences"]["progress"] >= 20){
						$this->progress["fences"]["progress"] = 20;
						$this->onCompleted($player);
					}
					return true;
				}
			}
		}
		return false;
	}

}