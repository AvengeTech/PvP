<?php namespace skyblock\challenges\levels\level15;

use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\item\ItemIds as Item;

use skyblock\challenges\Challenge;

class CollectWitherSkullChallenge extends Challenge{

	public function onEvent(Event $event, Player $player) : bool{
		if(!$this->isCompleted()){
			$item = $event->getItemEntity()->getItem();
			if($item->getId() === Item::SKULL && $item->getMeta() == 1){
				$this->onCompleted($player);
				return true;
			}
		}
		return false;
	}

}