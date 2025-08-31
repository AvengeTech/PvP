<?php namespace skyblock\challenges\levels\level12;

use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\block\BlockLegacyIds as Block;

use skyblock\challenges\Challenge;

class BuyPurpurBlocksChallenge extends Challenge{

	public function onEvent(Event $event, Player $player) : bool{
		if(!$this->isCompleted()){
			$item = $event->getShopItem()->getItem();
			if($item->getId() === Block::PURPUR_BLOCK){
				$count = $event->getCount();
				$this->progress["bought"]["progress"] += $count;
				if($this->progress["bought"]["progress"] >= 20){
					$this->progress["bought"]["progress"] = 20;
					$this->onCompleted($player);
				}
				return true;
			}
		}
		return false;
	}

}