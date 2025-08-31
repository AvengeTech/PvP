<?php namespace pvp\games\type;

use pocketmine\player\Player;
use pvp\games\GameHandler;
use pvp\games\stat\Scorekeeper;
use pvp\games\team\TeamManager;

class TeamGame extends Game{

	public TeamManager $teamManager;

	public function __construct(
		GameHandler  $handler,

		int $id,
		GameSettings $settings,
		array $players
	){
		parent::__construct($handler, $id, $settings, $players);
		$this->teamManager = new TeamManager($this, $settings, $players);
	}

	public function tick() : void{
		
	}

	public function getTeamManager() : TeamManager{
		return $this->teamManager;
	}

	public function addPlayer(Player $player) : void{

	}

	public function getPlayer(Player $player) : ?Scorekeeper{
		return $this->getTeamManager()->getPlayer($player);
	}

	public function hasPlayer(Player $player) : bool{
		return isset($this->players[$player->getName()]);
	}

	public function removePlayer(Player $player, bool $gotoSpawn = true, bool $left = false) : void{

	}

	public function processKill(Player $killer, Player $dead) : void{

	}
}