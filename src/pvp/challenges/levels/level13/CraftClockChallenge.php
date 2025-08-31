<?php namespace skyblock\challenges\levels\level13;

use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\item\ItemIds as Item;

use skyblock\challenges\Challenge;

class CraftClockChallenge extends Challenge{

	public function onEvent(Event $event, Player $player) : bool{
		if(!$this->isCompleted()){
			foreach($event->getOutputs() as $output){
				if($output->getId() === Item::CLOCK){
					$this->onCompleted($player);
					return true;
				}
			}
		}
		return false;
	}

}