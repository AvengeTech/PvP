<?php namespace skyblock\challenges\levels\level1;

use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\block\BlockLegacyIds as Block;

use skyblock\challenges\Challenge;

class TrapCraftChallenge extends Challenge{

	public function onEvent(Event $event, Player $player) : bool{
		if(!$this->isCompleted()){
			foreach($event->getOutputs() as $output){
				if($output->getId() === Block::TRAPDOOR){
					$count = $output->getCount();
					$this->progress["trapdoors"]["progress"] += $count;
					if($this->progress["trapdoors"]["progress"] >= 10){
						$this->progress["trapdoors"]["progress"] = 10;
						$this->onCompleted($player);
					}
					return true;
				}
			}
		}
		return false;
	}

}