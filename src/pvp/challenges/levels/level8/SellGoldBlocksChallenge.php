<?php namespace skyblock\challenges\levels\level8;

use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\block\BlockLegacyIds as Block;

use skyblock\challenges\Challenge;

class SellGoldBlocksChallenge extends Challenge{

	public function onEvent(Event $event, Player $player) : bool{
		if(!$this->isCompleted()){
			$shopitem = $event->getShopItem();
			$item = $shopitem->getItem();
			$count = $event->getCount();
			if($item->getId() == Block::GOLD_BLOCK){
				$this->progress["blocks"]["progress"] += $count;
				if($this->progress["blocks"]["progress"] >= 16){
					$this->progress["blocks"]["progress"] = 16;
					$this->onCompleted($player);
				}
				return true;
			}
		}
		return false;
	}

}