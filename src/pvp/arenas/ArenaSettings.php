<?php namespace pvp\arenas;

class ArenaSettings{

	public function __construct(
		public float $xpkr = 1.00,
		public bool $noDamage = false,
		public bool $antiInterrupt = false,
		public int $maxHealth = 20,
		public bool $building = false,
		public bool $killRegen = true,
		public int $pearlCooldown = 10
	){}

	public function getXpkr() : float{
		return $this->xpkr;
	}

	public function noDamage() : bool{
		return $this->noDamage;
	}

	public function antiInterrupt() : bool{
		return $this->antiInterrupt;
	}

	public function getMaxHealth() : int{
		return $this->maxHealth;
	}

	public function canBuild() : bool{
		return $this->building;
	}

	public function hasKillRegen() : bool{
		return $this->killRegen;
	}

	public function getPearlCooldown() : int{
		return $this->pearlCooldown;
	}

}