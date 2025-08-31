<?php namespace pvp\combat;

use pocketmine\entity\Entity;
use pocketmine\player\Player;

use pvp\PvP;
use pvp\PvPPlayer;
use pvp\combat\utils\ModeManager;
use pvp\enchantments\ItemData;
use pvp\games\entity\PracticeBot;

use core\session\PlayerSession;
use core\session\component\{
	ComponentRequest,
	SaveableComponent
};
use core\utils\{
	TextFormat
};

/**
 * @method PvPPlayer getPlayer()
 */
class CombatComponent extends SaveableComponent{

	public int $invincible = 0;

	public ModeManager $mode;

	public string $lastReason = ""; //hack?

	public function __construct(PlayerSession $session){
		parent::__construct($session);
		$this->mode = new ModeManager($this);
	}

	public function getName() : string{
		return "combat";
	}

	public function tick() : void{
		$this->getCombatMode()->tick();

		if($this->invincible > 0){
			$this->invincible--;
			if($this->invincible <= 0){
				$this->getPlayer()?->sendMessage(TextFormat::RI . "You are you longer invincible!");
			}
		}
	}

	/**
	 * Called when this player kills another player
	 */
	public function kill(Player $player) : void{
		/** @var PvPPlayer $killer */
		$killer = $this->getPlayer();
		if(($as = $killer->getGameSession()->getArenas())->inArena()){
			$arena = $as->getArena();
			$as->addKill($as->getArena());
			$as->addCurrentStreak();
			for($i = 0; $i <= 2; $i++){ //check streaks
				if($as->getCurrentStreak() > $as->getStreak($as->getArena(), $i)){
					$as->setStreak($as->getArena(), $as->getCurrentStreak(), $i);
				}
			}

			if($arena->getSettings()->hasKillRegen()){
				$killer->setHealth(20);
				$player->getHungerManager()->setFood(20);
				$player->getHungerManager()->setSaturation(20);
			}
		}

		$hand = $killer->getInventory()->getItemInHand();
		$item = new ItemData($hand);

		$killer->sendMessage(TextFormat::GI . "You killed " . TextFormat::RED . $player->getName());

		if($item->hasEffect()){
			$effect = $item->getEffect();
			$callable = $effect->getCallable();
			$callable($killer, $player->getPosition());
		}


		/** @var PvPPlayer $player */
		$session = $player->getGameSession()->getCombat();
		$session->death($killer);

		if(($gs = $killer->getGameSession()->getGame())->inGame()){
			$gs->getGame()->processKill($killer, $player);
		}

		if(($as = $killer->getGameSession()->getArenas())->inArena()){
			$killer->getGameSession()->getLevels()->addExperience(5 * $as->getArena()->getSettings()->getXpkr());
		}

		$this->getCombatMode()->sendMsg = false;
		$this->getCombatMode()->reset();
	}

	/**
	 * Called when player kills a mob lol
	 */
	public function maul(Entity $entity) : void{

	}

	/**
	 * Called when player kills this player
	 */
	public function death(Player $player) : void {
		/** @var PvPPlayer $player */
		if(($ded = $this->getPlayer())->inSpawnPvP()){
			$this->reset();
			$ded->setHotbar("spawn");
			$ded->setSpawnPvP(false);
			return;
		}
		if(!($gs = $ded->getGameSession()->getGame())->inGame()){
			$this->reset();
		}else{
			$this->reset($gs->getGame()->getSettings()->hasItemDrops(), false);
		}
		if(($as = $ded->getGameSession()->getArenas())->inArena()){
			$as->addDeath($as->getArena());
			$as->resetCurrentCombo();
			$as->resetCurrentStreak();

			$arena = $as->getArena();
			$arena->teleportTo($ded);
			$arena->resetPearlCooldown($ded);

			if(($ks = $ded->getGameSession()->getKits())->hasKit()){
				$ks->getKit()->equip($ded);
			}
		}

		$this->getCombatMode()->sendMsg = false;
		$this->getCombatMode()->reset();

		$ded->sendMessage(TextFormat::RI . "You were killed by " . TextFormat::RED . $player->getName());
	}

	/**
	 * Called when player dies to mob
	 */
	public function ded(Entity $entity) : void{
		$this->reset();
	}

	public function suicide() : void{
		if(($gs = $this->getPlayer()->getGameSession()->getGame())->inGame()){
			$this->reset(($game = $gs->getGame())->getSettings()->hasItemDrops(), false);
			$game->processSuicide($this->getPlayer());
		}else{
			$this->reset();
		}
		if(($ded = $this->getPlayer())->inSpawnPvP()){
			$ded->setHotbar("spawn");
			$ded->setSpawnPvP(false);
			return;
		}

		if(($as = $ded->getGameSession()->getArenas())->inArena()){
			$as->addDeath($as->getArena());
			$as->resetCurrentCombo();
			$as->resetCurrentStreak();
			$arena = $as->getArena();
			$arena->teleportTo($ded);
			$arena->resetPearlCooldown($ded);

			if(($ks = $ded->getGameSession()->getKits())->hasKit()){
				$ks->getKit()->equip($ded);
			}
		}
	}

