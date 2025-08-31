<?php namespace pvp\arenas;

use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\World;

use pvp\PvP;
use pvp\arenas\command\ArenaCommand;
use pvp\kits\{
	KitLibrary
};
class Arenas{
	
	public array $arenas = [];
	
	public function __construct(public PvP $plugin){
		$plugin->getServer()->getCommandMap()->register("arenas", new ArenaCommand($plugin, "arena", "Access arenas"));
		$this->setupArenas();
	}

	public function tick() : void{
		foreach($this->getArenas() as $arena) $arena->tick();
	}

	public function setupArenas() : void{
		foreach(ArenaData::ARENAS as $id => $data){
			$kitnames = $data["kits"] ?? [];
			$kits = [];
			foreach($kitnames as $kitn){
				$kit = $this->plugin->getKits()->getKit($kitn);
				if($kit !== null) $kits[] = $kit;
			}
			$settings = $data["settings"] ?? [];
			$this->arenas[$id] = new Arena($id, ($data["locked"] ?? false), $data["name"], $data["world"], $this->setupPositions($data["spawnpoints"]), $data["corners"], new Vector3(...$data["center"]), $data["icon"], new KitLibrary($kits), new ArenaSettings(...$settings));
		}
	}

	public function setupPositions(array $positions) : array{
		foreach($positions as $key => $array){
			$positions[$key] = new Vector3(...$array);
		}
		return $positions;
	}


	public function getArenas() : array{
		return $this->arenas;
	}

	public function getArena(string $id) : ?Arena{
		return $this->arenas[$id] ?? null;
	}

	public function isArena(World $world) : bool{
		foreach($this->getArenas() as $arena){
			if($arena->getWorld() === $world) return true;
		}
		return false;
	}

	public function doArenaTick() : void{
		foreach($this->getArenas() as $arena){
			$arena->tick();
		}
	}

	public function inArena(Player $player) : bool{
		return $this->isArena($player->getWorld());
	}

	public function getPlayerArena(Player $player) : ?Arena{
		foreach($this->getArenas() as $arena){
			if($arena->getWorld() === $player->getWorld()) return $arena;
		}
		return null;
	}
	
}