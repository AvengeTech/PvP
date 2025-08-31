<?php namespace skyblock\challenges\levels\level9;

use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\block\BlockLegacyIds as Block;

use skyblock\challenges\Challenge;

class CraftRedstoneBlocksChallenge extends Challenge{

	public function onEvent(Event $event, Player $player) : bool{
		if(!$this->isCompleted()){
			foreach($event->getOutputs() as $output){
				if($output->getId() === Block::REDSTONE_BLOCK){
					$count = $output->getCount();
					$this->progress["blocks"]["progress"] += $count;
					if($this->progress["blocks"]["progress"] >= 16){
						$this->progress["blocks"]["progress"] = 16;
						$this->onCompleted($player);
					}
					return true;
				}
			}
		}
		return false;
	}

}