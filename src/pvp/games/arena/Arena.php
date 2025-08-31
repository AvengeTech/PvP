<?php namespace pvp\games\arena;

use pocketmine\Server;
use pocketmine\player\{
	Player,
	GameMode
};
use pocketmine\world\{
	World,
	Position
};
use pocketmine\math\Vector3;

use pvp\games\type\Game;
use pvp\PvPPlayer;

class Arena{

	public Game $game;

	public function __construct(
		public string $id,
		public string $name,
		public string $worldName,
		public array $spawnpoints,
		public array $deathmatchSpawnpoints,
		public Vector3 $center,
		public array $games
	){}

	public function tick(?ArenaInstance $arenaInstance = null) : void{

	}
	
	public function setup(ArenaInstance $arenaInstance) : void{
		//probably mostly used in overrides (LootArena)
	}
	
	public function getId() : string{
		return $this->id;
	}
	
	public function getName() : string{
		return $this->name;
	}

	public function getGame() : Game{
		return $this->game;
	}

	public function getWorld() : ?World{
		return Server::getInstance()->getWorldManager()->getWorldByName($this->getWorldName());
	}

	public function getWorldName() : string{
		return $this->worldName;
	}

	public function getSpawnpoints() : array{
		return $this->spawnpoints;
	}

	public function getSpawnpoint(int $key) : ?Position{
		$spawn = $this->spawnpoints[$key] ?? null;
		if($spawn !== null) return Position::fromObject($spawn, $this->getWorld());
		return null;
	}

	public function getRandomSpawn() : Position{
		$spawn = $this->spawnpoints[mt_rand(0, count($this->spawnpoints) - 1)];
		return Position::fromObject($spawn, $this->getWorld());
	}

	public function teleportTo(Player $player, int $spawnpoint = 0){
		/** @var PvPPlayer $player */
		$player->setFlightMode(false);
		if($player->getGamemode() === GameMode::SURVIVAL()){
			$player->setGamemode(GameMode::ADVENTURE());
		}

		$player->setHotbar("");
		$player->teleport($spawnpoint == -1 ? $this->getRandomSpawn() : ($this->getSpawnpoint($spawnpoint) ?? $this->getRandomSpawn()));
	}

	public function getDeathmatchSpawnpoints() : array{
		return $this->deathmatchSpawnpoints;
	}

	public function getDeathmatchSpawnpoint(int $key) : ?Position{
		$spawn = $this->deathmatchSpawnpoints[$key] ?? null;
		if($spawn !== null) return Position::fromObject($spawn, $this->getWorld());
		return null;
	}

	public function getCenter() : Vector3{
		return $this->center;
	}
	
	public function getGames() : array{
		return $this->games;
	}
	
	public function compatibleWith(Game $game) : bool{
		return
			in_array($game->getName(), $this->getGames()) ||
			in_array($game->getName() . "_" . $game->getSettings()->getStatTag(), $this->getGames());
	}

}