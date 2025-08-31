<?php namespace skyblock\challenges\levels\level7;

use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\block\BlockLegacyIds as Block;

use skyblock\challenges\Challenge;

class CollectLeavesChallenge extends Challenge{

	public function onEvent(Event $event, Player $player) : bool{
		if(!$this->isCompleted()){
			$item = $event->getItemEntity()->getItem();
			if($item->getId() === Block::LEAVES){
				$count = $item->getCount();
				$this->progress["collected"]["progress"] += $count;
				if($this->progress["collected"]["progress"] >= 64){
					$this->progress["collected"]["progress"] = 64;
					$this->onCompleted($player);
				}
				return true;
			}
		}
		return false;
	}

}