<?php namespace pvp\games\type;

use pocketmine\player\GameMode;

class SkyWars extends Game{

	public function getName() : string{
		return "skywars";
	}

	public function getInstructions() : string{
		return "Collect loot and be the last player alive to win!";
	}
	
	public function start() : void{
		parent::start();
		foreach($this->getPlayers() as $player){
			$player->getPlayer()->setGamemode(GameMode::SURVIVAL());
		}
	}

}