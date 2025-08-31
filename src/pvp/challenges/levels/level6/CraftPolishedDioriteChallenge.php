<?php namespace skyblock\challenges\levels\level6;

use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\block\BlockLegacyIds as Block;

use skyblock\challenges\Challenge;

class CraftPolishedDioriteChallenge extends Challenge{

	public function onEvent(Event $event, Player $player) : bool{
		if(!$this->isCompleted()){
			foreach($event->getOutputs() as $output){
				if($output->getId() === Block::STONE && $output->getMeta() == 4){
					$count = $output->getCount();
					$this->progress["diorite"]["progress"] += $count;
					if($this->progress["diorite"]["progress"] >= 64){
						$this->progress["diorite"]["progress"] = 64;
						$this->onCompleted($player);
					}
					return true;
				}
			}
		}
		return false;
	}

}