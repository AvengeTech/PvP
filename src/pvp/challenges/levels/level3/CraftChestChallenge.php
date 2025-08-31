<?php namespace skyblock\challenges\levels\level3;

use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\block\BlockLegacyIds as Block;

use skyblock\challenges\Challenge;

class CraftChestChallenge extends Challenge{

	public function onEvent(Event $event, Player $player) : bool{
		if(!$this->isCompleted()){
			foreach($event->getOutputs() as $output){
				if($output->getId() === Block::CHEST){
					$count = $output->getCount();
					$this->progress["chests"]["progress"] += $count;
					if($this->progress["chests"]["progress"] >= 16){
						$this->progress["chests"]["progress"] = 16;
						$this->onCompleted($player);
					}
					return true;
				}
			}
		}
		return false;
	}

}