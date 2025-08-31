<?php namespace skyblock\challenges\levels\level10;

use pocketmine\event\Event;
use pocketmine\player\Player;

use skyblock\challenges\Challenge;

class CollectGoldKeysChallenge extends Challenge{

	public function onEvent(Event $event, Player $player) : bool{
		if(!$this->isCompleted()){
			$keytype = $event->getKeyType();
			$amount = $event->getAmount();
			if($keytype === "gold"){
				$this->progress["keys"]["progress"] += $amount;
				if($this->progress["keys"]["progress"] >= 10){
					$this->progress["keys"]["progress"] = 10;
					$this->onCompleted($player);
				}
				return true;
			}
		}
		return false;
	}

}