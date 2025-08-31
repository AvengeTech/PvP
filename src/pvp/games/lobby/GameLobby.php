<?php namespace pvp\games\lobby;

use pocketmine\Server;
use pocketmine\world\{
	World,
};
use pocketmine\math\Vector3;

class GameLobby{

	public function __construct(
		public string $id,
		public string $name,
		public string $worldName,
		public Vector3 $spawnpoint
	){}

	public function getId() : string{
		return $this->id;
	}

	public function getName() : string{
		return $this->name;
	}

	public function getWorld() : ?World{
		return Server::getInstance()->getWorldManager()->getWorldByName($this->getWorldName());
	}

	public function getWorldName() : string{
		return $this->worldName;
	}

	public function getSpawnpoint() : Vector3{
		return $this->spawnpoint;
	}

}