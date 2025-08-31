<?php namespace skyblock\challenges\levels\level9;

use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\item\{
	Item,
	ItemIds
};
use pocketmine\block\utils\DyeColor;

use skyblock\challenges\Challenge;

class WinLeatherBootsChallenge extends Challenge{

	public function onEvent(Event $event, Player $player) : bool{
		if(!$this->isCompleted()){
			$prize = $event->getPrize()->getPrize();
			if($prize instanceof Item){
				if($prize->getId() === ItemIds::LEATHER_BOOTS){
					$count = $prize->getCount();
					$this->progress["won"]["progress"] += $count;
					if($this->progress["won"]["progress"] >= 5){
						$this->progress["won"]["progress"] = 5;
						$this->onCompleted($player);
					}
					return true;
				}
			}
		}
		return false;
	}

}