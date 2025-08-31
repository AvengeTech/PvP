<?php namespace pvp\arenas;

use pocketmine\Server;
use pocketmine\block\Block;
use pocketmine\player\{
	Player,
	GameMode
};
use pocketmine\world\{
	World,
	Position
};
use pocketmine\entity\effect\{
	EffectInstance,
	VanillaEffects
};
use pocketmine\math\Vector3;

use pvp\PvPPlayer;
use pvp\kits\KitLibrary;

use core\Core;
use core\scoreboards\ScoreboardObject;
use core\utils\TextFormat;

class Arena{

	public int $ticks = 0;

	/** Used for blocks when build enabled */
	public array $blocks = [];
	public array $pearlCooldowns = [];
	
	public array $scoreboards = [];
	public array $lines = [];

	public function __construct(
		public string $id,
		public bool $locked,

		public string $name,

		public string $worldName,

		public array $spawnpoints,
		public array $corners,
		public Vector3 $center,

		public ?string $icon,
		public ?KitLibrary $kitLibrary,
		public ArenaSettings $settings
	){
		$this->icon ??= "";
		Server::getInstance()->getWorldManager()->loadWorld($worldName, true);
		$this->getWorld()->setTime(0);
		$this->getWorld()->stopTime();
		
		$this->lines = [
			0 => TextFormat::EMOJI_CONTROLLER . TextFormat::GRAY . " Arena: " . TextFormat::YELLOW . $this->getName(),
			1 => TextFormat::GRAY . "Uptime: ",
			2 => " ",
			3 => TextFormat::EMOJI_ARROW_RIGHT . TextFormat::GRAY . " K/D/R: ",
			4 => "  ",
			5 => TextFormat::EMOJI_SPARKLES . TextFormat::GRAY . " Combo: ",
			6 => TextFormat::EMOJI_SKULL . TextFormat::GRAY . " Streak: ",
		];

		if(Core::getInstance()->getNetwork()->getServerManager()->getThisServer()->isSubServer())
			$this->ticks = -1;
	}

	public function tick() : void{
		if($this->ticks === -1) return;

		$this->ticks++;
		foreach($this->getBlocks() as $key => $block){
			if($block->tick()){
				unset($this->blocks[$key]);
			}
		}
		$this->updateScoreboardLines();
	}

	public function getId() : string{
		return $this->id;
	}

