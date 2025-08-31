<?php namespace skyblock\challenges\levels\level1;

use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\block\BlockLegacyIds as Block;

use skyblock\challenges\Challenge;

class FurnaceCraftChallenge extends Challenge{

	public function onEvent(Event $event, Player $player) : bool{
		if(!$this->isCompleted()){
			foreach($event->getOutputs() as $output){
				if($output->getId() === Block::FURNACE){
					$this->onCompleted($player);
					return true;
				}
			}
		}
		return false;
	}

}