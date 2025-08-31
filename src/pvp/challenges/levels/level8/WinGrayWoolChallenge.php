<?php namespace skyblock\challenges\levels\level8;

use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\item\{
	Item,
	ItemIds
};
use pocketmine\block\{Block, BlockLegacyIds, utils\DyeColor};

use skyblock\challenges\Challenge;

class WinGrayWoolChallenge extends Challenge{

	public function onEvent(Event $event, Player $player) : bool{
		if(!$this->isCompleted()){
			$prize = $event->getPrize()->getPrize();
			if($prize instanceof Item){
				if($prize->getId() === BlockLegacyIds::WOOL && $prize->getMeta() == 7){
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