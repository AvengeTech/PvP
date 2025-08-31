<?php namespace pvp\games;

use pocketmine\math\Vector3;

use pvp\games\arena\{
	Arena,
	ArenaInstance
};
use pvp\games\lobby\GameLobbyInstance;
use pvp\games\type\{
	Game,
	GameSettings
};

class GameHandler{

	public int $id;
	
	public static int $gameId = 0;
	public int $ticks = 0;

	public Game $baseGame;
	
	public array $arenas = [];
	
	public array $availableGames = [];
	public array $games = [];
	
	public function __construct(
		public string $gameClass,
		public GameSettings $gameSettings
	){
		$this->id = GameManager::$handlerId++;
		$gc = $this->baseGame = new $gameClass($this, -1, $this->gameSettings);
		$this->arenas = GameManager::getInstance()->getCompatibleArenas($gc);

		$game = $this->createNewGame();
		$this->availableGames[$game->getId()] = $game;
	}

	public function getId() : int{
		return $this->id;
	}
	
	public function tick() : void{
		$this->ticks++;
		foreach($this->getAvailableGames() as $game){
			$game->tick();
		}
		foreach($this->getGames() as $game){
			$game->tick();
		}
	}

	public function close() : void{
		foreach($this->getGames() as $game){
			$game->end("Server restarting, sorry!");
		}
	}

	public static function newGameId() : int{
		return self::$gameId++;
	}

	public function createNewGame(array $players = [], ?Arena $arena = null, ?GameSettings $gameSettings = null) : Game{
		$gameClass = $this->getGameClass();
		$game = new $gameClass($this, $id = self::newGameId(), $gameSettings = $gameSettings ?? clone $this->gameSettings, $players);
		
		$gameLobby = GameManager::getInstance()->getRandomGameLobby();
		$gli = new GameLobbyInstance($gameLobby, $gameLobby->getWorldName() . "_" . $this->getBaseGame()->getName() . "_" . $id);
		$gli->create($game);
		$game->setGameLobby($gli);
		
		return $game;
	}

	public function getGameClass() : string{
		return $this->gameClass;
	}

	public function getBaseGame() : Game{
		return $this->baseGame;
	}
	
	public function getTestArena() : Arena{
		return new Arena("test", "test", "gametestarena", [
			new Vector3(267.5, 73, 256.5),
			new Vector3(256.5, 73, 267.5),
			new Vector3(245.5, 73, 256.5),
			new Vector3(256.5, 73, 245.5),
		], [], new Vector3(256.5, 74, 256.5), ["test"]);
	}
	
	public function getArenas() : array{
		return $this->arenas;
	}

	public function getArena(string $id) : ?Arena{
		return $this->arenas[$id] ?? null;
	}
	
	public function getRandomArena(array $chosenAlready = []) : Arena{
		$arena = $this->arenas[array_rand($this->arenas)] ?? $this->getTestArena();
		if(in_array($arena, $chosenAlready, true)){
			return $this->getRandomArena($chosenAlready);
		}
		return $arena;
	}

	public function getAvailableGames() : array{
		return $this->availableGames;
	}

	public function getAvailableGame() : Game{
		return $this->availableGames[array_rand($this->availableGames)];
	}

	public function getAvailableGameBy(Game $game) : ?Game{
		return $this->availableGames[$game->getId()];
	}

	public function inAvailableGames(Game $game) : bool{
		return isset($this->availableGames[$game->getId()]);
	}

	public function removeAvailableGame(Game $game) : void{
		$this->games[$game->getId()] = $game;
		unset($this->availableGames[$game->getId()]);
		if(count($this->availableGames) === 0){
			$game = $this->createNewGame();
			$this->availableGames[$game->getId()] = $game;
		}
	}

	public function makeGameAvailable(Game $game) : void{
		unset($this->games[$game->getId()]);
		$this->availableGames[$game->getId()] = $game;
	}
	
	public function getGames() : array{
		return $this->games;
	}
	
	public function getGame(int $id) : ?Game{
		return $this->games[$id] ?? null;
	}

	public function getTotalPlayers() : int{
		$total = 0;
		foreach($this->getAvailableGames() as $game) $total += count($game->getPlayers());
		foreach($this->getGames() as $game) $total += count($game->getPlayers());
		return $total;
	}
	
	public function getGameSettings() : GameSettings{
		return $this->gameSettings;
	}
	
}