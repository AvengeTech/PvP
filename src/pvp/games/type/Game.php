<?php namespace pvp\games\type;

use pocketmine\Server;
use pocketmine\block\{
	Block,
	VanillaBlocks
};
use pocketmine\player\{
	GameMode,
	Player
};
use pocketmine\world\Position;
use pocketmine\world\sound\{
	ClickSound
};

use pvp\kits\{
	Kit,
	KitLibrary,
	KitVoteLibrary
};
use pvp\games\{
	GameHandler,
	GameManager
};
use pvp\games\arena\{
	ArenaInstance,
	ArenaVote
};
use pvp\games\stat\Scorekeeper;
use pvp\games\lobby\GameLobbyInstance;
use pvp\games\ui\{
	KitSelectUi,
	KitVoteUi
};

use core\Core;
use core\scoreboards\ScoreboardObject;
use core\utils\TextFormat;
use pvp\PvPPlayer;

class Game{

	const GAME_WAITING = 0;

	const GAME_LOBBY_COUNTDOWN = 1;
	const GAME_COUNTDOWN = 2;

	const GAME_START = 3;

	const GAME_DEATHMATCH_COUNTDOWN = 8;
	const GAME_DEATHMATCH = 9;

	const GAME_END = 10;

	public ?GameLobbyInstance $gameLobby = null;

	public ArenaVote $arenaVote;
	public ?ArenaInstance $arena = null;
	public array $placedBlocks = [];

	public array $players = [];
	public array $left = []; //player stats of those who left?

	public array $eliminated = [];

	public int $round = 1;
	public array $rounds = []; //round scores

	public array $spectators = [];
	public array $respawning = [];

	public array $pearlCooldowns = [];

	public array $scoreboards = [];
	public array $lines = [];

	public int $ticks = 0;

	public int $status = self::GAME_WAITING;
	public int $timer = 0;

	public int $timeStarted = 0;


	public function __construct(
		public GameHandler $handler,

		public int $id,
		public GameSettings $settings,
		array $players = []
	){
		$this->arenaVote = new ArenaVote($this);
		foreach($players as $player){
			$this->addPlayer($player);
		}

		$this->lines = [
			1 => TextFormat::EMOJI_CONTROLLER . " " . TextFormat::YELLOW . $this->getSettings()->getDisplayName(),
			2 => " ",
			3 => TextFormat::EMOJI_ARROW_RIGHT . TextFormat::AQUA . " Players: " . TextFormat::YELLOW . count($this->getPlayers()) . "/" . $settings->getMaxTeams(),
			4 => "  ",
			5 => "   ",
			6 => TextFormat::EMOJI_BELL . TextFormat::AQUA . "Status: " . $this->getStatusNameColored(),
			7 => TextFormat::EMOJI_SPARKLES . TextFormat::RED . $this->getTimerValueFormatted(),
		];
	}

	public function getHandler() : GameHandler{
		return $this->handler;
	}

	public function getName() : string{
		return "game";
	}

	public function getInstructions() : string{
		return "Last player alive wins!";
	}

	/**
	 * For sessions / other stuff maybe
	 */
	public function getStatKey() : string{
		return $this->getName() . "_" . $this->getSettings()->getStatTag();
	}

	public function getGameLobby() : ?GameLobbyInstance{
		return $this->gameLobby;
	}

	public function hasGameLobby() : bool{
		return $this->gameLobby !== null;
	}

	public function setGameLobby(GameLobbyInstance $gameLobby) : void{
		$this->gameLobby = $gameLobby;
	}

	public function getArenaVote() : ArenaVote{
		return $this->arenaVote;
	}

	public function getArena() : ?ArenaInstance{
		return $this->arena;
	}

	public function hasArena() : bool{
		return $this->arena !== null;
	}

	public function setArena(?ArenaInstance $arena = null) : void{
		$this->arena = $arena;
	}

	/**
	 * Used to track blocks placed (so players can't break arenas)
	 */
	public function getPlacedBlocks() : array{
		return $this->placedBlocks;
	}

	public function getPosKey(Position $pos) : string{
		return $pos->getX() . ":" . $pos->getY() . ":" . $pos->getZ();
	}

	public function wasBlockPlaced(Block $block) : bool{
		return isset($this->placedBlocks[$this->getPosKey($block->getPosition())]);
	}

	public function addPlacedBlock(Block $block) : void{
		$this->placedBlocks[$this->getPosKey($block->getPosition())] = $block->getName();
	}

	public function removePlacedBlock(Block $block) : void{
		unset($this->placedBlocks[$this->getPosKey($block->getPosition())]);
	}

	public function getId() : int{
		return $this->id;
	}

	public function getSettings() : GameSettings{
		return $this->settings;
	}

	public function setSettings(GameSettings $gameSettings) : void{
		$this->settings = $gameSettings;
	}

	public function addPlayer(Player $player) : void{
		/** @var PvPPlayer $player */
		$gs = $player->getGameSession()->getGame();
		if($gs->inGame() && $gs->getGame() !== $this){
			$gs->getGame()->removePlayer($player);
		}
		$gs->setGame($this);

		$this->getGameLobby()->teleportTo($player);
		$player->setHotbar("game_lobby");

		$this->players[$player->getName()] = new Scorekeeper($this->getSettings(), $player);

		if(
			count($this->getPlayers()) >= $this->getSettings()->getMaxTeams() &&
			$this->getHandler()->inAvailableGames($this)
		){
			$this->getHandler()->removeAvailableGame($this);
		}

		$this->addScoreboard($player);
	}

