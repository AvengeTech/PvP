<?php namespace skyblock\challenges\levels\level10;

use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\item\{
	Item,
	ItemIds
};

use skyblock\challenges\Challenge;

class CollectFishChallenge extends Challenge{

	public function onEvent(Event $event, Player $player) : bool{
		if(!$this->isCompleted()){
			$item = $event->getFishingFind()->getItem();
			if($item instanceof Item){
				$count = $item->getCount();
				if(
					$item->getId() == ItemIds::RAW_FISH ||
					$item->getId() == ItemIds::RAW_SALMON ||
					$item->getId() == ItemIds::CLOWNFISH ||
					$item->getId() == ItemIds::PUFFERFISH
				){
					$this->progress["fish"]["progress"] += $count;
					if($this->progress["fish"]["progress"] >= 16){
						$this->progress["fish"]["progress"] = 16;
						$this->onCompleted($player);
					}
					return true;
				}
			}
		}
		return false;
	}

}