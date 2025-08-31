<?php namespace skyblock\challenges\levels\level4;

use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\item\ItemIds as Item;

use skyblock\challenges\Challenge;

class CraftPaintingsChallenge extends Challenge{

	public function onEvent(Event $event, Player $player) : bool{
		if(!$this->isCompleted()){
			foreach($event->getOutputs() as $output){
				if($output->getId() === Item::PAINTING){
					$count = $output->getCount();
					$this->progress["paintings"]["progress"] += $count;
					if($this->progress["paintings"]["progress"] >= 5){
						$this->progress["paintings"]["progress"] = 5;
						$this->onCompleted($player);
					}
					return true;
				}
			}
		}
		return false;
	}

}