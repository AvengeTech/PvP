<?php namespace skyblock\challenges\levels\level13;

use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\block\BlockLegacyIds as Block;

use skyblock\challenges\Challenge;

class BuyPurpurQuartzStoneBrickChallenge extends Challenge{

	public function onEvent(Event $event, Player $player) : bool{
		if(!$this->isCompleted()){
			$item = $event->getShopItem()->getItem();
			$count = $event->getCount();
			if($item->getId() == Block::PURPUR_BLOCK){
				if($this->progress["purpur"]["progress"] == 20){
					return false;
				}
				$this->progress["purpur"]["progress"] += $count;
				if($this->progress["purpur"]["progress"] >= 20){
					$this->progress["purpur"]["progress"] = 20;
					if(
						$this->progress["quartz"]["progress"] == 20 &&
						$this->progress["stone"]["progress"] == 20
					){
						$this->onCompleted($player);
					}
				}
				return true;
			}
			if($item->getId() == Block::QUARTZ_BLOCK){
				if($this->progress["quartz"]["progress"] == 20){
					return false;
				}
				$this->progress["quartz"]["progress"] += $count;
				if($this->progress["quartz"]["progress"] >= 20){
					$this->progress["quartz"]["progress"] = 20;
					if(
						$this->progress["purpur"]["progress"] == 20 &&
						$this->progress["stone"]["progress"] == 20
					){
						$this->onCompleted($player);
					}
				}
				return true;
			}
			if($item->getId() == Block::STONE_BRICK){
				if($this->progress["stone"]["progress"] == 20){
					return false;
				}
				$this->progress["stone"]["progress"] += $count;
				if($this->progress["stone"]["progress"] >= 20){
					$this->progress["stone"]["progress"] = 20;
					if(
						$this->progress["purpur"]["progress"] == 20 &&
						$this->progress["quartz"]["progress"] == 20
					){
						$this->onCompleted($player);
					}
				}
				return true;
			}
		}
		return false;
	}

}