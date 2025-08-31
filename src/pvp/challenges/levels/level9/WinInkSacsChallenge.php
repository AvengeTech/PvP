<?php namespace skyblock\challenges\levels\level9;

use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\item\{
	Item,
	ItemIds
};
use pocketmine\block\utils\DyeColor;

use skyblock\challenges\Challenge;

class WinInkSacsChallenge extends Challenge{

	public function onEvent(Event $event, Player $player) : bool{
		if(!$this->isCompleted()){
			$prize = $event->getPrize()->getPrize();
			if($prize instanceof Item){
				if($prize->getId() === ItemIds::DYE && $prize->getMeta() == 0){
					$count = $prize->getCount();
					$this->progress["won"]["progress"] += $count;
					if($this->progress["won"]["progress"] >= 32){
						$this->progress["won"]["progress"] = 32;
						$this->onCompleted($player);
					}
					return true;
				}
			}
		}
		return false;
	}

}