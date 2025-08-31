<?php namespace skyblock\challenges\levels\level7;

use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\item\ItemBlock;
use pocketmine\block\BlockLegacyIds;

use skyblock\challenges\Challenge;

class CollectAcaciaChallenge2 extends Challenge{

	public function onEvent(Event $event, Player $player) : bool{
		if(!$this->isCompleted()){
			$item = $event->getItemEntity()->getItem();
			if(
				$item instanceof ItemBlock &&
				$item->getId() == BlockLegacyIds::LOG2 &&
				$item->getMeta() == 0
			){
				$count = $item->getCount();
				$this->progress["collected"]["progress"] += $count;
				if($this->progress["collected"]["progress"] >= 200){
					$this->progress["collected"]["progress"] = 200;
					$this->onCompleted($player);
				}
				return true;
			}
		}
		return false;
	}

}