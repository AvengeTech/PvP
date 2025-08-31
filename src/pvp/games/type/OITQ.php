<?php namespace pvp\games\type;

use pocketmine\item\VanillaItems;
use pocketmine\player\Player;

class OITQ extends Game{

	public function getName() : string{
		return "oitq";
	}

	public function getInstructions() : string{
		return "Use your one shot bow and be the first player to get " . $this->getSettings()->getKillsNeeded() . " kills!";
		//todo: make dynamic based on if its respawns or not?
	}

	public function processKill(Player $killer, Player $dead) : void{
		parent::processKill($killer, $dead);
		if(!$this->isEnded()) $killer->getInventory()->addItem(VanillaItems::ARROW());
	}

}