<?php namespace pvp\arenas;

use pvp\arenas\Arena;

use core\session\component\{
	ComponentRequest,
	SaveableComponent
};
use core\session\mysqli\data\MySqlQuery;

class ArenaComponent extends SaveableComponent{

	const TYPE_ALL = -1; //for updates
	const TYPE_ALLTIME = 0;
	const TYPE_WEEKLY = 1;
	const TYPE_MONTHLY = 2;

	public ?Arena $arena = null;
	public bool $spectator = false;

	public array $stats = []; //could break into ArenaStats classes?
	
	public int $currentCombo = 0;
	public int $currentStreak = 0;
	
	public int $currency = 0;

	public function getName() : string{
		return "arenas";
	}

	public function getArena() : ?Arena{
		return $this->arena;
	}

	public function setArena(?Arena $arena = null, bool $spectator = false) : void{
		if($this->getArena() !== null){
			$this->getArena()->removeScoreboard($this->getPlayer());
		}
		$this->arena = $arena;
		$this->spectator = $spectator;
	}

	public function inArena() : bool{
		return $this->getArena() !== null;
	}

	public function isSpectator() : bool{
		return $this->spectator;
	}
	
	public function getStats() : array{
		return $this->stats;
	}

	public function createArenaStats(Arena|string $arena) : void{
		$this->stats[$arena instanceof Arena ? $arena->getId() : $arena] = [
			"kills" => [
				"alltime" => 0,
				"weekly" => 0,
				"monthly" => 0,
			],
			"deaths" => [
				"alltime" => 0,
				"weekly" => 0,
				"monthly" => 0,
			],
			"combo" => [
				"alltime" => 0,
				"weekly" => 0,
				"monthly" => 0,
			],
			"streak" => [
				"alltime" => 0,
				"weekly" => 0,
				"monthly" => 0,
			],
			"changed" => false
		];
	}
	
	public function getKills(Arena|string $arena, int $type = self::TYPE_ALLTIME) : int{
		$tag = ($type == self::TYPE_ALLTIME ? "alltime" : ($type == self::TYPE_WEEKLY ? "weekly" : ($type == self::TYPE_MONTHLY ? "monthly" : "alltime")));
		return (($this->getStats()[$arena instanceof Arena ? $arena->getId() : $arena] ?? [])["kills"] ?? [])[$tag] ?? 0;
	}

	public function addKill(Arena|string $arena, int $type = self::TYPE_ALL) : void{
		if($type == self::TYPE_ALL){
			$this->addKill($arena, self::TYPE_ALLTIME);
			$this->addKill($arena, self::TYPE_WEEKLY);
			$this->addKill($arena, self::TYPE_MONTHLY);
			return;
		}
		$tag = ($type == self::TYPE_ALLTIME ? "alltime" : ($type == self::TYPE_WEEKLY ? "weekly" : ($type == self::TYPE_MONTHLY ? "monthly" : "alltime")));
		$id = $arena instanceof Arena ? $arena->getId() : $arena;
		if(!isset($this->stats[$id])) $this->createArenaStats($id);
		$this->stats[$id]["kills"][$tag]++;
		$this->stats[$id]["changed"] = true;
	}

	public function getDeaths(Arena|string $arena, int $type = self::TYPE_ALLTIME) : int{
		$tag = ($type == self::TYPE_ALLTIME ? "alltime" : ($type == self::TYPE_WEEKLY ? "weekly" : ($type == self::TYPE_MONTHLY ? "monthly" : "alltime")));
		return (($this->getStats()[$arena instanceof Arena ? $arena->getId() : $arena] ?? [])["deaths"] ?? [])[$tag] ?? 0;
	}

	public function addDeath(Arena|string $arena, int $type = self::TYPE_ALL) : void{
		if($type == self::TYPE_ALL){
			$this->addDeath($arena, self::TYPE_ALLTIME);
			$this->addDeath($arena, self::TYPE_WEEKLY);
			$this->addDeath($arena, self::TYPE_MONTHLY);
			return;
		}
		$tag = ($type == self::TYPE_ALLTIME ? "alltime" : ($type == self::TYPE_WEEKLY ? "weekly" : ($type == self::TYPE_MONTHLY ? "monthly" : "alltime")));
		$id = $arena instanceof Arena ? $arena->getId() : $arena;
		if(!isset($this->stats[$id])) $this->createArenaStats($id);
		$this->stats[$id]["deaths"][$tag]++;
		$this->stats[$id]["changed"] = true;
	}

