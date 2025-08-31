<?php namespace skyblock\challenges\levels\level10;

use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\block\BlockLegacyIds as Block;

use skyblock\challenges\Challenge;

class BuyEndStoneChallenge extends Challenge{

	public function onEvent(Event $event, Player $player) : bool{
		if(!$this->isCompleted()){
			$item = $event->getShopItem()->getItem();
			if($item->getId() === Block::END_STONE){
				$count = $event->getCount();
				$this->progress["blocks"]["progress"] += $count;
				if($this->progress["blocks"]["progress"] >= 32){
					$this->progress["blocks"]["progress"] = 32;
					$this->onCompleted($player);
				}
				$this->onCompleted($player);
				return true;
			}
		}
		return false;
	}

}