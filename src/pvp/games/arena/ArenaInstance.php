<?php namespace pvp\games\arena;

use pocketmine\Server;
use pocketmine\entity\Location;
use pocketmine\player\{
	GameMode,
	Player
};
use pocketmine\world\{
	Position,
	World
};

use pvp\PvPPlayer;
use pvp\games\type\Game;

use core\Core;
use core\utils\Utils;

class ArenaInstance{

	const MAP_LOCATIONS = "/[REDACTED]/pvp/maps/";
	const SERVER_LOCATION = "/[REDACTED]/pvp/";

	public ?World $world = null;
	
	public ?Game $game = null;
	
	public function __construct(
		public Arena $arena,
		public string $worldName
	){}

	public function tick() : void{
		$this->getArena()->tick($this);
	}

	public function create(?Game $game = null) : void{
		$ts = Core::thisServer();
		Utils::recursiveCopy(
			self::MAP_LOCATIONS . $this->getArena()->getWorldName(),
			self::SERVER_LOCATION . ($ts->isSubServer() ? $ts->getSubId(true) : $ts->getTypeId()) . "/worlds/" . $this->getWorldName()
		);
		if(Server::getInstance()->getWorldManager()->loadWorld($this->getWorldName())){
			$this->world = $world = Server::getInstance()->getWorldManager()->getWorldByName($this->getWorldName());
			$world->setTime(0);
			$world->save();
			$this->setGame($game);
			$this->getArena()->setup($this); //does final arena things, for stuff like loading chest loot
		}else{
			$game?->end("Couldn't load arena world :(");
		}
	}

	public function getArena() : Arena{
		return $this->arena;
	}

	public function getWorld() : ?World{
		return $this->world;
	}

	public function getWorldName() : string{
		return $this->worldName;
	}

	public function getSpawnpoints() : array{
		return $this->getArena()->getSpawnpoints();
	}

	public function getSpawnpoint(int $key) : ?Location{
		$spawn = $this->getSpawnpoints()[$key] ?? null;
		if($spawn !== null) return Location::fromObject($spawn, $this->getWorld());
		return null;
	}

	public function getRandomSpawn() : Location{
		$spawn = $this->getSpawnpoints()[mt_rand(0, count($this->getSpawnpoints()) - 1)];
		return Location::fromObject($spawn, $this->getWorld());
	}

	public function teleportTo(Player $player, int $spawnpoint = -1, bool $hotbar = true) : void{
		/** @var PvPPlayer $player */
		$player->setFlightMode(false);
		if($player->getGamemode() === GameMode::SURVIVAL()){
			$player->setGamemode(GameMode::ADVENTURE());
		}

		if($hotbar) $player->setHotbar("");
		$player->teleport($spawnpoint == -1 ? $this->getRandomSpawn() : ($this->getSpawnpoint($spawnpoint) ?? $this->getRandomSpawn()));
	}

	public function getDeathmatchSpawnpoints() : array{
		return $this->getArena()->getDeathmatchSpawnpoints();
	}

	public function getDeathmatchSpawnpoint(int $key) : ?Position{
		$spawn = $this->getArena()->getDeathmatchSpawnpoints()[$key] ?? null;
		if($spawn !== null) return Position::fromObject($spawn, $this->getWorld());
		return null;
	}
	
	public function getGame() : ?Game{
		return $this->game;
	}
	
	public function setGame(?Game $game) : void{
		$this->game = $game;
	}

	public function destroy() : void{
		if(($world = $this->getWorld()) !== null && $world->isLoaded()){
			Server::getInstance()->getWorldManager()->unloadWorld($world);
		}
		$ts = Core::thisServer();
		Utils::recursiveDelete(self::SERVER_LOCATION . ($ts->isSubServer() ? $ts->getSubId(true) : $ts->getTypeId()) . "/worlds/" . $this->getWorldName());
	}
	
}