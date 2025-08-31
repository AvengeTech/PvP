<?php namespace skyblock\challenges\levels\level2;

use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\item\ItemIds as Item;

use skyblock\challenges\Challenge;

class SellCarrotsChallenge extends Challenge{

	public function onEvent(Event $event, Player $player) : bool{
		if(!$this->isCompleted()){
			$shopitem = $event->getShopItem();
			$item = $shopitem->getItem();
			$count = $event->getCount();
			if($item->getId() == Item::CARROT){
				$this->progress["carrots"]["progress"] += $count;
				if($this->progress["carrots"]["progress"] >= 16){
					$this->progress["carrots"]["progress"] = 16;
					$this->onCompleted($player);
				}
				return true;
			}
		}
		return false;
	}

}