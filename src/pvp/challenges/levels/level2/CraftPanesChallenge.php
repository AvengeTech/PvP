<?php namespace skyblock\challenges\levels\level2;

use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\block\BlockLegacyIds as Block;

use skyblock\challenges\Challenge;

class CraftPanesChallenge extends Challenge{

	public function onEvent(Event $event, Player $player) : bool{
		if(!$this->isCompleted()){
			foreach($event->getOutputs() as $output){
				if($output->getId() === Block::GLASS_PANE){
					$count = $output->getCount();
					$this->progress["panes"]["progress"] += $count;
					if($this->progress["panes"]["progress"] >= 16){
						$this->progress["panes"]["progress"] = 16;
						$this->onCompleted($player);
					}
					return true;
				}
			}
		}
		return false;
	}

}