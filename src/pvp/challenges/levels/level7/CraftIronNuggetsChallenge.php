<?php namespace skyblock\challenges\levels\level7;

use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\item\ItemIds as Item;

use skyblock\challenges\Challenge;

class CraftIronNuggetsChallenge extends Challenge{

	public function onEvent(Event $event, Player $player) : bool{
		if(!$this->isCompleted()){
			foreach($event->getOutputs() as $output){
				if($output->getId() === Item::IRON_NUGGET){
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