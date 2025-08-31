<?php namespace skyblock\challenges\levels\level11;

use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\item\ItemIds as Item;

use skyblock\challenges\Challenge;

class CollectEmeraldChallenge extends Challenge{

	public function onEvent(Event $event, Player $player) : bool{
		if(!$this->isCompleted()){
			$item = $event->getItemEntity()->getItem();
			if($item->getId() === Item::EMERALD){
				$count = $item->getCount();
				$this->progress["collected"]["progress"] += $count;
				if($this->progress["collected"]["progress"] >= 16){
					$this->progress["collected"]["progress"] = 16;
					$this->onCompleted($player);
				}
				return true;
			}
		}
		return false;
	}

}