<?php namespace skyblock\challenges\levels\level6;

use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\block\BlockLegacyIds as Block;

use skyblock\challenges\Challenge;

class BuyRedstoneOreGenChallenge extends Challenge{

	public function onEvent(Event $event, Player $player) : bool{
		if(!$this->isCompleted()){
			$item = $event->getShopItem()->getItem();
			if($item->getId() === Block::GRAY_GLAZED_TERRACOTTA && $item->getMeta() == 3){
				$this->onCompleted($player);
				return true;
			}
		}
		return false;
	}

}