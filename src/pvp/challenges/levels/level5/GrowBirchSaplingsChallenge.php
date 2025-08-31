<?php namespace skyblock\challenges\levels\level5;

use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\block\BlockLegacyIds as Block;
use pocketmine\item\ItemIds as Item;

use skyblock\challenges\Challenge;

class GrowBirchSaplingsChallenge extends Challenge{

	public function onEvent(Event $event, Player $player) : bool{
		if(!$this->isCompleted()){
			$block = $event->getBlock();
			$item = $event->getItem();
			if(
				$block->getId() == Block::SAPLING && $block->getIdInfo()->getVariant() == 2 &&
				$item->getId() == Item::DYE && $item->getMeta() == 15
			){
				$this->progress["grown"]["progress"]++;
				if($this->progress["grown"]["progress"] >= 10){
					$this->onCompleted($player);
				}
				return true;
			}
		}
		return false;
	}

}