	public function isLocked() : bool{
		return $this->locked;
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

	public function getSpawnpoints() : array{
		return $this->spawnpoints;
	}

	public function getCorners() : array{
		return $this->corners;
	}

	public function getSettings() : ArenaSettings{
		return $this->settings;
	}

	public function isInBorder(Player $player) : bool{
		$corners = $this->getCorners();
		$x = $player->getPosition()->getX();
		$z = $player->getPosition()->getZ();
		return $x >= $corners[0][0] && $x <= $corners[1][0] && $z >= $corners[0][1] && $z <= $corners[1][1];
	}

	public function goBack(Player $player) : void{
		$player->sendTip(TextFormat::RED . "Do NOT attempt to leave the arena!!");
		$this->teleportTo($player);
		$player->getEffects()->add(new EffectInstance(VanillaEffects::POISON(), 20 * 15, 1));
	}

	public function getRandomSpawn() : Position{
		$spawn = $this->spawnpoints[mt_rand(0, count($this->spawnpoints) - 1)];
		return new Position($spawn->getX(), $spawn->getY(), $spawn->getZ(), $this->getWorld());
	}

	public function teleportTo(Player $player, bool $spectator = false){
		/** @var PvPPlayer $player */
		$player->setFlightMode(false);
		if(!$this->getSettings()->canBuild() && $player->getGamemode() !== GameMode::CREATIVE()){
			$player->setGamemode(GameMode::ADVENTURE());
		}else{
			$player->setGamemode(GameMode::SURVIVAL());
		}

		if($player->getWorld() !== $this->getWorld()){
			$player->getGameSession()->getCombat()->setInvincible();
		}

		if(($as = $player->getGameSession()->getArenas())->getArena() !== $this){
			$as->setArena($this, $spectator);
			$as->resetCurrentCombo();
			$as->resetCurrentStreak();
			$this->addScoreboard($player);
		}
		$player->setHotbar("", false);
		$player->teleport($this->getRandomSpawn());
		if($spectator){
			$player->setHotbar("arena_spectator");
			$player->setGameMode(GameMode::SPECTATOR());
			foreach($this->getWorld()->getPlayers() as $pl){
				$player->despawnFrom($pl);
			}
		}
	}

	public function getCenter() : Vector3{
		return $this->center;
	}

	public function getPlayers() : array{
		if($this->getWorld() === null) return [];

		$players = [];
		foreach (Server::getInstance()->getOnlinePlayers() as $player) {
			/** @var PvPPlayer $player */
			if($player->isLoaded() && ($as = $player->getGameSession()->getArenas())->inArena() && $as->getArena() === $this){
				$players[] = $player;
			}
		}
		return $players; //maybe more efficient way to do this?
	}

	public function getIcon() : string{
		return $this->icon;
	}

	public function getKitLibrary() : ?KitLibrary{
		return $this->kitLibrary;
	}

	public function getBlocks() : array{
		return $this->blocks;
	}

	public function addPlacedBlock(Block $block) : void{
		$key = ($pos = $block->getPosition())->getX() . ":" . $pos->getY() . ":" . $pos->getZ();
		$this->blocks[$key] = new BlockState($this, $block);
	}

	public function removePlacedBlock(Block $block) : void{
		$key = ($pos = $block->getPosition())->getX() . ":" . $pos->getY() . ":" . $pos->getZ();
		unset($this->blocks[$key]);
	}

	public function isPlacedBlock(Block $block) : bool{
		$key = ($pos = $block->getPosition())->getX() . ":" . $pos->getY() . ":" . $pos->getZ();
		return isset($this->blocks[$key]);
	}

	public function onPearlCooldown(Player $player) : bool{
		return isset($this->pearlCooldowns[$player->getName()]) && $this->pearlCooldowns[$player->getName()] > time();
	}

	public function getPearlCooldown(Player $player) : int{
		return ($this->pearlCooldowns[$player->getName()] ?? time()) - time();
	}

	public function setPearlCooldown(Player $player) : void{
		$this->pearlCooldowns[$player->getName()] = time() + $this->getSettings()->getPearlCooldown();
	}

	public function resetPearlCooldown(Player $player) : void{
		unset($this->pearlCooldowns[$player->getName()]);
	}

	/* Scoreboard */
	public function updateScoreboardLines() : void{
		$network = Core::getInstance()->getNetwork();
		$seconds = $network->getUptime();
		$hours = floor($seconds / 3600);
		$minutes = floor(((int) ($seconds / 60)) % 60);
		$seconds = $seconds % 60;
		if(strlen((string) $hours) == 1) $hours = "0" . $hours;
		if(strlen((string) $minutes) == 1) $minutes = "0" . $minutes;
		if(strlen((string) $seconds) == 1) $seconds = "0" . $seconds;
		$left = $network->getRestartTime() - time();
		$this->lines[1] = TextFormat::GRAY . "Uptime: " . TextFormat::RED . $hours . TextFormat::GRAY . ":" . TextFormat::RED . $minutes . TextFormat::GRAY . ":" . TextFormat::RED . $seconds . " " . ($seconds %3 == 0 ? TextFormat::EMOJI_HOURGLASS_EMPTY : TextFormat::EMOJI_HOURGLASS_FULL) . " " . ($left <= 60 ? ($seconds %2 == 0 ? TextFormat::EMOJI_CAUTION : "") : "");

		ksort($this->lines);
		$this->updateAllScoreboards();
	}

	public function getLines() : array{
		return $this->lines;
	}

	public function getLinesFor(Player $player): array {
		/** @var PvPPlayer $player */
		$lines = $this->getLines();

		$session = $player->getGameSession()?->getArenas();
		if($session === null) return $lines;

		$lines[3] = TextFormat::EMOJI_ARROW_RIGHT . TextFormat::GRAY . " K/D/R: " . TextFormat::GREEN . ($k = $session->getKills($this)) . TextFormat::GRAY . "/" . TextFormat::RED . ($d = $session->getDeaths($this)) . TextFormat::GRAY . "/" . TextFormat::YELLOW . ($d == 0 ? "N" : round($k / $d, 2));
		$lines[5] = TextFormat::EMOJI_SPARKLES . TextFormat::GRAY . " Combo: " . TextFormat::AQUA . $session->getCurrentCombo() . TextFormat::GRAY . " (" . $session->getCombo($this) . ")";
		$lines[6] = TextFormat::EMOJI_SKULL . TextFormat::GRAY . " Streak: " . TextFormat::RED . $session->getCurrentStreak() . TextFormat::GRAY . " (" . $session->getStreak($this) . ")";

		ksort($lines);
		return $lines;
	}

	public function getScoreboards() : array{
		return $this->scoreboards;
	}

	public function getScoreboard(Player $player) : ?ScoreboardObject{
		return $this->scoreboards[$player->getXuid()] ?? null;
	}

	public function addScoreboard(Player $player, bool $removeOld = true) : void{
		if($removeOld){
			Core::getInstance()->getScoreboards()->removeScoreboard($player, true);
		}

		$scoreboard = $this->scoreboards[$player->getXuid()] = new ScoreboardObject($player);
		$scoreboard->send($this->getLines());
	}

	public function removeScoreboard(Player $player, bool $addOld = true) : void{
		$scoreboard = $this->getScoreboard($player);
		if($scoreboard !== null){
			unset($this->scoreboards[$player->getXuid()]);
			$scoreboard->remove();
		}
		if($addOld){
			Core::getInstance()->getScoreboards()->addScoreboard($player);
		}
	}

	public function removeAllScoreboards() : void{
		foreach($this->scoreboards as $xuid => $sb){
			if(($pl = $sb->getPlayer()) instanceof Player){
				$sb->remove();
				Core::getInstance()->getScoreboards()->addScoreboard($pl);
			}
			unset($this->scoreboards[$xuid]);
		}
	}

	public function updateAllScoreboards() : void{
		foreach($this->scoreboards as $xuid => $sb){
			if($sb->getPlayer() instanceof Player) $sb->update($this->getLinesFor($sb->getPlayer()));
		}
	}

}