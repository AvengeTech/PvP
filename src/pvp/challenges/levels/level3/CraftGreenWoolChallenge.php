<?php namespace skyblock\challenges\levels\level3;

use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\block\BlockLegacyIds as Block;

use skyblock\challenges\Challenge;

class CraftGreenWoolChallenge extends Challenge{

	public function onEvent(Event $event, Player $player) : bool{
		if(!$this->isCompleted()){
			foreach($event->getOutputs() as $output){
				if($output->getId() === Block::WOOL && $output->getMeta() == 13){
					$count = $output->getCount();
					$this->progress["wool"]["progress"] += $count;
					if($this->progress["wool"]["progress"] >= 10){
						$this->progress["wool"]["progress"] = 10;
						$this->onCompleted($player);
					}
					return true;
				}
			}
		}
		return false;
	}

}