	public function getCombo(Arena|string $arena, int $type = self::TYPE_ALLTIME) : int{
		$tag = ($type == self::TYPE_ALLTIME ? "alltime" : ($type == self::TYPE_WEEKLY ? "weekly" : ($type == self::TYPE_MONTHLY ? "monthly" : "alltime")));
		return (($this->getStats()[$arena instanceof Arena ? $arena->getId() : $arena] ?? [])["combo"] ?? [])[$tag] ?? 0;
	}

	public function setCombo(Arena|string $arena, int $combo, int $type = self::TYPE_ALL) : void{
		if($type == self::TYPE_ALL){
			$this->setCombo($arena, $combo, self::TYPE_ALLTIME);
			$this->setCombo($arena, $combo, self::TYPE_WEEKLY);
			$this->setCombo($arena, $combo, self::TYPE_MONTHLY);
			return;
		}
		$tag = ($type == self::TYPE_ALLTIME ? "alltime" : ($type == self::TYPE_WEEKLY ? "weekly" : ($type == self::TYPE_MONTHLY ? "monthly" : "alltime")));
		$id = $arena instanceof Arena ? $arena->getId() : $arena;
		if(!isset($this->stats[$id])) $this->createArenaStats($id);
		$this->stats[$id]["combo"][$tag] = $combo;
		$this->stats[$id]["changed"] = true;
	}

	public function addCombo(Arena|string $arena, int $type = self::TYPE_ALL) : void{
		if($type == self::TYPE_ALL){
			$this->addCombo($arena, self::TYPE_ALLTIME);
			$this->addCombo($arena, self::TYPE_WEEKLY);
			$this->addCombo($arena, self::TYPE_MONTHLY);
			return;
		}
		$this->setCombo($arena, $this->getCombo($arena, $type) + 1, $type);
	}

	public function getStreak(Arena|string $arena, int $type = self::TYPE_ALLTIME) : int{
		$tag = ($type == self::TYPE_ALLTIME ? "alltime" : ($type == self::TYPE_WEEKLY ? "weekly" : ($type == self::TYPE_MONTHLY ? "monthly" : "alltime")));
		return (($this->getStats()[$arena instanceof Arena ? $arena->getId() : $arena] ?? [])["streak"] ?? [])[$tag] ?? 0;
	}

	public function setStreak(Arena|string $arena, int $streak, int $type = self::TYPE_ALL) : void{
		if($type == self::TYPE_ALL){
			$this->setStreak($arena, $streak, self::TYPE_ALLTIME);
			$this->setStreak($arena, $streak, self::TYPE_WEEKLY);
			$this->setStreak($arena, $streak, self::TYPE_MONTHLY);
			return;
		}
		$tag = ($type == self::TYPE_ALLTIME ? "alltime" : ($type == self::TYPE_WEEKLY ? "weekly" : ($type == self::TYPE_MONTHLY ? "monthly" : "alltime")));
		$id = $arena instanceof Arena ? $arena->getId() : $arena;
		if(!isset($this->stats[$id])) $this->createArenaStats($id);
		$this->stats[$id]["streak"][$tag] = $streak;
		$this->stats[$id]["changed"] = true;
	}

	public function addStreak(Arena|string $arena, int $type = self::TYPE_ALL) : void{
		if($type == self::TYPE_ALL){
			$this->addStreak($arena, self::TYPE_ALLTIME);
			$this->addStreak($arena, self::TYPE_WEEKLY);
			$this->addStreak($arena, self::TYPE_MONTHLY);
			return;
		}
		$this->setStreak($arena, $this->getStreak($arena, $type) + 1, $type);
	}
	
	public function getCurrentCombo() : int{
		return $this->currentCombo;
	}
	
	public function addCurrentCombo() : void{
		$this->currentCombo++;
	}
	
	public function resetCurrentCombo() : void{
		$this->currentCombo = 0;
	}
	
	public function getCurrentStreak() : int{
		return $this->currentStreak;
	}

	public function addCurrentStreak() : void{
		$this->currentStreak++;
	}
	
