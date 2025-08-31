<?php namespace skyblock\challenges\levels\level3;

use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\block\Wood;

use skyblock\challenges\Challenge;

class BreakWood2Challenge extends Challenge{

	public function onEvent(Event $event, Player $player) : bool{
		if(!$this->isCompleted()){
			$block = $event->getBlock();
			if($block instanceof Wood && $block->getTreeType()->getDisplayName() == "Oak"){
				$this->progress["logs"]["progress"]++;
				if($this->progress["logs"]["progress"] >= 200){
					$this->onCompleted($player);
				}
				return true;
			}
		}
		return false;
	}

}