<?php namespace skyblock\challenges\levels\level4;

use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\item\ItemIds as Item;
use pocketmine\block\BlockLegacyIds as Block;

use skyblock\challenges\Challenge;

class BonemealSaplingsChallenge extends Challenge{

	public function onEvent(Event $event, Player $player) : bool{
		if(!$this->isCompleted()){
			$item = $event->getItem();
			$block = $event->getBlock();
			if($item->getId() == Item::DYE && $item->getMeta() == 15 && $block->getId() == Block::SAPLING){
				$count = $item->getCount();
				$this->progress["saplings"]["progress"] += $count;
				if($this->progress["saplings"]["progress"] >= 10){
					$this->progress["saplings"]["progress"] = 10;
					$this->onCompleted($player);
				}
				return true;
			}
		}
		return false;
	}

}