<?php namespace skyblock\challenges\levels\level5;

use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\block\BlockLegacyIds as Block;

use skyblock\challenges\Challenge;

class CollectPumpkinsChallenge extends Challenge{

	public function onEvent(Event $event, Player $player) : bool{
		if(!$this->isCompleted()){
			if(isset($this->progress["lanterns"])) unset($this->progress["lanterns"]);
			if(isset($this->progress["collected"])){
				if(is_int($this->progress["collected"])){
					$this->progress["collected"] = ["progress" => 0, "needed" => 5];
				}
			}
			if(!isset($this->progress["collected"])) $this->progress["collected"] = ["progress" => 0, "needed" => 5];

			$item = $event->getItemEntity()->getItem();
			if($item->getId() === Block::PUMPKIN){
				$count = $item->getCount();
				$this->progress["collected"]["progress"] += $count;
				if($this->progress["collected"]["progress"] >= 5){
					$this->progress["collected"]["progress"] = 5;
					$this->onCompleted($player);
				}
				return true;
			}
		}
		return false;
	}

}