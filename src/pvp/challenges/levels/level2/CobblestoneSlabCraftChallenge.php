<?php namespace skyblock\challenges\levels\level2;

use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\block\BlockLegacyIds as Block;

use skyblock\challenges\Challenge;

class CobblestoneSlabCraftChallenge extends Challenge{

	public function onEvent(Event $event, Player $player) : bool{
		if(!$this->isCompleted()){
			foreach($event->getOutputs() as $output){
				if($output->getId() === Block::STONE_SLAB && $output->getMeta() == 3){
					$count = $output->getCount();
					$this->progress["slabs"]["progress"] += $count;
					if($this->progress["slabs"]["progress"] >= 10){
						$this->progress["slabs"]["progress"] = 10;
						$this->onCompleted($player);
					}
					return true;
				}
			}
		}
		return false;
	}

}