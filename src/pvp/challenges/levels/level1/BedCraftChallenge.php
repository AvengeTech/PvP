<?php namespace skyblock\challenges\levels\level1;

use pocketmine\event\Event;
use pocketmine\item\ItemIds;
use pocketmine\player\Player;

use skyblock\challenges\Challenge;

class BedCraftChallenge extends Challenge{

	public function onEvent(Event $event, Player $player) : bool{
		if(!$this->isCompleted()){
			foreach($event->getOutputs() as $output){
				if($output->getId() === ItemIds::BED){
					$this->onCompleted($player);
					return true;
				}
			}
		}
		return false;
	}

}