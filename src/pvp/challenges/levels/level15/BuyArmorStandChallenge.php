<?php namespace skyblock\challenges\levels\level15;

use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\item\ItemIds as Item;

use skyblock\challenges\Challenge;

class BuyArmorStandChallenge extends Challenge{

	public function onEvent(Event $event, Player $player) : bool{
		if(!$this->isCompleted()){
			$item = $event->getShopItem()->getItem();
			if($item->getId() === Item::ARMOR_STAND){
				$this->onCompleted($player);
				return true;
			}
		}
		return false;
	}

}