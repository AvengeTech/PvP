<?php namespace skyblock\challenges\levels\level13;

use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\block\BlockLegacyIds as Block;

use skyblock\challenges\Challenge;

class BuyBlackWoolConcreteChallenge extends Challenge{

	public function onEvent(Event $event, Player $player) : bool{
		if(!$this->isCompleted()){
			$item = $event->getShopItem()->getItem();
			$count = $event->getCount();
			if($item->getId() == Block::WOOL && $item->getMeta() == 15){
				if($this->progress["wool"]["progress"] == 16){
					return false;
				}
				$this->progress["wool"]["progress"] += $count;
				if($this->progress["wool"]["progress"] >= 16){
					$this->progress["wool"]["progress"] = 16;
					if($this->progress["concrete"]["progress"] == 16){
						$this->onCompleted($player);
					}
				}
				return true;
			}
			if($item->getId() == Block::CONCRETE && $item->getMeta() == 15){
				if($this->progress["concrete"]["progress"] == 16){
					return false;
				}
				$this->progress["concrete"]["progress"] += $count;
				if($this->progress["concrete"]["progress"] >= 16){
					$this->progress["concrete"]["progress"] = 16;
					if($this->progress["wool"]["progress"] == 16){
						$this->onCompleted($player);
					}
				}
				return true;
			}
		}
		return false;
	}

}