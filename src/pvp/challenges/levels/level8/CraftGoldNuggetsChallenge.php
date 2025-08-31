<?php namespace skyblock\challenges\levels\level8;

use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\item\ItemIds as Item;

use skyblock\challenges\Challenge;

class CraftGoldNuggetsChallenge extends Challenge{

	public function onEvent(Event $event, Player $player) : bool{
		if(!$this->isCompleted()){
			foreach($event->getOutputs() as $output){
				if($output->getId() === Item::GOLD_NUGGET){
					$count = $output->getCount();
					$this->progress["nuggets"]["progress"] += $count;
					if($this->progress["nuggets"]["progress"] >= 18){
						$this->progress["nuggets"]["progress"] = 18;
						$this->onCompleted($player);
					}
					return true;
				}
			}
		}
		return false;
	}

}