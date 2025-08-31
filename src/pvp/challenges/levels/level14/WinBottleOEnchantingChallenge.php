<?php namespace skyblock\challenges\levels\level14;

use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\item\{
	Item,
	ItemIds
};

use skyblock\challenges\Challenge;

class WinBottleOEnchantingChallenge extends Challenge{

	public function onEvent(Event $event, Player $player) : bool{
		if(!$this->isCompleted()){
			$prize = $event->getPrize()->getPrize();
			if($prize instanceof Item){
				if($prize->getId() === ItemIds::BOTTLE_O_ENCHANTING){
					$count = $prize->getCount();
					$this->progress["won"]["progress"] += $count;
					if($this->progress["won"]["progress"] >= 16){
						$this->progress["won"]["progress"] = 16;
						$this->onCompleted($player);
					}
					return true;
				}
			}
		}
		return false;
	}

}