	public function canCombat(?Entity $entity = null) : bool{
		$this->lastReason = "";
		if(!($player = $this->getPlayer()) instanceof Player) return false;
		if($entity instanceof PracticeBot) return true;
		if($this->isInvincible()) return false;
		if(!$player->isLoaded()) return false;

		$ep = $entity instanceof Player;

		/** @var PvPPlayer $entity */
		if($player->inSpawn()){
			if(
				$player->inSpawnPvP() &&
				$ep &&
				$entity->inSpawnPvP()
			) return true;
			return false;
		}

		if(($gs = $player->getGameSession()->getGame())->inGame()){
			$game = $gs->getGame();
			if(!$game->isStarted() || $game->isEnded()){
				return false;
			}
		}

		if($ep && $entity->getGameSession()->getCombat()->isInvincible()) return false;
		
		if(($as = $player->getGameSession()->getArenas())->inArena()){
			$arena = $as->getArena();
			if($ep && $entity->getGameSession()->getArenas()->getArena() === $as->getArena()){
				if(
					$arena->getSettings()->antiInterrupt() &&
					($mode = $entity->getGameSession()->getCombat()->getCombatMode())->inCombat() &&
					$mode->hit !== $player->getName()
				){
					$this->lastReason = "ai";
					return false;
				}
				return true;
			}
		}

		//todo: checks for games, whether entities are in same game or same arena, etc

		return true;
	}

	/**
	 * Resets player, teleports back to spawn.
	 */
	public function reset(bool $dropitems = false, bool $gotospawn = true) : void{
		$player = $this->getPlayer();
		if(!$player instanceof Player) return;
		$player->getEffects()->clear();
		$player->setHealth(20);
		$player->getHungerManager()->setFood(20);
		$player->getHungerManager()->setSaturation(20);
		$player->extinguish();

		if($dropitems){
			$player->getWorld()->dropExperience($player->getPosition(), $player->getXpDropAmount());

			foreach($player->getInventory()->getContents() as $item){
				$player->getWorld()->dropItem($player->getPosition(), $item);
			}
			foreach($player->getArmorInventory()->getContents() as $item){
				$player->getWorld()->dropItem($player->getPosition(), $item);
			}
			foreach($player->getCursorInventory()->getContents() as $item){
				$player->getWorld()->dropItem($player->getPosition(), $item);
			}
			foreach($player->getOffhandInventory()->getContents() as $item){
				$player->getWorld()->dropItem($player->getPosition(), $item);
			}
			foreach($player->getCraftingGrid()->getContents() as $item){
				$player->getWorld()->dropItem($player->getPosition(), $item);
			}
		}

		$player->getInventory()->clearAll();
		$player->getArmorInventory()->clearAll();
		$player->getCursorInventory()->clearAll();
		$player->getOffhandInventory()->clearAll();
		$player->getCraftingGrid()->clearAll();
		
		$player->getXpManager()->setCurrentTotalXp(0);

		$this->getCombatMode()->reset(false);

		if($gotospawn) $player->teleport(PvP::getSpawn());
	}

	public function isInvincible() : bool{
		return $this->invincible - time() > 0;
	}

	public function setInvincible(int $seconds = 5) : void{
		$this->invincible = time() + $seconds;
		$this->getPlayer()?->sendMessage(TextFormat::YI . "You have invincibility for " . $seconds . " seconds.");
	}

	public function getCombatMode() : ModeManager{
		return $this->mode;
	}

	public function createTables() : void{
		$db = $this->getSession()->getSessionManager()->getDatabase();
		foreach([

		] as $query) $db->query($query);
	}

	public function loadAsync() : void{
		//$request = new ComponentRequest($this->getXuid(), $this->getName(), new MySqlQuery("main", "SELECT * FROM network_playerinfo WHERE xuid=?", [$this->getXuid()]));
		//$this->newRequest($request, ComponentRequest::TYPE_LOAD);
		//$this->newRequest($request, ComponentRequest::TYPE_LOAD);
		parent::loadAsync();
		$this->finishLoadAsync(); //todo
		echo "Load request sent for " . $this->getName() . " component", PHP_EOL;
	}

	public function finishLoadAsync(?ComponentRequest $request = null) : void{
		//$result = $request->getQuery()->getResult();
		//$rows = (array) $result->getRows();

		parent::finishLoadAsync($request);
		echo "Finished loading " . $this->getName() . " component", PHP_EOL;
	}

	/*public function load() : bool{
		return parent::load();
	}*/

	public function verifyChange() : bool{
		$player = $this->getPlayer();
		$verify = $this->getChangeVerify();
		return false;
	}

	public function saveAsync() : void{
		if(!$this->hasChanged() || !$this->isLoaded()) return;

		$this->setChangeVerify([]);

		/**$player = $this->getPlayer();
		$uuid = $player instanceof Player ? $player->getUniqueId()->toString() : "dddd";
		$request = new ComponentRequest($this->getXuid(), $this->getName(), new MySqlQuery("main", "INSERT network_playerinfo(xuid, uuid, gamertag) VALUES(?, ?, ?) ON DUPLICATE KEY UPDATE xuid=VALUES(xuid), uuid=VALUES(uuid), gamertag=VALUES(gamertag)", [$this->getXuid(), $uuid, $this->getGamertag()]));
		$this->newRequest($request, ComponentRequest::TYPE_SAVE);*/

		parent::saveAsync();
		$this->finishSaveAsync();
		echo $this->getName() . " component started saving async", PHP_EOL;
	}

	public function finishSaveAsync() : void{
		parent::finishSaveAsync();

		echo $this->getName() . " component finished saving async", PHP_EOL;
	}

	public function save() : bool{
		if(!$this->hasChanged()) return false;

		$player = $this->getPlayer();
		$uuid = $player instanceof Player ? $player->getUniqueId()->toString() : "dddd";
		$xuid = $this->getXuid();

		$db = $this->getSession()->getSessionManager()->getDatabase();

		echo $this->getName() . " component saved on main thread", PHP_EOL;
		return parent::save();
	}

}