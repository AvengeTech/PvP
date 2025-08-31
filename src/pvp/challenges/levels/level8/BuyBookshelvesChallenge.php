<?php namespace skyblock\challenges\levels\level8;

use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\block\BlockLegacyIds as Block;

use skyblock\challenges\Challenge;

class BuyBookshelvesChallenge extends Challenge{

	public function onEvent(Event $event, Player $player) : bool{
		if(!$this->isCompleted()){
			$item = $event->getShopItem()->getItem();
			if($item->getId() === Block::BOOKSHELF){
				$count = $event->getCount();
				$this->progress["bookshelves"]["progress"] += $count;
				if($this->progress["bookshelves"]["progress"] >= 16){
					$this->progress["bookshelves"]["progress"] = 16;
					$this->onCompleted($player);
				}
				$this->onCompleted($player);
				return true;
			}
		}
		return false;
	}

}