	public function getPlayer(Player $player) : ?Scorekeeper{
		return $this->players[$player->getName()] ?? null;
	}

	public function getPlayers() : array{
		return $this->players;
	}

	public function hasPlayer(Player $player) : bool{
		return isset($this->players[$player->getName()]);
	}

	public function removePlayer(Player $player, bool $gotoSpawn = true, bool $left = false): void {
		/** @var PvPPlayer $player */
		//todo: stats? add loss or some shit
		if($left){
			$this->left[$player->getName()] = $this->players[$player->getName()];
		}
		unset($this->players[$player->getName()]);

		$gs = $player->getGameSession()->getGame();
		$gs->setGame();

		$this->removeScoreboard($player);

		$player->setMaxHealth(20);
		$player->setHealth(20);
		$player->getHungerManager()->setFood(20);
		$player->getHungerManager()->setSaturation(20);

		if($gotoSpawn) $player->gotoSpawn();

		unset($this->getArenaVote()->votes[$player->getName()]);

		if(
			!$this->pastCountdown() &&
			count($this->getPlayers()) < $this->getSettings()->getMinTeams() &&
			!$this->getHandler()->inAvailableGames($this)
		){
			$this->getHandler()->makeGameAvailable($this);
		}
	}

	public function needsPlayers() : bool{
		return count($this->getPlayers()) < $this->getSettings()->getMaxTeams();
	}


	public function getLeftStats() : array{
		return $this->left;
	}

	public function inLeft(Player $player) : bool{
		return isset($this->left[$player->getName()]);
	}

	/**
	 * Eliminates player from game and
	 * sets to spectator mode
	 */
	public function eliminate(Player $player, bool $setSpectating = true, bool $left = false): void {
		/** @var PvPPlayer $player */
		$this->getPlayer($player)->setEliminated();
		$this->eliminated[$player->getName()] = $this->getPlayer($player);
		if($left){
			$this->left[$player->getName()] = $this->getPlayer($player);
			/**
			 * Ditch stats of players that leave!
			 */
			$player->getGameSession()->getGame()->getGameStat($this)?->addLoss();
		}else{
			if(!$this->getSettings()->hasRounds()){
				$player->getGameSession()->getGame()->getGameStat($this)?->addFromScorekeeper($sc = $this->getPlayer($player));
				$sc->reward();
			}
		}

		$this->removePlayer($player, false);
		if($setSpectating){
			$this->addSpectator($player, false, $this->getSettings()->hasRounds() && $this->getRound() <= $this->getSettings()->getRounds());
		}

		if(count($this->getPlayers()) <= 1){
			if($this->getSettings()->hasRounds()){
				$this->endRound();
			}else{
				$this->endPhase();
			}
		}
	}

	public function getEliminated() : array{
		return $this->eliminated;
	}

	public function isEliminated(Player $player) : bool{
		return isset($this->eliminated[$player->getName()]);
	}

	public function getSpectators() : array{
		return $this->spectators;
	}

	public function addSpectator(Player $player, bool $forRespawn = false, bool $forNextRound = false): void {
		/** @var PvPPlayer $player */
		$this->spectators[$player->getName()] = $player;
		if(!$forRespawn){
			$this->addScoreboard($player);
			$this->getArena()->teleportTo($player);

			$gs = $player->getGameSession()->getGame();
			$gs->setGame($this);

			if($forNextRound){
				$player->setHotbar("game_spectator_round");
			}else{
				$player->setHotbar("game_spectator");
			}
		}

		$player->setGameMode(GameMode::SPECTATOR());
	}

	public function isSpectator(Player $player) : bool{
		return isset($this->spectators[$player->getName()]);
	}

	public function removeSpectator(Player $player, bool $gotoSpawn = true, bool $forRespawn = false): void {
		/** @var PvPPlayer $player */
		unset($this->spectators[$player->getName()]);

		$player->setGameMode(GameMode::ADVENTURE());

		if(!$forRespawn){
			$this->removeScoreboard($player);

			$gs = $player->getGameSession()->getGame();
			$gs->setGame();

			$player->setHotbar("");
			if($gotoSpawn) $player->gotoSpawn();
		}
	}

	public function addRespawn(Player $player) : void{
		$this->respawning[$player->getName()] = time() + $this->getSettings()->getRespawnTime();
	}

	public function startRespawn(Player $player) : void{
		$this->addSpectator($player, true);
		$this->addRespawn($player);

		$player->sendTitle(TextFormat::RED . "Respawning in...", TextFormat::YELLOW . $this->getSettings()->getRespawnTime());
	}

	public function isRespawning(Player $player) : bool{
		return isset($this->respawning[$player->getName()]);
	}

