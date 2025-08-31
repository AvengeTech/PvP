<?php namespace pvp\games\arena\loot;

use pocketmine\block\tile\Chest;
use pocketmine\math\Vector3;

use pvp\games\arena\ArenaInstance;

class LootChest{
	
	public Chest $chest;

	public function __construct(
		public Vector3 $pos,
		public int $tier
	){}

	public function getPosition() : Vector3{
		return $this->pos;
	}
	
	public function getChest(ArenaInstance $arena) : ?Chest{
		//var_dump($this->getPosition()->getX() . ":" . $this->getPosition()->getY() . ":" . $this->getPosition()->getZ());
		return $arena->getWorld()->getTile($this->getPosition());
	}

	public function getTier() : int{
		return $this->tier;
	}

	/*public function getLootPool() : LootPool{
		return $this->lootPool;
	}*/

	public function fill(LootPool $lootPool, ArenaInstance $arena) : void{
		$chest = $this->getChest($arena);
		if(!$chest instanceof Chest){
			var_dump("INVALID CHEST POSITION FOR LOOT ARENA " . $arena->getArena()->getName() . ": ");
			var_dump($this->getPosition());
		}else{
			$lootPool->fillChest($chest, $this->getTier());
		}
	}

}