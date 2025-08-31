<?php namespace skyblock\challenges\levels\level11;

use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\block\BlockLegacyIds as Block;

use skyblock\challenges\Challenge;

class BuyWhiteStainedGlassChallenge extends Challenge{

	public function onEvent(Event $event, Player $player) : bool{
		if(!$this->isCompleted()){
			$item = $event->getShopItem()->getItem();
			if($item->getId() === Block::STAINED_GLASS && $item->getMeta() == 0){
				$count = $event->getCount();
				$this->progress["bought"]["progress"] += $count;
				if($this->progress["bought"]["progress"] >= 16){
					$this->progress["bought"]["progress"] = 16;
					$this->onCompleted($player);
				}
				return true;
			}
		}
		return false;
	}

}