	public function respawn(Player $player): void {
		/** @var PvPPlayer $player */
		unset($this->respawning[$player->getName()]);
		if($this->getSettings()->getRespawnTime() > 0){
			$this->removeSpectator($player, false, true);
		}
		$player->getGameSession()->getCombat()->reset(false, false);
		$kit = ($gs = $player->getGameSession()->getGame())->getKit() ?? $this->getSettings()->getKitLibrary()?->getDefaultKit() ?? null;
		if($kit !== null){
			$kit->equip($player);
		}
		$this->getArena()->teleportTo($player, $this->getSettings()->hasFixedSpawnpoint() ? $gs->getSpawnpointKey() : -1, false);
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

	public function tick() : void{
		$this->ticks++;
		if($this->ticks % 20 !== 0) return;
		switch($this->getStatus()){
			case self::GAME_WAITING:
				if(count($this->players) >= $this->getSettings()->getMinTeams()){
					$this->startLobbyCountdown();
				}
				break;
			case self::GAME_LOBBY_COUNTDOWN:
				if(count($this->players) < $this->getSettings()->getMinTeams()){
					foreach ($this->getViewers() as $viewer) {
						/** @var PvPPlayer $viewer */
						$viewer->sendMessage(TextFormat::RI . "Not enough players to start match, cancelling countdown!");
						$viewer->setHotbar("game_lobby");
					}
					$this->setStatus(self::GAME_WAITING);
					if($this->getArena() !== null){
						$this->getArena()->destroy();
						$this->setArena();
						$this->getArenaVote()->votedArena = null;
					}
					break;
				}
				if($this->getTimerValue() <= 0){
					$this->startCountdown();
				}else{
					if($this->getTimerValue() === 10){
						foreach ($this->getViewers() as $viewer) {
							/** @var PvPPlayer $viewer */
							$viewer->sendTitle(TextFormat::YELLOW . $this->getTimerValue(), "", 5, 20, 5);
							$viewer->getWorld()->addSound($viewer->getPosition(), new ClickSound(), [$viewer]);
						}
					}elseif($this->getTimerValue() <= 5){
						foreach ($this->getViewers() as $viewer) {
							/** @var PvPPlayer $viewer */
							$viewer->sendTitle(TextFormat::YELLOW . $this->getTimerValue(), "", 5, 20, 5);
							$viewer->getWorld()->addSound($viewer->getPosition(), new ClickSound(), [$viewer]);
						}
					}
				}
				break;
			case self::GAME_COUNTDOWN:
				if(count($this->players) < $this->getSettings()->getMinTeams()){
					if($this->getRound() > 1){
						foreach ($this->getViewers() as $viewer) {
							/** @var PvPPlayer $viewer */
							$viewer->setNoClientPredictions(false);
						}

						$this->endPhase();
					}else{
						foreach ($this->getViewers() as $viewer) {
							/** @var PvPPlayer $viewer */
							$viewer->sendMessage(TextFormat::RI . "Not enough players to start match, sending you to a new game!");
							$this->getHandler()->getAvailableGameBy($this)->addPlayer($viewer);
							$viewer->setNoClientPredictions(false);
						}
						$this->end();
					}
					break;
				}
				if($this->getTimerValue() <= 0){
					$this->start();
				}else{
					if($this->getTimerValue() === 10){
						foreach ($this->getViewers() as $viewer) {
							/** @var PvPPlayer $viewer */
							$viewer->sendTitle(TextFormat::YELLOW . $this->getTimerValue(), "", 5, 20, 5);
							$viewer->getWorld()->addSound($viewer->getPosition(), new ClickSound(), [$viewer]);
						}
					}elseif($this->getTimerValue() <= 5){
						foreach ($this->getViewers() as $viewer) {
							/** @var PvPPlayer $viewer */
							$viewer->sendTitle(TextFormat::YELLOW . $this->getTimerValue(), "", 5, 20, 5);
							$viewer->getWorld()->addSound($viewer->getPosition(), new ClickSound(), [$viewer]);
						}
					}
				}
				break;
			case self::GAME_START:
				$this->getArena()->tick();
				if($this->getTimerValue() <= 0){
					if(
						$this->getSettings()->hasDeathmatch() &&
						count($this->getArena()->getDeathmatchSpawnpoints()) >= count($this->getPlayers()) //no deathmatch if arena can't support it
					){
						$this->startDeathmatchCountdown();
					}else{
						if($this->getSettings()->hasRounds()){
							$this->endRound();
						}else{
							$this->endPhase();
						}
					}
				}else{
					if($this->getTimerValue() <= 120 && $this->getTimerValue() %60 == 0){
						$msg = TextFormat::YI . ($this->getSettings()->hasRounds() ? ($this->getSettings()->hasDeathmatch() ? "Deathmatch starting in " : "Round ending in ") : ($this->getSettings()->hasDeathmatch() ? "Deathmatch starting in " : "Game ending in ")) . TextFormat::YELLOW . $this->getTimerValue() . " seconds";
						foreach ($this->getViewers() as $viewer) {
							/** @var PvPPlayer $viewer */
							$viewer->sendMessage($msg);
							$viewer->getWorld()->addSound($viewer->getPosition(), new ClickSound(), [$viewer]);
						}
					}elseif($this->getTimerValue() < 60 && $this->getTimerValue() %30 == 0){
						$msg = TextFormat::YI . ($this->getSettings()->hasRounds() ? ($this->getSettings()->hasDeathmatch() ? "Deathmatch starting in " : "Round ending in ") : ($this->getSettings()->hasDeathmatch() ? "Deathmatch starting in " : "Game ending in ")) . TextFormat::YELLOW . $this->getTimerValue() . " seconds";
						foreach ($this->getViewers() as $viewer) {
							/** @var PvPPlayer $viewer */
							$viewer->sendMessage($msg);
							$viewer->getWorld()->addSound($viewer->getPosition(), new ClickSound(), [$viewer]);
						}
					}elseif($this->getTimerValue() <= 5){
						$msg = TextFormat::YI . ($this->getSettings()->hasRounds() ? ($this->getSettings()->hasDeathmatch() ? "Deathmatch starting in " : "Round ending in ") : ($this->getSettings()->hasDeathmatch() ? "Deathmatch starting in " : "Game ending in ")) . TextFormat::YELLOW . $this->getTimerValue() . "...";
						foreach ($this->getViewers() as $viewer) {
							/** @var PvPPlayer $viewer */
							$viewer->sendMessage($msg);
							$viewer->getWorld()->addSound($viewer->getPosition(), new ClickSound(), [$viewer]);
						}
					}
				}
				break;
			case self::GAME_DEATHMATCH_COUNTDOWN:
				$this->getArena()->tick();
				if($this->getTimerValue() <= 0){
					$this->startDeathmatch();
				}
				break;
			case self::GAME_DEATHMATCH:
				$this->getArena()->tick();
				if($this->getTimerValue() <= 0){
					if($this->getSettings()->hasRounds()){
						$this->endRound();
					}else{
						$this->endPhase();
					}
				}
				break;
			case self::GAME_END:
				if($this->getTimerValue() <= 0){
					$this->end();
				}
				break;
		}

		foreach($this->respawning as $playerName => $time){
			if(!isset($this->players[$playerName])){
				unset($this->respawning[$playerName]);
				continue;
			}

			$formatted = $time - time();

			$player = Server::getInstance()->getPlayerExact($playerName);
			if(!$player instanceof Player){
				unset($this->respawning[$playerName]);
				continue;
			}
			/** @var PvPPlayer $player */
			$player->sendTitle(TextFormat::RED . "Respawning in...", TextFormat::YELLOW . $formatted);
			if($formatted <= 0){
				$this->respawn($player);
			}
		}

		$this->updateScoreboardLines();
	}

	public function startLobbyCountdown() : void{
		$this->setStatus(self::GAME_LOBBY_COUNTDOWN);
		$this->setTimer($this->getSettings()->getGameLobbyCountdown());

		foreach ($this->getPlayers() as $player) {
			/** @var Scorekeeper $player */
			$kitLibrary = $this->getSettings()->getKitLibrary();
			if($kitLibrary !== null && count($kitLibrary->getKits()) > 1){
				if($kitLibrary instanceof KitVoteLibrary){
					$player->getPlayer()->showModal(new KitVoteUi($player->getPlayer(), $this));
				}else{
					$player->getPlayer()->showModal(new KitSelectUi($player->getPlayer(), $this));
				}
			}
		}
	}

	public function startCountdown(bool $newRound = false) : void{
		if(($av = $this->getArenaVote())->getVotedArena() === null){
			$voted = $av->getHighestVoted();
			
			$ai = new ArenaInstance($voted, $voted->getWorldName() . "_" . $this->getName() . "_" . $this->getId());
			$ai->create($this);
			$this->setArena($ai);

			foreach($this->getViewers() as $viewer){
				$viewer->sendMessage(TextFormat::GI . TextFormat::YELLOW . $voted->getName() . TextFormat::GRAY . " map won with " . TextFormat::AQUA . $av->getVoteTotal($voted) . TextFormat::GRAY . " votes!");
			}
		}

		$this->setStatus(self::GAME_COUNTDOWN);
		$this->setTimer($newRound ?
			$this->getSettings()->getBetweenRoundTime() :
			$this->getSettings()->getGameCountdown()
		);

		if($newRound){
			if($this->getSettings()->hasNewKitEachRound() && ($kl = $this->getSettings()->getKitLibrary()) instanceof KitVoteLibrary){
				/** @var KitVoteLibrary $kl */
				$kl->reset();
			}
		}else{
			if($this->getHandler()->inAvailableGames($this)){
				$this->getHandler()->removeAvailableGame($this);
			}
		}

		$key = 0;
		foreach($this->getPlayers() as $player){
			if($this->getSettings()->hasFixedSpawnpoint()){
				$gs = $player->getPlayer()->getGameSession()->getGame();
				if($gs->getSpawnpointKey() === -1){
					$this->getArena()->teleportTo($player->getPlayer(), $key);
					$gs->setSpawnpointKey($key);
				}else{
					$this->getArena()->teleportTo($player->getPlayer(), $gs->getSpawnpointKey());
				}
			}else{
				$this->getArena()->teleportTo($player->getPlayer(), $key);
			}
			$key++;

			$player->getPlayer()->setNoClientPredictions(true);

			if($newRound){
				if($this->getSettings()->hasNewKitEachRound()){
					$kitLibrary = $this->getSettings()->getKitLibrary();
					if($kitLibrary !== null){
						if(count($kitLibrary->getKits()) > 1){
							if($kitLibrary instanceof KitVoteLibrary){
								$player->getPlayer()->showModal(new KitVoteUi($player->getPlayer(), $this));
							}else{
								$player->getPlayer()->showModal(new KitSelectUi($player->getPlayer(), $this));
							}
						}else{
							$kit = $kitLibrary->getDefaultKit() ?? null;
							if($kit !== null){
								$kit->equip($player->getPlayer());
							}
						}
					}
				}else{
					$kit = $player->getPlayer()->getGameSession()->getGame()->getKit() ?? $this->getSettings()->getKitLibrary()?->getDefaultKit() ?? null;
					if($kit !== null){
						$kit->equip($player->getPlayer());
					}
				}
			}else{
				$player->getPlayer()->sendMessage(
					TextFormat::AQUA . str_repeat("=", 16) . PHP_EOL .
					TextFormat::EMOJI_CONTROLLER . TextFormat::LIGHT_PURPLE . " Game: " . TextFormat::YELLOW . $this->getSettings()->getDisplayName() . PHP_EOL .
					TextFormat::EMOJI_SPARKLES . TextFormat::GOLD . " Map: " . TextFormat::YELLOW . $this->getArena()->getArena()->getName() . PHP_EOL . PHP_EOL .
					TextFormat::EMOJI_ARROW_RIGHT . TextFormat::GREEN . " How to play:" . PHP_EOL .
					"    " . TextFormat::GRAY . $this->getInstructions() . PHP_EOL . PHP_EOL .
					TextFormat::YELLOW . "Good luck! " . TextFormat::EMOJI_HAPPIER . PHP_EOL .
					TextFormat::AQUA . str_repeat("=", 16)
				);
				//if($this->getSettings()->hasGameLobby()){
					$kl = $this->getSettings()->getKitLibrary();
					if($kl instanceof KitVoteLibrary){
						$player->getPlayer()->getGameSession()->getGame()->setKit(($kit = $kl->getDefaultKit()));
						$player->getPlayer()->sendMessage(TextFormat::GI . TextFormat::YELLOW . $kit->getName() . TextFormat::GRAY . " kit won with " . TextFormat::AQUA . $kl->getVoteTotal($kit) . TextFormat::GRAY . " votes");
					}
					$kit = $player->getPlayer()->getGameSession()->getGame()->getKit() ?? $this->getSettings()->getKitLibrary()?->getDefaultKit() ?? null;
					if($kit !== null){
						$kit->equip($player->getPlayer());
					}
				//}else{
				//	$kitLibrary = $this->getSettings()->getKitLibrary();
				//	if($kitLibrary !== null){
				//		if(count($kitLibrary->getKits()) > 1){
				//			if($kitLibrary instanceof KitVoteLibrary){
				//				$player->getPlayer()->showModal(new KitVoteUi($player->getPlayer(), $this));
				//			}else{
				//				$player->getPlayer()->showModal(new KitSelectUi($player->getPlayer(), $this));
				//			}
				//		}else{
				//			$kit = $kitLibrary->getDefaultKit() ?? null;
				//			if($kit !== null){
				//				$kit->equip($player->getPlayer());
				//			}
				//		}
				//	}
				//}
			}

			$player->getPlayer()->setMaxHealth($this->getSettings()->getTotalHealth());
			$player->getPlayer()->setHealth($this->getSettings()->getTotalHealth());
		}
		//prob needs work
	}

	public function inCountdown() : bool{
		return $this->getStatus() == self::GAME_COUNTDOWN;
	}

	public function start() : void{
		$this->setStatus(self::GAME_START);
		$this->setTimeStarted();
		$this->setTimer($this->getSettings()->getGameLength());
		foreach($this->getPlayers() as $player){
			$player->getPlayer()->sendTitle(TextFormat::GREEN . "Start!", TextFormat::AQUA . "Good luck", 5, 20, 5);
			$player->getPlayer()->setNoClientPredictions(false);
			if($this->getRound() !== 1 && $this->getSettings()->hasNewKitEachRound()){
				if(($kl = $this->getSettings()->getKitLibrary()) instanceof KitVoteLibrary){
					/** @var KitVoteLibrary $kl */
					$player->getPlayer()->getGameSession()->getGame()->setKit(($kit = $kl->getDefaultKit()));
					$player->getPlayer()->sendMessage(TextFormat::GI . TextFormat::YELLOW . $kit->getName() . TextFormat::GRAY . " kit won with " . TextFormat::AQUA . $kl->getVoteTotal($kit) . TextFormat::GRAY . " votes");
			
					$kit = $player->getPlayer()->getGameSession()->getGame()->getKit() ?? $this->getSettings()->getKitLibrary()?->getDefaultKit() ?? null;
					if($kit !== null){
						$kit->equip($player->getPlayer());
					}
				}elseif(($kit = $player->getPlayer()->getGameSession()->getGame()->getKit()) === null){
					$kit = $kl->getDefaultKit() ?? null;
					if($kit !== null){
						$kit->equip($player->getPlayer());
					}
				}else{
					$kit->equip($player->getPlayer());
				}
			}
		}
		foreach($this->getViewers() as $viewer){
			$viewer->sendMessage(TextFormat::GI . ($this->getSettings()->hasRounds() ? "Round " . $this->getRound() : "Game") . " has started! Good luck");
		}
	}

	public function startDeathmatchCountdown() : void{
		$this->setStatus(self::GAME_DEATHMATCH_COUNTDOWN);
		$this->setTimer(5);

		$key = 0;
		foreach($this->getPlayers() as $player){
			$player->getPlayer()->teleport($this->getArena()->getDeathmatchSpawnpoint($key));
			$player->getPlayer()->setNoClientPredictions(true);
			$key++;
		}
	}

	public function startDeathmatch() : void{
		$this->setStatus(self::GAME_DEATHMATCH);
		$this->setTimer($this->getSettings()->getDeathmatchLength());

		foreach($this->getPlayers() as $player){
			$player->getPlayer()->setNoClientPredictions(false);
		}

		foreach($this->getViewers() as $viewer){
			$viewer->sendMessage(TextFormat::RI . "Deathmatch has started. Good luck!");
		}
	}

	public function processKill(Player $killer, Player $dead) : void{
		if($this->getSettings()->getMaxTeams() > 2){
			foreach($this->getViewers() as $viewer){
				$viewer->sendMessage(TextFormat::RI . TextFormat::GREEN . $killer->getName() . " " . TextFormat::EMOJI_SKULL . TextFormat::EMOJI_ARROW_RIGHT . TextFormat::RED . " " . $dead->getName());
			}
		}
		$this->getPlayer($killer)?->addScore("kills");
		$this->getPlayer($killer)?->addScore("streak");
		if(($str = $this->getPlayer($killer)?->getScore("streak")) > $this->getPlayer($killer)?->getScore("highest_streak")){
			$this->getPlayer($killer)?->setScore("highest_streak", $str);
		}
		$dscore = $this->getPlayer($dead);
		$dscore->addScore("deaths");
		$dscore->setScore("combo", 0);
		$dscore->setScore("streak", 0);

		if($this->getSettings()->hasRespawns()){
			if($this->getSettings()->hasLives()){
				$dscore->takeScore("lives");
				if($dscore->getScore("lives") <= 0){
					$this->eliminate($dead);
				}else{
					if($this->getSettings()->getRespawnTime() > 0){
						$this->startRespawn($dead);
					}else{
						$this->respawn($dead);
					}
				}
			}elseif(($killsNeeded = $this->getSettings()->getKillsNeeded()) > 0){
				if($this->getPlayer($killer)->getScore("kills") >= $killsNeeded){
					$this->respawn($dead);
					if($this->getSettings()->hasRounds()){
						$this->endRound();
					}else{
						$this->endPhase();
					}
				}else{
					if($this->getSettings()->getRespawnTime() > 0){
						$this->startRespawn($dead);
					}else{
						$this->respawn($dead);
					}
				}
			}else{
				if($this->getSettings()->getRespawnTime() > 0){
					$this->startRespawn($dead);
				}else{
					$this->respawn($dead);
				}
			}
		}else{
			$this->eliminate($dead);
		}
	}

	public function processSuicide(Player $dead) : void{
		if($this->getSettings()->getMaxTeams() > 2){
			foreach($this->getViewers() as $viewer){
				$viewer->sendMessage(TextFormat::RI . TextFormat::RED . $dead->getName() . " " . TextFormat::EMOJI_SKULL);
			}
		}

		$dscore = $this->getPlayer($dead);
		$dscore->addScore("deaths");

		if($this->getSettings()->hasRespawns()){
			if($this->getSettings()->hasLives()){
				$dscore->takeScore("lives");
				if($dscore->getScore("lives") <= 0){
					$this->eliminate($dead);
				}else{
					if($this->getSettings()->getRespawnTime() > 0){
						$this->startRespawn($dead);
					}else{
						$this->respawn($dead);
					}
				}
			}else{
				if($this->getSettings()->getRespawnTime() > 0){
					$this->startRespawn($dead);
				}else{
					$this->respawn($dead);
				}
			}
		}else{
			$this->eliminate($dead);
		}
	}

	public function getRound() : int{
		return $this->round;
	}

	public function addRound() : void{
		$this->round++;
	}

	public function getRoundScores(int $round = -1) : array{
		if($round == -1) return $this->rounds;
		return $this->rounds[$round] ?? [];
	}

	public function getPlayerScores(Player $player) : array{
		$scores = [];
		if($this->getSettings()->hasRounds()){
			foreach($this->getRoundScores() as $round => $scores){
				foreach($scores as $name => $score){
					if($name === $player->getName()) $scores[] = $score;
				}
			}
		}else{
			if($this->isEliminated($player)){
				$scores[] = $this->eliminated[$player->getName()];
			}else{
				$scores[] = $this->getPlayer($player);
			}
		}
		return $scores;
	}

	public function endRound() : void{
		$this->rounds[$this->getRound()] = [];
		foreach($this->getPlayers() as $name => $player){
			$player->getPlayer()->getGameSession()->getCombat()->getCombatMode()->reset();
			$player->setRound($this->getRound());
			$this->rounds[$this->getRound()][$name] = $player;
			$this->players[$name] = new Scorekeeper($this->getSettings(), $player->getPlayer()); //reset score after storing
		}
		foreach($this->getEliminated() as $name => $elim){
			$elim->setRound($this->getRound());
			$this->rounds[$this->getRound()][$name] = $elim;
			if($this->isSpectator($elim->getPlayer())){
				$this->removeSpectator($elim->getPlayer(), false);
				$this->addPlayer($elim->getPlayer());
			}
			unset($this->eliminated[$name]);
		}

		$winner = null;
		$winners = [];

		if($this->getSettings()->hasRespawns()){
			$highest = [];
			$score = 0;
			foreach($this->rounds[$this->getRound()] as $player){
				if($player->getScore("kills") > $score){
					$highest = [$player];
					$score = $player->getScore("kills");
				}elseif($score !== 0 && $player->getScore("kills") === $score){
					$highest[] = $player;
				}
			}
			if(count($highest) === 1){
				$winner = array_shift($highest);
			}else{
				$winners = $highest;
			}
		}else{
			$highest = [];
			$score = 0;
			foreach($this->rounds[$this->getRound()] as $player){
				if($player->getScore("kills") > $score){
					$highest = [$player];
					$score = $player->getScore("kills");
				}elseif($score !== 0 && $player->getScore("kills") === $score){
					$highest[] = $player;
				}
			}
			if(count($highest) === 1){
				$winner = array_shift($highest);
			}else{
				$winners = $highest;
			}
		}

		if($winner !== null){
			foreach($this->getViewers() as $viewer){
				$viewer->sendMessage(TextFormat::GI . TextFormat::AQUA . $winner->getPlayer()->getName() . TextFormat::GRAY . " won round " . TextFormat::YELLOW . $this->getRound());
			}
			$this->rounds[$this->getRound()][$winner->getPlayer()->getName()]?->setRoundWinner();
		}else{
			foreach($this->getViewers() as $viewer){
				$viewer->sendMessage(TextFormat::GI . "Round " . TextFormat::YELLOW . $this->getRound() . TextFormat::GRAY . " ended in a draw!");
			}
			foreach($winners as $win){
				$this->rounds[$this->getRound()][$win->getPlayer()->getName()]?->setRoundWinner();
			}
		}

		$players = [];
		foreach($this->getPlayers() as $name => $player) $players[$name] = 0;
		foreach($this->getLeftStats() as $name => $player) $players[$name] = 0;
		foreach($this->getRoundScores() as $round => $scores){
			foreach($scores as $name => $score){
				if($score->isRoundWinner()) $players[$name]++;
			}
		}

		$majority = false;
		foreach($players as $name => $score){
			if($score > ($this->getSettings()->getRounds() / 2)){
				$majority = true;
				break;
			}
		}

		if($majority || $this->getRound() >= $this->getSettings()->getRounds()){
			$this->endPhase();
		}else{
			$this->startCountdown(true);
		}

		$this->addRound();
	}

	public function isEnded() : bool{
		return $this->getStatus() === Game::GAME_END;
	}

	public function endPhase() : void{
		$this->setStatus(self::GAME_END);
		$this->setTimer(15);

		$winner = null;
		$winners = [];

		if($this->getSettings()->hasRounds()){
			$rounds = $this->getRoundScores();

			$players = [];
			foreach($this->getPlayers() as $name => $player) $players[$name] = 0;
			foreach($this->getLeftStats() as $name => $player) $players[$name] = 0;

			foreach($rounds as $round => $scores){
				foreach($scores as $name => $score){
					if($score->isRoundWinner()) $players[$name]++;

					$score->getPlayer()?->getGameSession()?->getGame()->getGameStat($this)?->addFromScorekeeper($score);
					$score->reward();
				}
			}

			$ws = 0;
			$wnrs = [];
			foreach($players as $name => $wins){
				if(isset($this->players[$name])){
					if($wins > $ws){
						$ws = $wins;
						$wnrs = [$this->players[$name]];
					}elseif($ws !== 0 && $wins === $ws){
						$wnrs[] = $this->players[$name] ?? $this->left[$name];
					}
				}
			}

			if(count($wnrs) === 1){
				$winner = current($wnrs);
			}else{
				$winners = $wnrs;
			}
		}else{
			foreach($this->getPlayers() as $player){
				$player->getPlayer()?->getGameSession()?->getGame()->getGameStat($this)?->addFromScorekeeper($player);
				$player->reward();
			}
			if($this->getSettings()->hasRespawns()){
				$highest = [];
				$score = 0;
				foreach($this->getPlayers() as $player){
					if($player->getScore("kills") > $score){
						$highest = [$player];
						$score = $player->getScore("kills");
					}elseif($score !== 0 && $player->getScore("kills") === $score){
						$highest[] = $player;
					}
				}
				if(count($highest) === 1){
					$winner = array_shift($highest);
				}else{
					$winners = $highest;
				}
			}else{
				if(count($this->getPlayers()) === 1){
					$winner = current($this->getPlayers());
				}else{ //todo: check if count is 0?
					$highest = [];
					$score = 0;
					foreach($this->getPlayers() as $player){
						if($player->getScore("kills") > $score){
							$highest = [$player];
							$score = $player->getScore("kills");
						}elseif($score !== 0 && $player->getScore("kills") === $score){
							$highest[] = $player;
						}
					}
					if(count($highest) === 1){
						$winner = array_shift($highest);
					}else{
						$winners = $highest;
					}
				}
			}
		}

		foreach($this->getPlayers() as $player){
			$player->getPlayer()->getGameSession()->getCombat()->getCombatMode()->reset();
			$player->getPlayer()->setHotbar("game_end");
		}
		foreach($this->getSpectators() as $player){
			if($this->isRespawning($player)){
				$this->respawn($player);
				$player->getGameSession()->getCombat()->getCombatMode()->reset(false);
				$this->addSpectator($player, false);
				$player->setHotbar("game_end");
			}
		}

		if($winner !== null){
			foreach($this->getPlayers() as $player){
				if(($p = $player->getPlayer())->getName() !== $winner->getPlayer()->getName()){
					$p->getGameSession()?->getGame()->getGameStat($this)?->addLoss();
				}
			}
			$this->winner($winner);
		}else{
			$this->draw($winners);
		}

		foreach($this->getViewers() as $viewer){
			$viewer->sendMessage(TextFormat::GI . "You have been queued for a new game");
		}
	}

	public function getViewers() : array{
		$viewers = [];
		foreach($this->getPlayers() as $player){
			$viewers[] = $player->getPlayer();
		}
		foreach($this->getSpectators() as $spectator){
			$viewers[] = $spectator;
		}
		return $viewers;
	}

	public function winner(Scorekeeper $scorekeeper) : void{
		$scorekeeper->getPlayer()?->getGameSession()?->getGame()->getGameStat($this)->addWin();
		foreach($this->getViewers() as $viewer){
			$viewer->sendMessage(TextFormat::RED . $scorekeeper->getPlayer()->getName() . " won the game!");
		}
	}

	public function draw(array $scores) : void{
		foreach($this->getViewers() as $viewer){
			$viewer->sendMessage(TextFormat::RED . "Game ended in a draw!");
		}
	}

	public function end(string $reason = "") : void{
		foreach($this->getPlayers() as $player){
			if($reason !== "") $player->getPlayer()->sendMessage(TextFormat::RI . "Game ended: " . $reason);
			$this->removePlayer($player->getPlayer());
			$player->getPlayer()->getGameSession()->getGame()->setSpawnpointKey();
			$this->getHandler()->getAvailableGame()->addPlayer($player->getPlayer());
		}
		foreach($this->getSpectators() as $spectator){
			if($reason !== "") $spectator->sendMessage(TextFormat::RI . "Game ended: " . $reason);
			$this->removeSpectator($spectator);
			$this->getHandler()->getAvailableGame()->addPlayer($spectator);
		}

		$this->getGameLobby()?->destroy();
		$this->getArena()?->destroy();
		$this->destruct();
	}

	public function pastCountdown() : bool{
		return $this->getStatus() >= self::GAME_COUNTDOWN;
	}

	public function isStarted() : bool{
		return $this->getStatus() >= self::GAME_START;
	}

	public function getStatus() : int{
		return $this->status;
	}

	public function setStatus(int $status) : void{
		$this->status = $status;
	}

	public function getStatusName() : string{
		return match($this->getStatus()) {
			self::GAME_WAITING => "Waiting...",
			self::GAME_LOBBY_COUNTDOWN => "Countdown",
			self::GAME_COUNTDOWN => "Countdown",
			self::GAME_START => "Start!",
			self::GAME_DEATHMATCH_COUNTDOWN => "Countdown",
			self::GAME_DEATHMATCH => "Deathmatch!",
			self::GAME_END => "End"
		};
	}

	public function getStatusNameColored() : string{
		return match($this->getStatus()) {
			self::GAME_WAITING => TextFormat::RED . "Waiting...",
			self::GAME_LOBBY_COUNTDOWN => TextFormat::GOLD . "Countdown",
			self::GAME_COUNTDOWN => TextFormat::GOLD . "Countdown",
			self::GAME_START => TextFormat::GREEN . "Start!",
			self::GAME_DEATHMATCH_COUNTDOWN => TextFormat::GOLD . "Countdown",
			self::GAME_DEATHMATCH => TextFormat::RED . "Deathmatch!",
			self::GAME_END => TextFormat::YELLOW . "End"
		};
	}

	public function getTimer() : int{
		return $this->timer;
	}

	public function getTimeStarted() : int{
		return $this->timeStarted;
	}

	public function setTimeStarted() : void{
		$this->timeStarted = time();
	}

	/**
	 * Formats timer into subtraction mode
	 */
	public function getTimerValue() : int{
		return $this->getTimer() - time();
	}

	public function getTimerValueFormatted() : string{
		$seconds = $this->getTimerValue();

		$minutes = floor($seconds / 60);
		$seconds = $seconds % 60;

		$formattedTimer = sprintf('%02d:%02d', $minutes, $seconds);

		return $formattedTimer;
	}

	public function setTimer(int $seconds) : void{
		$this->timer = time() + $seconds;
	}

	public function get() : ?Game{
		return $this->getHandler()->getGame($this->getId()) ?? $this->getHandler()->getAvailableGameBy($this);
	}

	public function destruct() : void{
		unset($this->getHandler()->availableGames[$this->getId()]);
		unset($this->getHandler()->games[$this->getId()]);
	}

	/* Scoreboard */
	public function updateScoreboardLines() : void{
		if($this->getStatus() <= self::GAME_COUNTDOWN){
			$this->lines[3] = TextFormat::EMOJI_ARROW_RIGHT . TextFormat::AQUA . " Players: " . TextFormat::YELLOW . count($this->getPlayers()) . "/" . $this->getSettings()->getMaxTeams();


		}else{
			$this->lines[3] = "          ";
		}

		$this->lines[6] = TextFormat::EMOJI_BELL . TextFormat::AQUA . " Status: " . $this->getStatusNameColored();
		if($this->getStatus() !== self::GAME_WAITING){
			$this->lines[7] = TextFormat::EMOJI_SPARKLES . TextFormat::RED . " " . $this->getTimerValueFormatted();
		}else{
			$this->lines[7] = "           ";
		}

		ksort($this->lines);
		$this->updateAllScoreboards();
	}

	public function getLines() : array{
		return $this->lines;
	}

	public function getLinesFor(Player $player): array {
		/** @var PvPPlayer $player */
		$lines = $this->getLines();

		$session = $player->getGameSession()?->getGame();
		if($session === null) return $lines;


		if($this->getStatus() <= self::GAME_COUNTDOWN){

		}else{
			if($this->getSettings()->hasRespawns()){
				if($this->hasPlayer($player)){
					$lines[3] = TextFormat::EMOJI_SKULL . TextFormat::GREEN . " Kills: " . TextFormat::YELLOW . $this->getPlayer($player)->getScore("kills");
					$lines[4] = TextFormat::EMOJI_SKULL . TextFormat::RED . " Deaths: " . TextFormat::YELLOW . $this->getPlayer($player)->getScore("deaths");
				}elseif($this->isSpectator($player)){

				}
			}else{
				$lines[3] = TextFormat::EMOJI_SKULL . TextFormat::GREEN . " Players left: " . TextFormat::YELLOW . count($this->getPlayers());
				$lines[4] = "      ";
			}
		}

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