	public function resetCurrentStreak() : void{
		$this->currentStreak = 0;
	}

	public function createTables() : void{
		$db = $this->getSession()->getSessionManager()->getDatabase();
		foreach([
			//"CREATE TABLE IF NOT EXISTS arena_stats(
			//	xuid BIGINT(16) NOT NULL,
			//	arena VARCHAR(32) NOT NULL,
			//	kills_alltime INT NOT NULL DEFAULT 0, kills_weekly INT NOT NULL DEFAULT 0, kills_monthly INT NOT NULL DEFAULT 0,
			//	deaths_alltime INT NOT NULL DEFAULT 0, deaths_weekly INT NOT NULL DEFAULT 0, deaths_monthly INT NOT NULL DEFAULT 0,
			//	combo_alltime INT NOT NULL DEFAULT 0, combo_weekly INT NOT NULL DEFAULT 0, combo_monthly INT NOT NULL DEFAULT 0,
			//	streak_alltime INT NOT NULL DEFAULT 0, streak_weekly INT NOT NULL DEFAULT 0, streak_monthly INT NOT NULL DEFAULT 0,
			//	PRIMARY KEY(xuid, arena)
			//)",

			"CREATE TABLE IF NOT EXISTS arena_stats(xuid BIGINT(16) NOT NULL,arena VARCHAR(32) NOT NULL,kills_alltime INT NOT NULL DEFAULT 0, kills_weekly INT NOT NULL DEFAULT 0, kills_monthly INT NOT NULL DEFAULT 0,deaths_alltime INT NOT NULL DEFAULT 0, deaths_weekly INT NOT NULL DEFAULT 0, deaths_monthly INT NOT NULL DEFAULT 0,combo_alltime INT NOT NULL DEFAULT 0, combo_weekly INT NOT NULL DEFAULT 0, combo_monthly INT NOT NULL DEFAULT 0,streak_alltime INT NOT NULL DEFAULT 0, streak_weekly INT NOT NULL DEFAULT 0, streak_monthly INT NOT NULL DEFAULT 0,PRIMARY KEY(xuid, arena))"
		] as $query) $db->query($query);
		echo $db->error, PHP_EOL;
	}

	public function loadAsync() : void{
		$request = new ComponentRequest($this->getXuid(), $this->getName(), new MySqlQuery("main", "SELECT * FROM arena_stats WHERE xuid=?", [$this->getXuid()]));
		$this->newRequest($request, ComponentRequest::TYPE_LOAD);
		parent::loadAsync();
	}

	public function finishLoadAsync(?ComponentRequest $request = null) : void{
		$result = $request->getQuery()->getResult();
		$rows = (array) $result->getRows();
		if(count($rows) > 0){
			foreach($rows as $row){
				$this->stats[$row["arena"]] = [
					"kills" => [
						"alltime" => $row["kills_alltime"],
						"weekly" => $row["kills_weekly"],
						"monthly" => $row["kills_monthly"],
					],
					"deaths" => [
						"alltime" => $row["deaths_alltime"],
						"weekly" => $row["deaths_weekly"],
						"monthly" => $row["deaths_monthly"],
					],
					"combo" => [
						"alltime" => $row["combo_alltime"],
						"weekly" => $row["combo_weekly"],
						"monthly" => $row["combo_monthly"],
					],
					"streak" => [
						"alltime" => $row["streak_alltime"],
						"weekly" => $row["streak_weekly"],
						"monthly" => $row["streak_monthly"],
					],
					"changed" => false
				];
			}
		}

		parent::finishLoadAsync($request);
		echo $this->getName() . " component finished loading async", PHP_EOL;
	}

	public function verifyChange() : bool{
		$verify = $this->getChangeVerify();
		return $this->getStats() !== $verify["stats"];
	}

