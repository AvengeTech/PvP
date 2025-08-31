<?php namespace pvp\games\stat;

use pvp\games\type\Game;

class GameStatHandler{

	const TYPE_ALLTIME = 0;
	const TYPE_WEEKLY = 1;
	const TYPE_MONTHLY = 2;

	public bool $changed = false;

	public array $stats = [];

	public function __construct(
		public Game $game,
		array $alltime_rows = [],
		array $weekly_rows = [],
		array $monthly_rows = []
	){
		$this->addStatHandlers($alltime_rows, $weekly_rows, $monthly_rows);
	}

	public function getGame() : Game{
		return $this->game;
	}

	public function addStatHandlers(array $alltime_rows = [], array $weekly_rows = [], array $monthly_rows = []) : void{
		$game = $this->getGame();
		$this->stats = [
			self::TYPE_ALLTIME => new GameStats(self::TYPE_ALLTIME, $game, $alltime_rows),
			self::TYPE_WEEKLY => new GameStats(self::TYPE_WEEKLY, $game, $weekly_rows),
			self::TYPE_MONTHLY => new GameStats(self::TYPE_MONTHLY, $game, $monthly_rows),
		];
	}
	
	public function getAllStats() : array{
		return $this->stats;
	}

	public function getStats(int $type = self::TYPE_ALLTIME) : ?GameStats{
		return $this->stats[$type] ?? null;
	}

	public function getKills(int $type = self::TYPE_ALLTIME) : int{
		return $this->getStats($type)->getKills();
	}

	public function setKills(int $kills, int $type = self::TYPE_ALLTIME) : void{
		$this->getStats($type)->setKills($kills);
		$this->setChanged();
	}

	public function addKill(int $kills = 1) : void{
		$this->setKills($this->getKills(self::TYPE_ALLTIME) + $kills, self::TYPE_ALLTIME);
		$this->setKills($this->getKills(self::TYPE_WEEKLY) + $kills, self::TYPE_WEEKLY);
		$this->setKills($this->getKills(self::TYPE_MONTHLY) + $kills, self::TYPE_MONTHLY);
	}

	public function getDeaths(int $type = self::TYPE_ALLTIME) : int{
		return $this->getStats($type)->getDeaths();
	}

	public function setDeaths(int $deaths, int $type = self::TYPE_ALLTIME) : void{
		$this->getStats($type)->setDeaths($deaths);
		$this->setChanged();
	}

	public function addDeath(int $deaths = 1) : void{
		$this->setDeaths($this->getDeaths(self::TYPE_ALLTIME) + $deaths, self::TYPE_ALLTIME);
		$this->setDeaths($this->getDeaths(self::TYPE_WEEKLY) + $deaths, self::TYPE_WEEKLY);
		$this->setDeaths($this->getDeaths(self::TYPE_MONTHLY) + $deaths, self::TYPE_MONTHLY);
	}

	public function getWins(int $type = self::TYPE_ALLTIME) : int{
		return $this->getStats($type)->getWins();
	}

	public function setWins(int $wins, int $type = self::TYPE_ALLTIME) : void{
		$this->getStats($type)->setWins($wins);
		$this->setChanged();
	}

	public function addWin(int $wins = 1) : void{
		$this->setWins($this->getWins(self::TYPE_ALLTIME) + $wins, self::TYPE_ALLTIME);
		$this->setWins($this->getWins(self::TYPE_WEEKLY) + $wins, self::TYPE_WEEKLY);
		$this->setWins($this->getWins(self::TYPE_MONTHLY) + $wins, self::TYPE_MONTHLY);
	}

	public function getLosses(int $type = self::TYPE_ALLTIME) : int{
		return $this->getStats($type)->getLosses();
	}

	public function setLosses(int $losses, int $type = self::TYPE_ALLTIME) : void{
		$this->getStats($type)->setLosses($losses);
		$this->setChanged();
	}

	public function addLoss(int $losses = 1) : void{
		$this->setLosses($this->getLosses(self::TYPE_ALLTIME) + $losses, self::TYPE_ALLTIME);
		$this->setLosses($this->getLosses(self::TYPE_WEEKLY) + $losses, self::TYPE_WEEKLY);
		$this->setLosses($this->getLosses(self::TYPE_MONTHLY) + $losses, self::TYPE_MONTHLY);
	}

	public function getHits(int $type = self::TYPE_ALLTIME) : int{
		return $this->getStats($type)->getHits();
	}

	public function setHits(int $hits, int $type = self::TYPE_ALLTIME) : void{
		$this->getStats($type)->setHits($hits);
		$this->setChanged();
	}

	public function addHit(int $hits = 1) : void{
		$this->setHits($this->getHits(self::TYPE_ALLTIME) + $hits, self::TYPE_ALLTIME);
		$this->setHits($this->getHits(self::TYPE_WEEKLY) + $hits, self::TYPE_WEEKLY);
		$this->setHits($this->getHits(self::TYPE_MONTHLY) + $hits, self::TYPE_MONTHLY);
	}

	public function getHighestCombo(int $type = self::TYPE_ALLTIME) : int{
		return $this->getStats($type)->getHighestCombo();
	}

	public function setHighestCombo(int $highest_combo, int $type = self::TYPE_ALLTIME) : void{
		$this->getStats($type)->setHighestCombo($highest_combo);
		$this->setChanged();
	}

	public function getHighestStreak(int $type = self::TYPE_ALLTIME) : int{
		return $this->getStats($type)->getHighestStreak();
	}

	public function setHighestStreak(int $highest_streak, int $type = self::TYPE_ALLTIME) : void{
		$this->getStats($type)->setHighestStreak($highest_streak);
		$this->setChanged();
	}

	public function getExtra(int $extra = 1, int $type = self::TYPE_ALLTIME) : int{
		return $this->getStats($type)->getExtra($extra);
	}

	public function setExtra(int $extra = 1, int $value = 0, int $type = self::TYPE_ALLTIME) : void{
		$this->getStats($type)->setExtra($extra, $value);
		$this->setChanged();
	}

	public function addExtra(int $extra = 1, int $value = 1) : void{
		$this->setExtra($extra, $this->getExtra($extra, self::TYPE_ALLTIME) + $value, self::TYPE_ALLTIME);
		$this->setExtra($extra, $this->getExtra($extra, self::TYPE_WEEKLY) + $value, self::TYPE_WEEKLY);
		$this->setExtra($extra, $this->getExtra($extra, self::TYPE_MONTHLY) + $value, self::TYPE_MONTHLY);
	}

	public function takeExtra(int $extra = 1, int $value = 1) : void{
		$this->setExtra($extra, $this->getExtra($extra, self::TYPE_ALLTIME) - $value, self::TYPE_ALLTIME);
		$this->setExtra($extra, $this->getExtra($extra, self::TYPE_WEEKLY) - $value, self::TYPE_WEEKLY);
		$this->setExtra($extra, $this->getExtra($extra, self::TYPE_MONTHLY) - $value, self::TYPE_MONTHLY);
	}

	public function addFromScorekeeper(Scorekeeper $sk) : void{
		foreach($this->getAllStats() as $stats){
			$stats->addFromScorekeeper($sk);
		}
	}

	public function hasChanged() : bool{
		return $this->changed;
	}

	public function setChanged(bool $changed = true) : void{
		$this->changed = $changed;
	}
	
}