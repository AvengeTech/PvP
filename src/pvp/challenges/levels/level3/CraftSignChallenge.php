<?php namespace skyblock\challenges\levels\level3;

use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\item\ItemIds as Item;

use skyblock\challenges\Challenge;

class CraftSignChallenge extends Challenge{

	public function onEvent(Event $event, Player $player) : bool{
		if(!$this->isCompleted()){
			foreach($event->getOutputs() as $output){
				if($output->getId() === Item::SIGN){
					$count = $output->getCount();
					$this->progress["signs"]["progress"] += $count;
					if($this->progress["signs"]["progress"] >= 10){
						$this->progress["signs"]["progress"] = 10;
						$this->onCompleted($player);
					}
					return true;
				}
			}
		}
		return false;
	}

}