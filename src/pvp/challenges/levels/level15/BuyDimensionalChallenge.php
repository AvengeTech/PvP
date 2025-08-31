<?php namespace skyblock\challenges\levels\level15;

use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\block\BlockLegacyIds as Block;

use skyblock\challenges\Challenge;

class BuyDimensionalChallenge extends Challenge{

	public function onEvent(Event $event, Player $player) : bool{
		if(!$this->isCompleted()){
			$item = $event->getShopItem()->getItem();
			if($item->getId() === Block::BROWN_GLAZED_TERRACOTTA){
				$this->onCompleted($player);
				return true;
			}
		}
		return false;
	}

}