<?php namespace skyblock\challenges\levels\level7;

use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\block\BlockLegacyIds as Block;

use skyblock\challenges\Challenge;

class BuyLapisOreGenChallenge extends Challenge{

	public function onEvent(Event $event, Player $player) : bool{
		if(!$this->isCompleted()){
			$item = $event->getShopItem()->getItem();
			if($item->getId() === Block::GRAY_GLAZED_TERRACOTTA && $item->getMeta() == 4){
				$this->onCompleted($player);
				return true;
			}
		}
		return false;
	}

}