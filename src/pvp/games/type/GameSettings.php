<?php namespace pvp\games\type;

use pvp\kits\KitLibrary;

class GameSettings{
	
	const ROUND_SCORE_KILLS = 0;
	const ROUND_SCORE_HITS = 1;

	public bool $custom = false;

	public function __construct(
		public ?KitLibrary $kitLibrary = null,
		public string $displayName = "",
		public string $statTag = "", //used for storing stats with different game modes

		public int $gameLength = 300,
		public bool $deathmatch = false,
		public int $deathmatchLength = 300,

		public int $rounds = 1,
		public int $betweenRoundTime = 10,
		public bool $newKitEachRound = false,

		public bool $itemDrops = false,
		public bool $canDropItems = false,
		public bool $canBreakArena = false,
		
		public int $totalHealth = 20,
		public bool $hideNametags = false,
		public int $pearlCooldown = 10,
		
		public bool $respawns = true,
		public bool $fixedSpawnpoint = false,
		public int $respawnTime = 3, //0 = instant
		public int $lives = 2,
		public int $killsNeeded = -1,

		public float $xpkr = 1.00,
		
		public int $gameLobbyCountdown = 15,
		public int $gameCountdown = 10,

		public int $maxTeams = 2,
		public int $minTeams = 2,
		public int $perTeam = 1 //only changed for team games!
	){}

	/**
	 * @return KitVoteLibrary
	 */
	public function getKitLibrary() : ?KitLibrary{
		return $this->kitLibrary;
	}
	
	public function hasDisplayName() : bool{
		return $this->displayName !== "";
	}
	
	public function getDisplayName() : string{
		return $this->displayName;
	}
	
	public function getStatTag() : string{
		return $this->statTag;
	}
	
	public function setStagTag(string $tag) : void{
		$this->statTag = $tag;
	}

	public function getGameLength() : int{
		return $this->gameLength;
	}

	public function setGameLength(int $length) : void{
		$this->gameLength = $length;
	}
	
	public function hasDeathmatch() : bool{
		return $this->deathmatch;
	}
	
	public function setDeathmatch(bool $dm) : void{
		$this->deathmatch = $dm;
	}
	
	public function getDeathmatchLength() : int{
		return $this->deathmatchLength;
	}
	
	public function setDeathmatchLength(int $length) : void{
		$this->deathmatchLength = $length;
	}

	public function getRounds() : int{
		return $this->rounds;
	}

	public function setRounds(int $rounds) : void{
		$this->rounds = $rounds;
	}

	public function hasRounds() : bool{
		return $this->getRounds() > 1;
	}
	
	public function getBetweenRoundTime() : int{
		return $this->betweenRoundTime;
	}
	
	public function setBetweenRoundTime(int $time) : void{
		$this->betweenRoundTime = $time;
	}
	
	public function hasNewKitEachRound() : bool{
		return $this->newKitEachRound;
	}
	
	public function setNewKitEachRound(bool $new) : void{
		$this->newKitEachRound = $new;
	}

	public function hasItemDrops() : bool{
		return $this->itemDrops;
	}

	public function setItemDrops(bool $drops) : void{
		$this->itemDrops = $drops;
	}

	public function canDropItems() : bool{
		return $this->canDropItems;
	}

	public function setCanDropItems(bool $drops) : void{
		$this->canDropItems = $drops;
	}
	
	public function canBreakArena() : bool{
		return $this->canBreakArena;
	}
	
	public function setCanBreakArena(bool $break) : void{
		$this->canBreakArena = $break;
	}
	
	public function getTotalHealth() : int{
		return $this->totalHealth;
	}
	
	public function setTotalHealth(int $health) : void{
		$this->totalHealth = $health;
	}
	
	public function hideNametags() : bool{
		return $this->hideNametags;
	}
	
	public function setHideNametags(bool $hide) : void{
		$this->hideNametags = $hide;
	}

	public function getPearlCooldown() : int{
		return $this->pearlCooldown;
	}

	public function setPearlCooldown(int $cooldown) : void{
		$this->pearlCooldown = $cooldown;
	}

	public function hasRespawns() : bool{
		return $this->respawns;
	}

	public function setRespawns(bool $respawn) : void{
		$this->respawns = $respawn;
	}
	
	public function hasFixedSpawnpoint() : bool{
		return $this->fixedSpawnpoint;
	}

	public function setFixedSpawnpoint(bool $fixed) : void{
		$this->fixedSpawnpoint = $fixed;
	}

	public function getRespawnTime() : int{
		return $this->respawnTime;
	}

	public function setRespawnTime(int $time) : void{
		$this->respawnTime = $time;
	}
	
	public function hasLives() : bool{
		return $this->getLives() !== -1;
	}

	public function getLives() : int{
		return $this->lives;
	}

	public function setLives(int $lives) : void{
		$this->lives = $lives;
	}

	public function getKillsNeeded() : int{
		return $this->killsNeeded;
	}

	public function setKillsNeeded(int $kills) : void{
		$this->killsNeeded = $kills;
	}

	public function getXpkr() : float{
		return $this->xpkr;
	}

	public function setXpkr(float $xpkr) : void{
		$this->xpkr = $xpkr;
	}
	
	public function getGameLobbyCountdown() : int{
		return $this->gameLobbyCountdown;
	}

	public function setGameLobbyCountdown(int $seconds) : void{
		$this->gameLobbyCountdown = $seconds;
	}

	public function getGameCountdown() : int{
		return $this->gameCountdown;
	}

	public function setGameCountdown(int $seconds) : void{
		$this->gameCountdown = $seconds;
	}

	public function getMaxPlayers() : int{
		return $this->maxTeams;
	}

	public function getMinPlayers() : int{
		return $this->minTeams;
	}

	/**
	 * use this function in TeamGame (save confusion)
	 */
	public function getMaxTeams() : int{
		return $this->getMaxPlayers();
	}

	public function getMinTeams() : int{
		return $this->getMinPlayers();
	}

	public function getPlayersPerTeam() : int{
		return $this->perTeam;
	}

	public function __clone(){
		$this->kitLibrary = clone $this->kitLibrary;
	}

}