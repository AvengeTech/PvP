<?php namespace skyblock\challenges\levels\level3;

use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\block\BlockLegacyIds as Block;

use skyblock\challenges\Challenge;

class CraftTorchChallenge extends Challenge{

	public function onEvent(Event $event, Player $player) : bool{
		if(!$this->isCompleted()){
			foreach($event->getOutputs() as $output){
				if($output->getId() === Block::TORCH){
					$count = $output->getCount();
					$this->progress["torches"]["progress"] += $count;
					if($this->progress["torches"]["progress"] >= 32){
						$this->progress["torches"]["progress"] = 32;
						$this->onCompleted($player);
					}
					return true;
				}
			}
		}
		return false;
	}

}