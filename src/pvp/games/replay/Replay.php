<?php namespace pvp\games\replay;

use pvp\games\arena\{
	Arena,
	ArenaInstance
};
use pvp\games\type\Game;

class Replay{

	public ArenaInstance $arenaInstance;

	public array $viewers = [];

	public function __construct(
		public string $id,
		public Game $game,
		public Arena $arena,
		public array $entities = [],
		public array $sequences = []
	){}

	public function tick() : void{

	}

	public function getId() : string{
		return $this->id;
	}

	public function getGame() : Game{
		return $this->game;
	}

	public function getArena() : Arena{
		return $this->arena;
	}

	public function createArenaInstance() : void{
		$this->arenaInstance = new ArenaInstance($this->getArena(), $this->getArena()->getName() . "_replay_" . $this->getId());
	}

	public function getArenaInstance() : ?ArenaInstance{
		return $this->arenaInstance;
	}

	public function getEntities() : array{
		return $this->entities;
	}

	public function getSequences() : array{
		return $this->sequences;
	}

	public function getViewers() : array{
		return $this->viewers;
	}

	public function start() : void{

	}

	public function end() : void{

	}

}