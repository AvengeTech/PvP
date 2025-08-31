<?php namespace pvp\games\team;

use pocketmine\player\Player;
use pvp\games\stat\Scorekeeper;

class Team{

	public array $players = [];

	public function __construct(public TeamManager $teamManager, public int $id, array $players = []){
		if(!empty($players)){
			foreach($players as $player){
				$this->players[$player->getName()] = new Scorekeeper($teamManager->getGame()->getSettings(), $player);
			}
		}
	}

	public function getTeamManager() : TeamManager{
		return $this->teamManager;
	}

	public function getId() : int{
		return $this->id;
	}

	public function getPlayers() : array{
		return $this->players;
	}

	public function hasPlayer(Player $player) : bool{
		return isset($this->players[$player->getName()]);
	}

	public function addPlayer(Player $player) : void{
		$this->players[$player->getName()] = new Scorekeeper($this->getTeamManager()->getGame()->getSettings(), $player);
	}

	public function removePlayer(Player $player) : void{
		if (!$this->hasPlayer($player)) return;
		unset($this->players[$player->getName()]);
	}

	public function getPlayer(Player $player) : ?Scorekeeper{
		return $this->players[$player->getName()] ?? null;
	}

}