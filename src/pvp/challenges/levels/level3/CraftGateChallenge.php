<?php namespace skyblock\challenges\levels\level3;

use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\block\BlockLegacyIds as Block;

use skyblock\challenges\Challenge;

class CraftGateChallenge extends Challenge{

	public function onEvent(Event $event, Player $player) : bool{
		if(!$this->isCompleted()){
			foreach($event->getOutputs() as $output){
				if($output->getId() === Block::FENCE_GATE){
					$count = $output->getCount();
					$this->progress["gates"]["progress"] += $count;
					if($this->progress["gates"]["progress"] >= 4){
						$this->progress["gates"]["progress"] = 4;
						$this->onCompleted($player);
					}
					return true;
				}
			}
		}
		return false;
	}

}