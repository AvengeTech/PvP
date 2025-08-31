<?php namespace pvp\games;

use pocketmine\block\VanillaBlocks;
use pocketmine\entity\Location;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;

use pvp\games\arena\{
	Arena,
	ArenaData
};
use pvp\games\arena\loot\{
	LootArena,
	LootChest,
	LootPool
};
use pvp\games\type\{
	Game,
	GameSettings,

	Duel,
	BotDuel,

	SuddenDeath,
	OITQ,
	SkyWars
};
use pvp\games\lobby\{
	GameLobby,
	GameLobbyData
};
use pvp\item\HealthPotItem;
use pvp\kits\{
	KitLibrary,
};

class GameManager{

	public static ?self $instance = null;
	public static int $handlerId = 0;

	public array $lootPools = [];

	public array $arenas = [];
	public array $gameLobbies = [];

	public array $gameHandlers = [];

	public function __construct(){
		self::$instance = $this;

		$this->setupLootPools();

		$this->setupArenas();
		$this->setupGameLobbies();

		$this->addGameHandler(new GameHandler(Duel::class, new GameSettings(
			new KitLibrary(["nodebuff"], "nodebuff"), "Duels", "classic",
			300, false, 300,
			1, 5, false,
			false, false, false,
			20, false, 10,
			false, false, 0, -1, 20, //respawn
			1,
			15, 5
		)));
		$this->addGameHandler(new GameHandler(SuddenDeath::class, new GameSettings(
			new KitLibrary(["sd"], "sd"), "Sudden Death", "classic",
			180, false, 300,
			3, 5, false,
			false, false, false,
			2, true, 10,
			false, false, 0, -1, 20,
			0.6,
			10, //game lobby
			10,
			4, 2
		)));
		$this->addGameHandler(new GameHandler(OITQ::class, new GameSettings(
			new KitLibrary(["oitq"], "oitq"), "OITQ", "classic",
			600, false, 300,
			1, 10, false,
			false, false, false,
			20, false, 10,
			true, false, 0, -1, 20,
			0.8,
			15, //game lobby
			10,
			12, 6
		)));

		$this->addGameHandler(new GameHandler(SkyWars::class, new GameSettings(
			new KitLibrary(["sw_basic"], "basic"), "SkyWars (Mini)", "small", //todo: kits
			600, false, 300, //todo: boom phase with 2 minutes left
			1, 10, false,
			true, true, true,
			20, false, 10,
			false, false, 0, -1, 20,
			1,
			15, 5, //game lobby
			4, 3
		)));
		$this->addGameHandler(new GameHandler(SkyWars::class, new GameSettings(
			new KitLibrary(["sw_basic"], "basic"), "SkyWars (Normal)", "normal", //todo: kits
			600, false, 300,
			1, 10, false,
			true, true, true,
			20, false, 10,
			false, false, 0, -1, 20,
			1,
			15, 5, //game lobby
			8, 4
		)));
		$this->addGameHandler(new GameHandler(SkyWars::class, new GameSettings(
			new KitLibrary(["sw_basic"], "basic"), "SkyWars (Large)", "large", //todo: kits
			600, false, 300,
			1, 10, false,
			true, true, true,
			20, false, 10,
			false, false, 0, -1, 20,
			1,
			15, 5, //game lobby
			12, 6
		)));

		$this->addGameHandler(new GameHandler(BotDuel::class, new GameSettings(
			new KitLibrary(["nodebuff"], "nodebuff"), "Bot Duels", "classic", //kit/name/stat tag
			300, false, 300, //time / deathmatch
			1, 10, false, //rounds
			false, false, false, //item drops
			20, false, 10, //health / hide nametags / pearl cooldown
			false, false, 0, -1, 20, //respawn
			0, //xpkr
			15, 5, //game lobby
			1, 1, //players
		)));
	}

	public static function getInstance() : self{
		return self::$instance;
	}

	public function tick() : void{
		foreach($this->getGameHandlers() as $handler){
			$handler->tick();
		}
	}

	public function close() : void{
		foreach($this->getGameHandlers() as $handler){
			$handler->close();
		}
	}

	public function setupLootPools() : void{
		$this->lootPools["basic"] = new LootPool("basic",
			[
				VanillaItems::STONE_SWORD(),
				VanillaItems::STONE_AXE(),
				VanillaItems::STONE_AXE(),
				VanillaItems::GOLDEN_AXE(),
				VanillaItems::GOLDEN_SWORD(),
				VanillaItems::ARROW()->setCount(4),
				VanillaItems::ARROW()->setCount(8),
				VanillaItems::BOW(),

				//VanillaItems::LEATHER_CAP(),
				//VanillaItems::LEATHER_TUNIC(),
				VanillaItems::LEATHER_PANTS(),
				VanillaItems::LEATHER_BOOTS(),

				//VanillaItems::CHAINMAIL_HELMET(),
				//VanillaItems::CHAINMAIL_CHESTPLATE(),
				VanillaItems::CHAINMAIL_LEGGINGS(),
				VanillaItems::CHAINMAIL_BOOTS(),

				//VanillaItems::GOLDEN_HELMET(),
				//VanillaItems::GOLDEN_CHESTPLATE(),
				VanillaItems::GOLDEN_LEGGINGS(),
				VanillaItems::GOLDEN_BOOTS(),

				VanillaBlocks::COBBLESTONE()->asItem()->setCount(16),
				VanillaBlocks::COBBLESTONE()->asItem()->setCount(16),
				VanillaBlocks::COBBLESTONE()->asItem()->setCount(32),
				VanillaBlocks::SANDSTONE()->asItem()->setCount(16),
				VanillaBlocks::SANDSTONE()->asItem()->setCount(32),
			],
			[
				VanillaItems::IRON_SWORD(),
				VanillaItems::IRON_AXE(),
				VanillaItems::GOLDEN_APPLE(),
				VanillaBlocks::CAKE()->asItem(),
				VanillaItems::ENDER_PEARL()->setCount(3),
				new HealthPotItem(),
				VanillaItems::LAVA_BUCKET(),
				VanillaItems::WATER_BUCKET(),

				VanillaItems::IRON_HELMET(),
				VanillaItems::IRON_CHESTPLATE(),
				VanillaItems::IRON_LEGGINGS(),
				VanillaItems::IRON_BOOTS(),
			],
			[
				VanillaItems::DIAMOND_SWORD(),
				VanillaItems::DIAMOND_AXE(),

				VanillaItems::GOLDEN_APPLE()->setCount(2),

				VanillaItems::DIAMOND_HELMET(),
				VanillaItems::DIAMOND_CHESTPLATE(),
				VanillaItems::DIAMOND_LEGGINGS(),
				VanillaItems::DIAMOND_BOOTS(),
			],
		);
	}

