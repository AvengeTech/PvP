<?php namespace skyblock\challenges\levels\level4;

use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\item\ItemIds as Item;

use skyblock\challenges\Challenge;

class CraftBreadChallenge extends Challenge{

	public function onEvent(Event $event, Player $player) : bool{
		if(!$this->isCompleted()){
			foreach($event->getOutputs() as $output){
				if($output->getId() === Item::BREAD){
					$count = $output->getCount();
					$this->progress["bread"]["progress"] += $count;
					if($this->progress["bread"]["progress"] >= 10){
						$this->progress["bread"]["progress"] = 10;
						$this->onCompleted($player);
					}
					return true;
				}
			}
		}
		return false;
	}

}