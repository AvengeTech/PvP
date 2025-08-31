<?php namespace skyblock\challenges\levels\level7;

use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\item\{
	Item, ItemIds
};
use pocketmine\block\BlockLegacyIds as Block;

use skyblock\challenges\Challenge;

class WinGlowstoneChallenge extends Challenge{

	public function onEvent(Event $event, Player $player) : bool{
		if(!$this->isCompleted()){
			$prize = $event->getPrize()->getPrize();
			if($prize instanceof Item){
				if($prize->getId() === Block::GLOWSTONE){
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