	public function saveAsync() : void{
		if(!$this->isLoaded()) return;

		$player = $this->getPlayer();
		$request = new ComponentRequest($this->getXuid(), $this->getName(), []);
		foreach($this->getStats() as $arenaId => $stats){
			if($stats["changed"]){
				$request->addQuery(new MySqlQuery(
					"arena_stats_" . $this->getXuid() . "_" . $arenaId,
					"INSERT INTO arena_stats(
						xuid, arena,
						kills_alltime, kills_weekly, kills_monthly,
						deaths_alltime, deaths_weekly, deaths_monthly,
						combo_alltime, combo_weekly, combo_monthly,
						streak_alltime, streak_weekly, streak_monthly
					) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE
						kills_alltime=VALUES(kills_alltime),
						kills_weekly=VALUES(kills_weekly), 
						kills_monthly=VALUES(kills_monthly),
						
						deaths_alltime=VALUES(deaths_alltime),
						deaths_weekly=VALUES(deaths_weekly), 
						deaths_monthly=VALUES(deaths_monthly), 
						
						combo_alltime=VALUES(combo_alltime),
						combo_weekly=VALUES(combo_weekly), 
						combo_monthly=VALUES(combo_monthly), 
						
						streak_alltime=VALUES(streak_alltime),
						streak_weekly=VALUES(streak_weekly), 
						streak_monthly=VALUES(streak_monthly);",
					[
						$this->getXuid(), $arenaId,
						$stats["kills"]["alltime"], $stats["kills"]["weekly"], $stats["kills"]["monthly"],
						$stats["deaths"]["alltime"], $stats["deaths"]["weekly"], $stats["deaths"]["monthly"],
						$stats["combo"]["alltime"], $stats["combo"]["weekly"], $stats["combo"]["monthly"],
						$stats["streak"]["alltime"], $stats["streak"]["weekly"], $stats["streak"]["monthly"]
					]
				));
				$this->stats[$arenaId]["changed"] = false;
			}

			$this->setChangeVerify([
				"stats" => $this->getStats(),
			]);
		}
		if(count($request->getQueries()) === 0) return;

		$this->newRequest($request, ComponentRequest::TYPE_SAVE);
		parent::saveAsync();
	}

	public function finishSaveAsync() : void{
		parent::finishSaveAsync();

		echo $this->getName() . " component finished saving async", PHP_EOL;
	}

	public function save() : bool{
		if(!$this->isLoaded()) return false;

		$xuid = $this->getXuid();

		$db = $this->getSession()->getSessionManager()->getDatabase();
		$stmt = $db->prepare(
			"INSERT INTO arena_stats(
				xuid, arena,
				kills_alltime, kills_weekly, kills_monthly,
				deaths_alltime, deaths_weekly, deaths_monthly,
				combo_alltime, combo_weekly, combo_monthly,
				streak_alltime, streak_weekly, streak_monthly
			) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE
				kills_alltime=VALUES(kills_alltime),
				kills_weekly=VALUES(kills_weekly), 
				kills_monthly=VALUES(kills_monthly),
				
				deaths_alltime=VALUES(deaths_alltime),
				deaths_weekly=VALUES(deaths_weekly), 
				deaths_monthly=VALUES(deaths_monthly), 
				
				combo_alltime=VALUES(combo_alltime),
				combo_weekly=VALUES(combo_weekly), 
				combo_monthly=VALUES(combo_monthly), 
				
				streak_alltime=VALUES(streak_alltime),
				streak_weekly=VALUES(streak_weekly), 
				streak_monthly=VALUES(streak_monthly);"
		);
		foreach($this->getStats() as $arenaId => $stats){
			if($stats["changed"]){
				$ka = $stats["kills"]["alltime"];
				$kw = $stats["kills"]["weekly"];
				$km = $stats["kills"]["monthly"];

				$da = $stats["deaths"]["alltime"];
				$dw = $stats["deaths"]["weekly"];
				$dm = $stats["deaths"]["monthly"];

				$ca = $stats["combo"]["alltime"];
				$cw = $stats["combo"]["weekly"];
				$cm = $stats["combo"]["monthly"];

				$sa = $stats["streak"]["alltime"];
				$sw = $stats["streak"]["weekly"];
				$sm = $stats["streak"]["monthly"];

				$stmt->bind_param("isiiiiiiiiiiii", $xuid, $arenaId, $ka, $kw, $km, $da, $dw, $dm, $ca, $cw, $cm, $sa, $sw, $sm);
				$stmt->execute();

				$this->stats[$arenaId]["changed"] = false;
			}
		}
		$stmt->close();

		echo $this->getName() . " component saved on main thread", PHP_EOL;
		return parent::save();
	}

}