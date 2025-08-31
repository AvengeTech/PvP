<?php namespace pvp\games\team;

use pocketmine\player\Player;
use pvp\games\stat\Scorekeeper;
use pvp\games\type\Game;
use pvp\games\type\GameSettings;

class TeamManager{

	public array $teams = [];

	public function __construct(
		public Game $game,
		public GameSettings $settings,
		array $teams = []
	){
		if(count($teams) > 0){
			foreach($teams as $id => $players){
				$this->teams[$id] = new Team($this, $id, $players);
			}
		}
	}

	public function getGame() : Game{
		return $this->game;
	}

	public function getMaxTeams() : int{
		return $this->settings->maxTeams;
	}

	public function getPlayersPerTeam() : int{
		return $this->settings->perTeam;
	}

	public function getTeams() : array{
		return $this->teams;
	}

	public function getTeam(int $id) : ?Team{
		return $this->teams[$id] ?? null;
	}

	public function getTeamByPlayer(Player $player) : ?Team{
		foreach($this->getTeams() as $team){
			if($team->hasPlayer($player)) return $team;
		}
		return null;
	}

	public function hasPlayer(Player $player) : bool{
		foreach($this->getTeams() as $team){
			if($team->hasPlayer($player)) return true;
		}
		return false;
	}

	public function getPlayer(Player $player) : ?Scorekeeper{
		foreach($this->getTeams() as $team){
			if($team->hasPlayer($player)) return $team->getPlayer($player);
		}
		return null;
	}

	public function addPlayer(Player $player, ?Team $preferredTeam = null) : ?Team{
		if($preferredTeam !== null){
			if(count($preferredTeam->getPlayers()) >= $this->getPlayersPerTeam()){
				return null;
			}
			($team = $this->getTeam($preferredTeam->getId()))->addPlayer($player);
			return $team;
		}
		//todo: look for team with least players
		foreach($this->getTeams() as $team){
			if(count($team->getPlayers()) >= $this->getPlayersPerTeam()) continue;
			$team->addPlayer($player);
			return $team;
		}
		return null;
	}

	public function removePlayer(Player $player) : void{
		foreach($this->getTeams() as $team){
			if($team->hasPlayer($player)) $team->removePlayer($player);
		}
	}

}