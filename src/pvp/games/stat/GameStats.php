<?php namespace pvp\games\stat;

use pvp\games\type\{
	Game,
	GameSettings
};

use core\user\User;
use core\session\mysqli\data\MySqlQuery;

class GameStats{

	public bool $changed = false;

	public int $kills = 0;
	public int $deaths = 0;
	
	public int $wins = 0;
	public int $losses = 0;

	public int $hits = 0;
	public int $highest_combo = 0;
	
	public int $highest_streak = 0;

	public int $extra1 = 0;
	public int $extra2 = 0;
	public int $extra3 = 0;

	public function __construct(
		public int $type,
		public Game $game,
		array $row = []
	){
		if(count($row) !== 0){
			$this->setupFromRow($row);
		}
	}
	
	public function setupFromRow(array $row) : void{
		$this->kills = $row["kills"] ?? 0;
		$this->deaths = $row["deaths"] ?? 0;
		
		$this->wins = $row["wins"] ?? 0;
		$this->losses = $row["losses"] ?? 0;

		$this->hits = $row["hits"] ?? 0;
		$this->highest_combo = $row["highest_combo"] ?? 0;
		
		$this->highest_streak = $row["streak"] ?? 0;

		$this->extra1 = $row["extra1"] ?? 0;
		$this->extra2 = $row["extra2"] ?? 0;
		$this->extra3 = $row["extra3"] ?? 0;
	}
	
	public function getType() : int{
		return $this->type;
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

	public function setKills(int $kills) : void{
		$this->kills = $kills;
		$this->setChanged();
	}

	public function addKill(int $kills = 1) : void{
		$this->setKills($this->getKills() + $kills);
	}

	public function getDeaths() : int{
		return $this->deaths;
	}

	public function setDeaths(int $deaths) : void{
		$this->deaths = $deaths;
		$this->setChanged();
	}

	public function addDeath(int $deaths = 1) : void{
		$this->setDeaths($this->getDeaths() + $deaths);
	}

	public function getWins() : int{
		return $this->wins;
	}

	public function setWins(int $wins) : void{
		$this->wins = $wins;
		$this->setChanged();
	}

	public function addWin(int $wins = 1) : void{
		$this->setWins($this->getWins() + $wins);
	}

	public function getLosses() : int{
		return $this->losses;
	}

	public function setLosses(int $losses) : void{
		$this->losses = $losses;
		$this->setChanged();
	}

	public function addLoss(int $losses = 1) : void{
		$this->setLosses($this->getLosses() + $losses);
	}

	public function getHits() : int{
		return $this->hits;
	}

	public function setHits(int $hits) : void{
		$this->hits = $hits;
		$this->setChanged();
	}

	public function addHit(int $hits = 1) : void{
		$this->setHits($this->getHits() + $hits);
	}

	public function getHighestCombo() : int{
		return $this->highest_combo;
	}

	public function setHighestCombo(int $highest_combo) : void{
		$this->highest_combo = $highest_combo;
		$this->setChanged();
	}

	public function getHighestStreak() : int{
		return $this->highest_streak;
	}

	public function setHighestStreak(int $highest_streak) : void{
		$this->highest_streak = $highest_streak;
		$this->setChanged();
	}

	public function getExtra(int $extra = 1) : int{
		return match($extra){
			1 => $this->extra1,
			2 => $this->extra2,
			3 => $this->extra3,
			default => $this->extra1
		};
	}

	public function setExtra(int $extra = 1, int $value = 0) : void{
		switch($extra){
			case 1:
				$this->extra1 = $value;
				break;
			case 2:
				$this->extra2 = $value;
				break;
			case 3:
				$this->extra3 = $value;
				break;
		}
		$this->setChanged();
	}

	public function addExtra(int $extra = 1, int $value = 1) : void{
		$this->setExtra($extra, $this->getExtra($extra) + $value);
	}

	public function takeExtra(int $extra = 1, int $value = 1) : void{
		$this->setExtra($extra, $this->getExtra($extra) - $value);
	}

	public function addFromScorekeeper(Scorekeeper $sk) : void{
		$this->addKill($sk->getScore("kills"));
		if($sk->getScore("deaths") !== -1)
			$this->addDeath($sk->getScore("deaths"));

		$this->addHit($sk->getScore("hits"));
		if(($hc = $sk->getScore("highest_combo")) > $this->getHighestCombo())
			$this->setHighestCombo($hc);
		if(($hs = $sk->getScore("highest_streak")) > $this->getHighestStreak())
			$this->setHighestStreak($hs);

		//prob do extras in game class cuz they can b diff
	}

	public function hasChanged() : bool{
		return $this->changed;
	}

	public function setChanged(bool $changed = true) : void{
		$this->changed = $changed;
	}
	
	public function getQuery(User $user) : MySqlQuery{
		return new MySqlQuery(
			"game_stats_" . $user->getXuid() . "_" . $this->getGame()->getName() . "_" . $this->getGameSettings()->getStatTag() . "_" . $this->getType(),
			"INSERT INTO game_stats(
				xuid,
				game, stat_tag, type,
				kills, deaths,
				wins, losses,
				hits, highest_combo, highest_streak,
				extra1, extra2, extra3
			) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE
				kills=VALUES(kills),
				deaths=VALUES(deaths),
				wins=VALUES(wins),
				losses=VALUES(losses),
				hits=VALUES(hits),
				highest_combo=VALUES(highest_combo),
				highest_streak=VALUES(highest_streak),
				extra1=VALUES(extra1),
				extra2=VALUES(extra2),
				extra3=VALUES(extra3)",
			[
				$user->getXuid(),
				$this->getGame()->getName(),
				$this->getGameSettings()->getStatTag(),
				$this->getType(),
				$this->getKills(), $this->getDeaths(),
				$this->getWins(), $this->getLosses(),
				$this->getHits(), $this->getHighestCombo(), $this->getHighestStreak(),
				$this->getExtra(1), $this->getExtra(2), $this->getExtra(3)
			]
		);
	}
	
	/**
	 * Main-thread saving :3
	 */
	public function mtSave(User $user, \mysqli $db) : void{
		$xuid = $user->getXuid();
		
		$game = $this->getGame()->getName();
		$statTag = $this->getGameSettings()->getStatTag();
		$type = $this->getType();
		
		$kills = $this->getKills();
		$deaths = $this->getDeaths();
		$wins = $this->getWins();
		$losses = $this->getLosses();
		$hits = $this->getHits();
		$highest_combo = $this->getHighestCombo();
		$highest_streak = $this->getHighestStreak();

		$extra1 = $this->getExtra(1);
		$extra2 = $this->getExtra(2);
		$extra3 = $this->getExtra(3);

		$stmt = $db->prepare(
			"INSERT INTO game_stats(
				xuid,
				game, stat_tag, type,
				kills, deaths,
				wins, losses,
				hits, highest_combo, highest_streak,
				extra1, extra2, extra3
			) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE
				kills=VALUES(kills),
				deaths=VALUES(deaths),
				wins=VALUES(wins),
				losses=VALUES(losses),
				hits=VALUES(hits),
				highest_combo=VALUES(highest_combo),
				highest_streak=VALUES(highest_streak),
				extra1=VALUES(extra1),
				extra2=VALUES(extra2),
				extra3=VALUES(extra3)"
		);
		$stmt->bind_param("issiiiiiiiiiii", $xuid, $game, $statTag, $type, $kills, $deaths, $wins, $losses, $hits, $highest_combo, $highest_streak, $extra1, $extra2, $extra3);
		$stmt->execute();
		$stmt->close();
	}

}