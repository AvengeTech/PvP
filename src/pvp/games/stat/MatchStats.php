<?php namespace pvp\games\stat;

use pvp\games\type\{
	Game,
	GameSettings
};

use core\user\User;

class MatchStats{

	public int $round = 0;

	public int $kills = 0;
	public int $deaths = 0;

	public int $hits = 0;
	public int $highest_combo = 0;

	public int $highest_streak = 0;

	public int $extra1 = 0;
	public int $extra2 = 0;
	public int $extra3 = 0;

	public function __construct(
		public string $matchId,
		public User $user,
		public Game $game,
		array $rows = []
	){
		if(count($rows) !== 0){
			$this->setupFromRows($rows);
		}
	}

	public function setupFromRows(array $rows) : void{
		$this->round = $rows["round"] ?? 0;

		$this->kills = $rows["kills"] ?? 0;
		$this->deaths = $rows["deaths"] ?? 0;

		$this->hits = $rows["hits"] ?? 0;
		$this->highest_combo = $rows["highest_combo"] ?? 0;

		$this->highest_streak = $rows["streak"] ?? 0;

		$this->extra1 = $rows["extra1"] ?? 0;
		$this->extra2 = $rows["extra2"] ?? 0;
		$this->extra3 = $rows["extra3"] ?? 0;
	}

	public function getMatchId() : string{
		return $this->matchId;
	}

	public function getUser() : User{
		return $this->user;
	}

	public function getGame() : Game{
		return $this->game;
	}

	public function getGameSettings() : GameSettings{
		return $this->getGame()->getSettings();
	}

	public function getKills() : int{
		return $this->kills;
	}

	public function getDeaths() : int{
		return $this->deaths;
	}

	public function getHits() : int{
		return $this->hits;
	}

	public function getHighestCombo() : int{
		return $this->highest_combo;
	}

	public function getHighestStreak() : int{
		return $this->highest_streak;
	}

}