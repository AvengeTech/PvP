<?php namespace skyblock\challenges\levels\level4;

use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\item\ItemIds as Item;

use skyblock\challenges\Challenge;

class CraftLaddersChallenge extends Challenge{

	public function onEvent(Event $event, Player $player) : bool{
		if(!$this->isCompleted()){
			foreach($event->getOutputs() as $output){
				if($output->getId() === Item::LADDER){
					$count = $output->getCount();
					$this->progress["ladders"]["progress"] += $count;
					if($this->progress["ladders"]["progress"] >= 9){
						$this->progress["ladders"]["progress"] = 9;
						$this->onCompleted($player);
					}
					return true;
				}
			}
		}
		return false;
	}

}