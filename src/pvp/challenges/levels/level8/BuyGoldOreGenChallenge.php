<?php namespace skyblock\challenges\levels\level8;

use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\block\BlockLegacyIds as Block;

use skyblock\challenges\Challenge;

class BuyGoldOreGenChallenge extends Challenge{

	public function onEvent(Event $event, Player $player) : bool{
		if(!$this->isCompleted()){
			$item = $event->getShopItem()->getItem();
			if($item->getId() === Block::GRAY_GLAZED_TERRACOTTA && $item->getMeta() == 5){
				$this->onCompleted($player);
				return true;
			}
		}
		return false;
	}

}