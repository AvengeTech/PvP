<?php namespace pvp\games\lobby;

use pocketmine\Server;
use pocketmine\entity\Location;
use pocketmine\player\{
	GameMode,
	Player
};
use pocketmine\world\{
	World
};

use pvp\games\type\Game;

use core\Core;
use core\utils\Utils;
use pvp\PvPPlayer;

class GameLobbyInstance{

	const MAP_LOCATIONS = "/[REDACTED]/pvp/lobbies/";
	const SERVER_LOCATION = "/[REDACTED]/pvp/";

	public ?World $world = null;

	public ?Game $game = null;

	public function __construct(
		public GameLobby $gameLobby,
		public string $worldName
	){}

	public function create(?Game $game = null) : void{
		$ts = Core::thisServer();
		Utils::recursiveCopy(
			self::MAP_LOCATIONS . $this->getGameLobby()->getWorldName(),
			self::SERVER_LOCATION . ($ts->isSubServer() ? $ts->getSubId(true) : $ts->getTypeId()) . "/worlds/" . $this->getWorldName()
		);
		if(Server::getInstance()->getWorldManager()->loadWorld($this->getWorldName())){
			$this->world = Server::getInstance()->getWorldManager()->getWorldByName($this->getWorldName());
			$this->setGame($game);
		}else{
			$game?->end("Couldn't load lobby world :(");
		}
	}

	public function getGameLobby() : GameLobby{
		return $this->gameLobby;
	}

	public function getWorld() : ?World{
		return $this->world;
	}

	public function getWorldName() : string{
		return $this->worldName;
	}


	public function getSpawnpoint() : ?Location{
		return Location::fromObject($this->getGameLobby()->getSpawnpoint(), $this->getWorld());
	}

	public function teleportTo(Player $player, int $spawnpoint = -1) : void{
		/** @var PvPPlayer $player */
		$player->setFlightMode(false);
		if($player->getGamemode() === GameMode::SURVIVAL()){
			$player->setGamemode(GameMode::ADVENTURE());
		}

		$player->setHotbar("game_lobby"); //game lobby hotbar?
		$player->teleport($this->getSpawnpoint());
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