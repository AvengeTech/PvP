<?php namespace skyblock\challenges\levels\level4;

use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\item\ItemBlock;
use pocketmine\block\Wood;

use skyblock\challenges\Challenge;

class CollectSpruceChallenge1 extends Challenge{

	public function onEvent(Event $event, Player $player) : bool{
		if(!$this->isCompleted()){
			$item = $event->getItemEntity()->getItem();
			if(
				$item instanceof ItemBlock &&
				($block = $item->getBlock()) instanceof Wood &&
				$block->getTreeType()->getDisplayName() == "Spruce"
			){
				$count = $item->getCount();
				$this->progress["collected"]["progress"] += $count;
				if($this->progress["collected"]["progress"] >= 50){
					$this->progress["collected"]["progress"] = 50;
					$this->onCompleted($player);
				}
				return true;
			}
		}
		return false;
	}

}