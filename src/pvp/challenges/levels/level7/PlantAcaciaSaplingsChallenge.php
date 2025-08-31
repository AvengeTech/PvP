<?php namespace skyblock\challenges\levels\level7;

use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\block\BlockLegacyIds as Block;

use skyblock\challenges\Challenge;

class PlantAcaciaSaplingsChallenge extends Challenge{

	public function onEvent(Event $event, Player $player) : bool{
		if(!$this->isCompleted()){
			$block = $event->getBlock();
			if($block->getId() == Block::SAPLING && $block->getIdInfo()->getVariant() == 4){
				$this->progress["planted"]["progress"]++;
				if($this->progress["planted"]["progress"] >= 5){
					$this->onCompleted($player);
				}
				return true;
			}
		}
		return false;
	}

}