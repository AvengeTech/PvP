<?php namespace skyblock\challenges\levels\level2;

use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\block\BlockLegacyIds as Block;

use skyblock\challenges\Challenge;

class ButtonCraftChallenge extends Challenge{

	public function onEvent(Event $event, Player $player) : bool{
		if(!$this->isCompleted()){
			foreach($event->getOutputs() as $output){
				if($output->getId() === Block::STONE_BUTTON){
					$count = $output->getCount();
					$this->progress["buttons"]["progress"] += $count;
					if($this->progress["buttons"]["progress"] >= 10){
						$this->progress["buttons"]["progress"] = 10;
						$this->onCompleted($player);
					}
					return true;
				}
			}
		}
		return false;
	}

}