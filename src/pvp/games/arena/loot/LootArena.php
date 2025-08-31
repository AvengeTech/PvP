<?php namespace pvp\games\arena\loot;

use pocketmine\math\Vector3;

use pvp\games\arena\{
	Arena,
	ArenaInstance
};

class LootArena extends Arena{

	public int $lastFill = 0;
	
	public function __construct(
		string $id,
		string $name,
		string $worldName,
		array $spawnpoints,
		array $deathmatchSpawnpoints,
		Vector3 $center,
		array $games,
		public LootPool $lootPool,
		public array $lootChests = [],
		public int $refillInterval = -1
	){
		parent::__construct($id, $name, $worldName, $spawnpoints, $deathmatchSpawnpoints, $center, $games);
	}

	public function tick(?ArenaInstance $arenaInstance = null) : void{
		if(
			$this->getLastFill() !== 0 &&
			$this->getLastFill() > time() + $this->getRefillInterval()
		){
			$this->fillAll($arenaInstance);
			$viewers = $arenaInstance->getGame()?->getViewers() ?? [];
			foreach($viewers as $viewer){
				$viewer->sendMessage(TextFormat::GI . "All chests have been refilled!");
			}
		}
	}

	public function setup(ArenaInstance $arenaInstance) : void{
		$this->fillAll($arenaInstance);
	}
	
	public function getLootPool() : LootPool{
		return $this->lootPool;
	}
	
	public function getLootChests() : array{
		return $this->lootChests;
	}

	public function getRefillInterval() : int{
		return $this->refillInterval;
	}

	public function getLastFill() : int{
		return $this->lastFill;
	}

	public function setLastFill() : void{
		$this->lastFill = time();
	}

	public function fillAll(ArenaInstance $arenaInstance) : void{
		foreach($this->getLootChests() as $lootChest){
			$lootChest->fill($this->getLootPool(), $arenaInstance);
		}
		$this->setLastFill();
	}
	
}