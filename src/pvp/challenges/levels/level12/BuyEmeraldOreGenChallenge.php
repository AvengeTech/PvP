<?php namespace skyblock\challenges\levels\level12;

use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\block\BlockLegacyIds as Block;

use skyblock\challenges\Challenge;

class BuyEmeraldOreGenChallenge extends Challenge{

	public function onEvent(Event $event, Player $player) : bool{
		if(!$this->isCompleted()){
			$item = $event->getShopItem()->getItem();
			if($item->getId() === Block::SILVER_GLAZED_TERRACOTTA && $item->getMeta() == 2){
				$this->onCompleted($player);
				return true;
			}
		}
		return false;
	}

}