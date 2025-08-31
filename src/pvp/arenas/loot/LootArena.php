<?php namespace pvp\arenas\loot;

use pvp\arenas\Arena;

class LootArena extends Arena{

	public ?LootPool $lootPool;
	public array $lootBoxes = [];

	public function tick() : void{
		parent::tick();

		foreach($this->getLootBoxes() as $lootBox){
			$lootBox->tick();
		}
	}

	public function getLootPool() : ?LootPool{
		return $this->lootPool;
	}

	public function setLootPool(LootPool $lootPool) : void{
		$this->lootPool = $lootPool;
	}

	public function getLootBoxes() : array{
		return $this->lootBoxes;
	}

	public function addLootBox(LootBox $lootBox){
		$this->lootBoxes[] = $lootBox;
	}

}