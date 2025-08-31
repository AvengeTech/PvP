<?php namespace pvp;

use pocketmine\plugin\PluginBase;
use pocketmine\player\{
	Player,
	GameMode
};
use pocketmine\inventory\{
    ArmorInventory,
    CallbackInventoryListener,
	Inventory,
    PlayerInventory,
    PlayerOffHandInventory
};
use pocketmine\item\Item;
use pocketmine\entity\Location;
use pocketmine\Server;

use pvp\{

	arenas\Arenas,
	challenges\Challenges,
	combat\Combat,
	enchantments\Enchantments,
	games\Games,
	hotbar\Hotbar,
	hud\Hud,
	kits\Kits,
	leaderboards\Leaderboards,
	levels\Levels,
	tags\Tags,
	techits\Techits
};
use pvp\command\{
	SpawnCommand
};

use core\session\SessionManager;
use core\utils\TextFormat;
use pocketmine\data\bedrock\PotionTypeIdMap;
use pocketmine\data\bedrock\PotionTypeIds;
use pocketmine\data\SavedDataLoadingException;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\world\World;
use pvp\entity\HealthPot;

class PvP extends PluginBase{

	const SPAWN_WORLD = "spawn";
	const SPAWN_LOCATION = [0.5, 64, 0.5];

	public static ?self $instance = null;
	public array $databases = [];

	public SessionManager $sessionManager;

	public Arenas $arenas;
	public Challenges $challenges;
	public Combat $combat;
	public Enchantments $enchantments;
	public Games $games;
	public Hotbar $hotbar;
	public Hud $hud;
	public Kits $kits;
	public Leaderboards $leaderboards;
	public Levels $levels;
	public Tags $tags;
	public Techits $techits;

	public function onEnable() : void{
		self::$instance = $this;

		$keys = [
			1020 => "1",
			1021 => "test"
		];
		foreach(["global", "here"] as $name){
			$dbkey = $name;
			if($name == "here") $name = $keys[$this->getServer()->getPort()];

			$creds = array_merge(file("/[REDACTED]"), ["pvp_" . $name]);
			foreach($creds as $key => $cred) $creds[$key] = str_replace("\n", "", $cred);

			try{
				$this->databases[$dbkey] = new \mysqli(...$creds);
				foreach($this->databases as $db){
					$db->query("SET SESSION wait_timeout=2147483");
				}
				$this->getLogger()->notice("Successfully connected to pvp_" . $name . " database.");
			}catch(\Exception $e){
				$this->getLogger()->error("Database connection failed! Error: " . $e->getMessage());
				$this->getServer()->shutdown();
			}
		}

		$this->sessionManager = new SessionManager($this, PvPSession::class, "pvp_" . $keys[$this->getServer()->getPort()]);

		$this->challenges = new Challenges($this);
		$this->combat = new Combat($this);
		$this->enchantments = new Enchantments($this);
		$this->hotbar = new Hotbar($this);
		$this->hud = new Hud($this);

		$this->kits = new Kits($this);
		$this->arenas = new Arenas($this);
		$this->games = new Games($this);
		
		$this->leaderboards = new Leaderboards($this);
		$this->levels = new Levels($this);
		$this->tags = new Tags($this);
		$this->techits = new Techits($this);

		$this->getServer()->getWorldManager()->loadWorld(self::SPAWN_WORLD);
		$world = $this->getServer()->getWorldManager()->getWorldByName(self::SPAWN_WORLD);
		$world->setTime(6000);
		$world->stopTime();

		$this->getScheduler()->scheduleRepeatingTask(new TickTask($this), 1);
		$this->getServer()->getPluginManager()->registerEvents(new MainListener($this), $this);

		$this->getServer()->getCommandMap()->registerAll("skyblock", [
			new SpawnCommand($this, "spawn", "Teleport back to spawn!")
		]);

		EntityFactory::getInstance()->register(HealthPot::class, function (World $world, CompoundTag $nbt): HealthPot {
			$potionType = PotionTypeIdMap::getInstance()->fromId($nbt->getShort(HealthPot::TAG_POTION_ID, PotionTypeIds::WATER));
			if ($potionType === null) {
				throw new SavedDataLoadingException("No such potion type");
			}
			return new HealthPot(EntityDataHelper::parseLocation($nbt, $world), null, $potionType, $nbt);
		}, ['ThrownPotion', 'minecraft:potion', 'thrownpotion']);
	}

	public function onDisable() : void{
		$this->getCombat()->close();
		$this->getGames()->close();
		
		$this->getSessionManager()->close();
	}

	public function getSessionManager() : SessionManager{
		return $this->sessionManager;
	}

	public static function getInstance() : self{
		return self::$instance;
	}

	public function getDatabase(string $key = "here") : ?\mysqli{
		return $this->databases[$key] ?? null;
	}

	public function isTestServer() : bool{
		return $this->getServer()->getPort() == 1007;
	}
	
	public function getArenas() : Arenas{
		return $this->arenas;
	}

	public function getChallenges() : Challenges{
		return $this->challenges;
	}

	public function getCombat() : Combat{
		return $this->combat;
	}

	public function getEnchantments() : Enchantments{
		return $this->enchantments;
	}

	public function getGames() : Games{
		return $this->games;
	}

	public function getHotbar() : Hotbar{
		return $this->hotbar;
	}

	public function getHud() : Hud{
		return $this->hud;
	}

	public function getKits() : Kits{
		return $this->kits;
	}

	public function getLeaderboards() : Leaderboards{
		return $this->leaderboards;
	}

	public function getLevels() : Levels{
		return $this->levels;
	}

	public function getTags() : Tags{
		return $this->tags;
	}

	public function getTechits() : Techits{
		return $this->techits;
	}
	
	/**
	 * Called before session loads
	 */
	public function onPreJoin(Player $player) : void{
		$player->teleport(self::getSpawn());
	}

	/**
	 * Called after session loads
	 */
	public function onJoin(Player $player) : void{
		/** @var PvPPlayer $player */
		$this->getHud()->send($player);
		$this->getLeaderboards()->onJoin($player);

		$player->isTier3();

		$player->getArmorInventory()->getListeners()->add(new CallbackInventoryListener(function(Inventory $inventory, int $slot, Item $oldItem) : void{
			/** @var PlayerInventory|ArmorInventory|PlayerOffhandInventory $inventory */
			$this->getEnchantments()->calculateCache($inventory->getHolder());
		}, null));

		$player->getGameSession()->getCombat()->reset();
		$player->setHotbar("spawn");
		$player->setGameMode(GameMode::ADVENTURE());

		$player->sendMessage(TextFormat::EMOJI_CAUTION . TextFormat::YELLOW . " NOTE: This is a gamemode beta! Servers may be unstable and crash/shutdown for bug fixes at any time. Report new bugs to our Discord at avengetech.net/discord");
	}

	/**
	 * Called before session saves when player leaves
	 */
	public function onQuit(Player $player) : void {
		/** @var PvPPlayer $player */
		$cs = $player->getGameSession()?->getCombat();
		if($cs !== null){
			$mode = $cs->getCombatMode();
			if($mode->inCombat()){
				$mode->punish();
			}
		}

		$this->getLeaderboards()->onQuit($player);
		$this->getGames()->onQuit($player);
	}

	public static function getSpawn() : Location{
		return new Location(self::SPAWN_LOCATION[0], self::SPAWN_LOCATION[1], self::SPAWN_LOCATION[2], Server::getInstance()->getWorldManager()->getWorldByName(self::SPAWN_WORLD), 0, 0);
	}

}