<?php namespace skyblock\challenges\levels\level5;

use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\item\ItemIds as Item;

use skyblock\challenges\Challenge;

class CollectCoalChallenge extends Challenge{

	public function onEvent(Event $event, Player $player) : bool{
		if(!$this->isCompleted()){
			$item = $event->getItemEntity()->getItem();
			if($item->getId() === Item::COAL){
				$count = $item->getCount();
				$this->progress["collected"]["progress"] += $count;
				if($this->progress["collected"]["progress"] >= 64){
					$this->progress["collected"]["progress"] = 64;
					$this->onCompleted($player);
				}
				return true;
			}
		}
		return false;
	}

}