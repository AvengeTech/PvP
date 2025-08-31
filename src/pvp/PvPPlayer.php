<?php namespace pvp;

use pocketmine\player\{
	GameMode,
	Player
};
use pocketmine\block\VanillaBlocks;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\world\particle\BlockBreakParticle;
use pocketmine\utils\TextFormat;

use pvp\hotbar\utils\HotbarHandler;

use core\AtPlayer;
use pvp\kits\Kit;

class PvPPlayer extends AtPlayer{

	/** @var Player|null */
	public $bleedInflict = null;
	/** @var int */
	public $bleedTicks = 0;

	public $combo = 0;
	public $comboTicks = -1;

	public $gappleCooldown = 0;

	public $healthChanged = false;

	public $spawnPvP = false;

	public bool $networkPropertiesDirty = true;

	public function getGameSession() : ?PvPSession{
		return PvP::getInstance()->getSessionManager()->getSession($this);
	}
	
	public function hasGameSession() : bool{
		return $this->getGameSession() !== null;
	}

	public function getTechits() : int{
		return $this->getGameSession()->getTechits()->getTechits();
	}

	public function setTechits(int $value) : void{
		$this->getGameSession()->getTechits()->setTechits($value);
	}

	public function takeTechits(int $value){
		$this->getGameSession()->getTechits()->takeTechits($value);
	}

	public function addTechits(int $value){
		$this->getGameSession()->getTechits()->addTechits($value);
	}

	protected function onDeath() : void{
		parent::onDeath();

		if($this->isBleeding()){
			$this->stopBleeding();
		}
	}

	public function onUpdate(int $currentTick) : bool{
		parent::onUpdate($currentTick);

		if($this->getCombo() > 0){
			if($this->comboTicks >= 0){
				$this->comboTicks--;
			}else{
				$this->combo = 0;
			}
		}

		if($this->bleedTicks > 0){
			$this->bleedTicks--;
			if($this->bleedTicks % 15 == 0){
				if($this->getHealth() <= 1){
					if($this->bleedInflict !== null && $this->bleedInflict->isAlive()){
						//PvP::getInstance()->getCombat()->processKill($this->bleedInflict, $this);
					}else{
						//PvP::getInstance()->getCombat()->processSuicide($this);
					}
				}else{
					$this->setHealth($this->getHealth() - mt_rand(0, 1));
					$this->getWorld()->addParticle($this->getPosition(), new BlockBreakParticle(VanillaBlocks::REDSTONE()));
				}
			}

			if($this->bleedTicks == 0){
				$this->bleedInflict = null;
			}
		}

		if($this->healthChanged){
			$this->healthChanged = false;
			$this->setScoreTag($this->getHealthBar());
		}

		return $this->isAlive();
	}

	public function spawnTo(Player $player) : void{
		if($this->isLoaded() && ($as = $this->getGameSession()->getArenas())->inArena()){
			if(!$as->isSpectator()){
				parent::spawnTo($player);
			}
		}else{
			parent::spawnTo($player);
		}
	}

	public function getHealthBar() : string{
		$max = $this->getMaxHealth();
		$health = $this->getHealth();
		$os = $this->getDeviceOSname();
		return TextFormat::AQUA . "[" . ($health >= 0 ? TextFormat::GREEN . str_repeat("|", (int) $health) : "") . (($max - $health) >= 0 ? TextFormat::RED . str_repeat("|", (int) ($max - $health)) : "") . TextFormat::AQUA . "] " . TextFormat::GREEN . $os;
	}

	public function attack(EntityDamageEvent $source) : void{
		parent::attack($source);
		$this->healthChanged = true;
	}

	public function setHealth(float $amount) : void{
		parent::setHealth($amount);
		if($this->isAlive()){
			$this->healthChanged = true;
		}
	}

	public function setMaxHealth(int $amount) : void{
		parent::setMaxHealth($amount);
		if($this->isAlive()){
			$this->healthChanged = true;
		}
	}

	public function hasHotbar() : bool{
		return $this->getGameSession()->getHotbar()->hasHotbar();
	}

	public function getHotbar() : ?HotbarHandler{
		return $this->getGameSession()->getHotbar()->getHotbar();
	}

	public function setHotbar(string $name = "", bool $clear = true) : void{
		$hotbar = PvP::getInstance()->getHotbar();
		$this->getGameSession()->getHotbar()->setHotbar($hotbar->getHotbar($name), $clear);
	}

	public function getCombo() : int{
		return $this->combo;
	}

	public function addCombo() : void{
		$this->combo++;
		$this->comboTicks = 40;
	}

	public function resetCombo() : void{
		$this->combo = 0;
		$this->comboTicks = -1;
	}

	public function isBleeding() : bool{
		return $this->bleedTicks > 0;
	}

	public function bleed(?Player $player, int $ticks) : void{
		$this->bleedInflict = $player;
		$this->bleedTicks += $ticks;
	}

	public function stopBleeding() : void{
		$this->bleedInflict = null;
		$this->bleedTicks = 0;
	}

	public function hasGappleCooldown() : bool{
		return $this->gappleCooldown >= time();
	}

	public function getGappleCooldown() : int{
		return $this->gappleCooldown - time();
	}

	public function setGappleCooldown(int $cooldown = 45) : void{
		$this->gappleCooldown = time() + $cooldown;
	}

	public function inSpawn() : bool{
		return $this->getWorld()->getDisplayName() == PvP::SPAWN_WORLD;
	}

	public function gotoSpawn() : void{
		if(!$this->inSpawn()){
			$this->setHotbar("spawn");
		}
		$this->teleport(PvP::getSpawn());

		if($this->getGamemode() === GameMode::SURVIVAL() || $this->getGamemode() === GameMode::SPECTATOR()){
			$this->setGamemode(GameMode::ADVENTURE());
		}
	}

	public function inSpawnPvP() : bool{
		return $this->spawnPvP;
	}

	public function setSpawnPvP(bool $pvp = true) : void{
		$this->spawnPvP = $pvp;
	}

	public function getKit() : ?Kit{
		return $this->getGameSession()->getKits()->getKit();
	}

	public function hasKit() : bool{
		return $this->getKit() !== null;
	}

	public function setKit($kit = null, bool $equip = true) : void{
		$this->getGameSession()->getKits()->setKit($kit, $equip);
	}

	public function canFly() : string|bool{
		if(!parent::canFly()){
			if($this->inSpawn()){
				return true;
			}
			return "You cannot fly here!";
		}
		return true;
	}
	
	public function setFlightMode(bool $mode = true) : void{
		parent::setFlightMode($mode);
		if(!$mode){
			$this->setGamemode(GameMode::ADVENTURE());
		}
	}

}