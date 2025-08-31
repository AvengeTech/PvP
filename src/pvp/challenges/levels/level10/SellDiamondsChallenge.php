<?php namespace skyblock\challenges\levels\level10;

use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\item\ItemIds as Item;

use skyblock\challenges\Challenge;

class SellDiamondsChallenge extends Challenge{

	public function onEvent(Event $event, Player $player) : bool{
		if(!$this->isCompleted()){
			$shopitem = $event->getShopItem();
			$item = $shopitem->getItem();
			$count = $event->getCount();
			if($item->getId() == Item::DIAMOND){
				$this->progress["diamonds"]["progress"] += $count;
				if($this->progress["diamonds"]["progress"] >= 16){
					$this->progress["diamonds"]["progress"] = 16;
					$this->onCompleted($player);
				}
				return true;
			}
		}
		return false;
	}

}