	public function setupArenas() : void{
		foreach(ArenaData::ARENAS as $id => $data){
			$spawnpoints = [];
			foreach($data["spawnpoints"] as $spawnpoint){
				$sp = explode(",", $spawnpoint);
				if(count($sp) === 3){
					$spawnpoints[] = new Vector3(...$sp);
				}else{
					$spawnpoints[] = new Location($sp[0], $sp[1], $sp[2], null, $sp[4] ?? 0, $sp[5] ?? 0);
				}
			}

			$deathmatchSpawnpoints = [];
			foreach(($data["deathmatch"] ?? []) as $dm){
				$sp = explode(",", $dm);
				if(count($sp) === 3){
					$deathmatchSpawnpoints[] = new Vector3(...$sp);
				}else{
					$deathmatchSpawnpoints[] = new Location($sp[0], $sp[1], $sp[2], null, $sp[4] ?? 0, $sp[5] ?? 0);
				}
			}

			$center = new Vector3(...explode(",", $data["center"]));
			if(isset($data["lootPool"])){
				$lootChests = [];
				foreach($data["loot"] as $lootChest){
					$lcd = explode(",", $lootChest);
					//var_dump($data["name"]);
					//var_dump($lcd);
					$lootChests[] = new LootChest(new Vector3((float) $lcd[0], (float) $lcd[1], (float) $lcd[2]), $lcd[3]);
				}
				$this->arenas[$id] = new LootArena(
					$id,
					$data["name"],
					$data["world"],
					$spawnpoints,
					$deathmatchSpawnpoints,
					$center,
					$data["games"],
					$this->getLootPool($data["lootPool"]),
					$lootChests,
					$data["lootRefillInterval"] ?? -1
				);
			}else{
				$this->arenas[$id] = new Arena(
					$id,
					$data["name"],
					$data["world"],
					$spawnpoints,
					$deathmatchSpawnpoints,
					$center,
					$data["games"],
				);
			}
		}
	}

	public function setupGameLobbies() : void{
		foreach(GameLobbyData::LOBBIES as $id => $data){
			$sp = explode(",", $data["spawnpoint"]);
			if(count($sp) === 3){
				$spawnpoint = new Vector3(...$sp);
			}else{
				$spawnpoint = new Location($sp[0], $sp[1], $sp[2], null, $sp[4] ?? 0, $sp[5] ?? 0);
			}

			$this->gameLobbies[$id] = new GameLobby(
				$id,
				$data["name"],
				$data["world"],
				$spawnpoint
			);
		}
	}

	public function getLootPools() : array{
		return $this->lootPools;
	}

	public function getLootPool(string $name) : ?LootPool{
		return $this->lootPools[$name] ?? null;
	}

	public function getArenas() : array{
		return $this->arenas;
	}

	/**
	 * Only used on GameHandler initialization
	 */
	public function getCompatibleArenas(Game $game) : array{
		$arenas = [];
		foreach($this->getArenas() as $arena){
			if($arena->compatibleWith($game)) $arenas[$arena->getId()] = $arena;
		}
		return $arenas;
	}

	public function getGameLobbies() : array{
		return $this->gameLobbies;
	}

	public function getRandomGameLobby() : GameLobby{
		$lobbies = array_values($this->getGameLobbies());
		return $lobbies[mt_rand(0, count($lobbies) - 1)];
	}

	public function getGameHandlers() : array{
		return $this->gameHandlers;
	}

	public function getHandlerByGame(Game $game) : ?GameHandler{
		return $this->gameHandlers[$game->getHandler()->getId()];
	}

	public function getHandler(GameHandler $handler) : GameHandler{
		return $this->gameHandlers[$handler->getId()];
	}

	public function getHandlerBy(string $gameName, string $statTag) : ?GameHandler{
		foreach($this->gameHandlers as $id => $handler){
			if(
				$handler->getBaseGame()->getName() === $gameName &&
				$handler->getGameSettings()->getStatTag() === $statTag
			){
				return $handler;
			}
		}
		return null;
	}

	public function getHandlersBy(string $gameName) : array{
		$handlers = [];
		foreach($this->gameHandlers as $id => $handler){
			if($handler->getBaseGame()->getName() === $gameName){
				$handlers[] = $handler;
			}
		}
		return $handlers;
	}

	public function addGameHandler(GameHandler $handler) : void{
		$this->gameHandlers[$handler->getId()] = $handler;
	}

}
