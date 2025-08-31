<?php namespace pvp\games\entity; //thx prim

use pocketmine\block\BlockToolType;
use pocketmine\entity\{
	Entity,
	Human,
	Living,
	Location,
	Skin,

	animation\ArmSwingAnimation,
	animation\CriticalHitAnimation,

	projectile\SplashPotion
};
use pocketmine\event\entity\{
	EntityDamageByEntityEvent,
	EntityDamageEvent,
	ProjectileLaunchEvent
};
use pocketmine\item\{
	ItemUseResult,
	PotionType,
	ProjectileItem,
    SplashPotion as ItemSplashPotion,
    VanillaItems,
};
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\{
	LevelEventPacket,
	LevelSoundEventPacket,
	EmotePacket
};
use pocketmine\network\mcpe\protocol\types\{
	LevelSoundEvent,
	LevelEvent
};
use pocketmine\utils\TextFormat;
use pocketmine\world\sound\{
	EntityAttackNoDamageSound,
	EntityAttackSound,
	ThrowSound
};

use pvp\games\type\Game;

class PracticeBot extends Human {

	public float $blocksRunToPot = 3;
	public float $reach = 2.3;
	public int $damage = 1;

	public ?Vector3 $knockbackMotion = null;
	public int $inAirTicks = 0;
	public int $potTicks = 0;
	public int $pearlCooldown = 0;
	public int $swings = 0;

	public float $moveForward = 0.0;
	public float $moveStrafe = 0.0;
	public int $nextStrafeTicks = 40;

	public bool $isRunningAway = false;
	public Vector3 $potStartSpot;
	public string $difficulty;
	public int $pots = 8;
	public int $pearls = 4;

	public $jumpVelocity = 0.334;
	public $gravity = 0.0645;

	public $stepHeight = 1.0;

	public int $deathTicks = 0;
	public bool $sentDeathAn = false;

	public const POT_HEALTH = [10, 7, 5];

	public bool $started = false;
	
	public ?Game $game = null;

	public function __construct(Location $location, Skin $skin, ?CompoundTag $nbt = null){
		parent::__construct($location, $skin, $nbt);

		$this->getArmorInventory()->setContents([VanillaItems::DIAMOND_HELMET(), VanillaItems::DIAMOND_CHESTPLATE(), VanillaItems::DIAMOND_LEGGINGS(), VanillaItems::DIAMOND_BOOTS()]);
		$this->getInventory()->setItem(0, VanillaItems::DIAMOND_SWORD());
		$this->getInventory()->setItem(1, VanillaItems::SPLASH_POTION()->setType(PotionType::STRONG_HEALING()));
		$this->getInventory()->setItem(2, VanillaItems::ENDER_PEARL());
		$this->getInventory()->setHeldItemIndex(0);

		$this->setCanSaveWithChunk(false);
	}

	public function entityBaseTick(int $tickDiff = 1) : bool {
		if(!$this->started) return false;
		$target = $this->getTargetEntity();
		if(is_null($target) || $this->closed || !$target->isAlive()) return false;

		$this->setNameTag(TextFormat::AQUA . "Jefflin Jr " . TextFormat::GREEN . "[" . round($this->getHealth(), 2) . "/" . $this->getMaxHealth() . "]");
		$this->setNametagAlwaysVisible(true);

		$targetPos = $target->getLocation();
		$selfPos = $this->getLocation();
		$distance = $targetPos->distance($selfPos);

		$this->moveForward = 1.0;
		if($distance < 7 && !$this->isPotting() && mt_rand(0, 100) === 0) {
			if (--$this->nextStrafeTicks <= 0) {
				$this->moveStrafe = mt_rand(-1, 1);
				$this->nextStrafeTicks = 40;
			}
		} else {
			$this->moveStrafe = 0.0;
		}

		if (abs($this->moveForward) > 0 && abs($this->moveStrafe) > 0) {
			$this->moveForward *= 0.788;
			$this->moveStrafe *= 0.788;
		}

		$this->moveForward *= 0.98;
		$this->moveStrafe *= 0.98;

		if($this->pearlCooldown > 0) $this->pearlCooldown--;
		if($this->pearlCooldown <= 0 && $this->pearls > 0){
			if($distance > 20){
				$this->pearlCooldown = 200;
				$this->lookAt($targetPos);
				$this->calculatePearlPitch();
				$this->pearl();
			} elseif($distance < 7 && mt_rand(1, 950) === 1){
				$this->pearlCooldown = 200;
				$this->lookAt($targetPos);
				$this->calculatePearlPitch();
				$this->pearl();
			}
		}

		if($selfPos->y > $targetPos->y + 1){
			$this->inAirTicks++;
		} else {
			$this->inAirTicks = 0;
		}

		if($this->getHealth() <= self::POT_HEALTH[array_rand(self::POT_HEALTH)] && mt_rand(0, 20) > 18){
			if($this->pots > 0 && !$this->isPotting()){
				$this->potTicks = 100;
				$this->potStartSpot = $this->getPosition();
			}
		}

		if($this->isPotting()){
			$this->potTicks--;
			$this->runAway();
			if($selfPos->distance($this->potStartSpot) >= $this->blocksRunToPot && !$this->isLookingAt($targetPos)){
				if($selfPos->y > $targetPos->y){
					$selfPos->pitch = -25;
					$this->runAway();
					mt_rand(1, 4) === 1 ? $this->pot() : $this->instantPot();
				} else {
					$selfPos->yaw = $selfPos->yaw < 0 ? abs($selfPos->yaw) : ($selfPos->yaw == 0 ? -180 : -$selfPos->yaw);
					$selfPos->pitch = 85;
					$this->pot();
				}
				$this->potTicks = 0;
			}
		}

		if($distance > 10){
			$this->jump();
			$this->moveStrafe = 0.0;
		}

		if($this->inAirTicks > 55 && !$this->isRunningAway){
			$this->isRunningAway = true;
		} elseif($this->onGround && $this->isRunningAway) $this->isRunningAway = false;
		if(mt_rand(0, 700) === 1) $this->swings = 0;

		if(!$this->isRunningAway && !$this->isPotting()) $this->lookAt($targetPos);

		if ($this->knockbackMotion !== null) {
			$this->knockbackMotion->x *= 0.81;
			$this->knockbackMotion->z *= 0.81;
			if ($this->onGround) {
				$this->knockbackMotion->x *= 0.6;
				$this->knockbackMotion->z *= 0.6;
			}
			if (abs($this->knockbackMotion->x) < 0.005 || abs($this->knockbackMotion->z) < 0.005) {
				$this->knockbackMotion = null;
			}
		} elseif (!$this->isRunningAway) {
			$this->setSpeed(0.7);
		}

		if($distance <= (mt_rand(57, 70) / 10) && $this->isLookingAt($targetPos)){
			$this->getInventory()->setHeldItemIndex(0);
			if($this->swings < 80){
				$this->broadcastAnimation(new ArmSwingAnimation($this));
				$event = new EntityDamageByEntityEvent($this, $target, EntityDamageEvent::CAUSE_ENTITY_ATTACK, $this->getInventory()->getItemInHand()->getBlockToolType() === BlockToolType::SWORD ? $this->damage + 7 : 0.5);
				$this->swings++;
				if($distance <= $this->reach/** && $this->getInventory()->getItemInHand()->getBlockToolType() === BlockToolType::SWORD*/){
					$target->attack($event);
					$this->swings = 0;
				}
			} else {
				if($distance <= $this->reach/** && $this->getInventory()->getItemInHand()->getBlockToolType() === BlockToolType::SWORD*/){
					$this->broadcastAnimation(new ArmSwingAnimation($this));
					$event = new EntityDamageByEntityEvent($this, $target, EntityDamageEvent::CAUSE_ENTITY_ATTACK, $this->getInventory()->getItemInHand()->getBlockToolType() === BlockToolType::SWORD? $this->damage + 7 : 0.5);
					$target->attack($event);
					$this->swings = 0;
				}
			}
		}

		$friction = 0.81;
		if ($this->onGround) {
			$friction *= 0.6;
			$f = 0.13 * (0.16277136 / ($friction ** 3));
		} else {
			$f = 0.026;
		}

		$this->moveFlying($this->moveForward, $this->moveStrafe, $f);

		$this->motion->y -= 0.08;
		$this->motion->y *= 0.9800000190734863;
		$this->motion->x *= $friction; // remove friction for crazy cool stuff
		$this->motion->z *= $friction;

		// STRAFING IS CAUSED BY: setSpeed(), setMotion(), moveFlying()

		if (abs($this->motion->x) < 0.005) $this->motion->x = 0;
		if (abs($this->motion->y) < 0.005) $this->motion->y = 0;
		if (abs($this->motion->z) < 0.005) $this->motion->z = 0;

		return parent::entityBaseTick($tickDiff);
	}

	public function setMotion(Vector3 $motion): bool {
		if ($motion->y > 0 && ($motion->y < 0.3 || abs($motion->y - 0.4) < 0.05)) {
			$motion->y = 0.385;
		}
		if (abs($motion->x) < 0.05 || abs($motion->z) < 0.05) {
			if (abs($motion->x) > abs($motion->z)){
				$motion->x = $motion->x >= 0 ? 0.37 : -0.37;
				$motion->z = $motion->z >= 0 ? 0.115 : -0.115;
			} else {
				$motion->z = $motion->z >= 0 ? 0.37 : -0.37;
				$motion->x = $motion->x >= 0 ? 0.115 : -0.115;
			}
		}
		$motion->x *= 0.7;
		$motion->z *= 0.7;
		$valid = parent::setMotion($motion);
		if ($valid) {
			$this->knockbackMotion = $motion;
		}
		return $valid;
	}

	public function moveFlying(float $forward, float $strafe, float $friction): void {
		$var4 = $forward * $forward + $strafe * $strafe;
		if ($var4 >= 1E-4) {
			$var4 = sqrt($var4);
			if ($var4 < 1.0) $var4 = 1.0;
			$var4 = $friction / $var4;
			$forward *= $var4;
			$strafe *= $var4;
			$var5 = sin($this->getLocation()->yaw * M_PI / 180);
			$var6 = cos($this->getLocation()->yaw * M_PI / 180);
			$this->motion->x += ($forward * $var6 - $strafe * $var5);
			$this->motion->z += ($strafe * $var6 - $forward * $var5);
		}
	}

	protected function setSpeed(float $speed): void {
		$directionSpeed = $this->getDirectionSpeed();
		$this->motion->x = -sin($directionSpeed) * $speed;
		$this->motion->z = cos($directionSpeed) * $speed;
	}

	// returns a random float from 0.0 - 1.0

	private function getDirectionSpeed(): float {
		$direction = abs($this->getLocation()->yaw);
		if ($this->moveForward < 0) $direction += 180;
		$forward = $this->moveForward < 0 ? -0.5 : 0.5;
		$this->moveStrafe > 0 ? $direction -= 90 * $forward : $direction += 90 * $forward;
		$direction *= 0.017453292;
		return $direction;
	}

	public function attackEntity(Entity $entity) : bool{
		if(!$entity->isAlive()) return false;

		$heldItem = $this->inventory->getItemInHand();
		$oldItem = clone $heldItem;

		$ev = new EntityDamageByEntityEvent($this, $entity, EntityDamageEvent::CAUSE_ENTITY_ATTACK, $heldItem->getAttackPoints());

		if(!$this->isSprinting() and $this->fallDistance > 0 and !$this->isUnderwater()){
			$ev->setModifier($ev->getFinalDamage() / 2, EntityDamageEvent::MODIFIER_CRITICAL);
		}

		$entity->attack($ev);

		$soundPos = $entity->getPosition()->add(0, $entity->size->getHeight() / 2, 0);
		if($ev->isCancelled()){
			$this->getWorld()->addSound($soundPos, new EntityAttackNoDamageSound());
			return false;
		}
		$this->broadcastAnimation(new ArmSwingAnimation($this), $this->getViewers());
		$this->getWorld()->addSound($soundPos, new EntityAttackSound());

		if($ev->getModifier(EntityDamageEvent::MODIFIER_CRITICAL) > 0 and $entity instanceof Living){
			$entity->broadcastAnimation(new CriticalHitAnimation($entity));
		}

		if($this->isAlive()){
			//reactive damage like thorns might cause us to be killed by attacking another mob, which
			//would mean we'd already have dropped the inventory by the time we reached here
			if($heldItem->onAttackEntity($entity, []) and $oldItem->equalsExact($this->inventory->getItemInHand())){ //always fire the hook, even if we are survival
				$this->inventory->setItemInHand($heldItem);
			}
		}

		return true;
	}

	public function calculateFallDamage(float $fallDistance): float{
		return 0;
	}

	public function pot(){
		$this->getInventory()->setHeldItemIndex(1);
		/** @var ItemSplashPotion $item */
		$item = $this->getInventory()->getItemInHand();
		$location = $this->getLocation();
		$projectile = new SplashPotion($this->getLocation(), $this, PotionType::STRONG_HEALING());
		$projectile->setMotion($projectile->getMotion()->multiply($item->getThrowForce()));
		$projectile->spawnToAll();
		$location->getWorld()->addSound($location, new ThrowSound());
		$this->setHealth($this->getHealth() + 5);
		$this->pots--;
	}

	public function instantPot(){
		$pos = $this->getPosition();
		$this->getInventory()->setHeldItemIndex(1);

		$this->getWorld()->broadcastPacketToViewers($pos, LevelSoundEventPacket::create(LevelSoundEvent::GLASS, $pos, -1, ':', false, false));
		$this->getWorld()->broadcastPacketToViewers($pos, LevelEventPacket::create(LevelEvent::PARTICLE_SPLASH, 4294452259, $pos));

		$this->setHealth($this->getHealth() + 10);
		$this->pots--;
	}

	public function pearl(){
		$this->getInventory()->setHeldItemIndex(2);
		$item = $this->getInventory()->getItemInHand();
		if($item instanceof ProjectileItem){
			$this->onClickAir($item);
			$this->pearls--;
		}
	}

	public function onClickAir(ProjectileItem $item) : ItemUseResult{
		$location = $this->getLocation();

		$projectile = new BotPearl($location, $this);
		$projectile->setMotion($this->getDirectionVector()->multiply($item->getThrowForce()));

		$projectileEv = new ProjectileLaunchEvent($projectile);
		$projectileEv->call();
		if($projectileEv->isCancelled()){
			$projectile->flagForDespawn();
			return ItemUseResult::FAIL();
		}

		$projectile->spawnToAll();
		$location->getWorld()->addSound($location, new ThrowSound());
		$item->pop();

		return ItemUseResult::SUCCESS();
	}

	public function calculatePearlPitch() {
		$pitch = 1 - sqrt($this->getLocation()->distanceSquared($this->getTargetEntity()->getPosition()));
		if($pitch <= -40) $pitch /= 1.3;
		if($pitch <= -55) $pitch /= 1.7;
		$this->getLocation()->pitch = $pitch;
	}

	//credit to ethaniccc
	public function isLookingAt(Vector3 $target) : bool{
		$loc = $this->getLocation();
		$horizontal = sqrt(($target->x - $loc->x) ** 2 + ($target->z - $loc->z) ** 2);
		$vertical = $target->y - ($loc->y + $this->getEyeHeight());
		$expectedPitch = -atan2($vertical, $horizontal) / M_PI * 180; //negative is up, positive is down

		$xDist = $target->x - $loc->x;
		$zDist = $target->z - $loc->z;
		$expectedYaw = atan2($zDist, $xDist) / M_PI * 180 - 90;
		if($expectedYaw < 0){
			$expectedYaw += 360.0;
		}

		return abs($expectedPitch - $loc->pitch) <= 5 && abs($expectedYaw - $loc->yaw) <= 10;
	}

	public function isPotting() : bool{
		return $this->potTicks > 0;
	}

	public function debug(string $message){
		$this->getTargetEntity()?->sendMessage(TextFormat::BOLD . TextFormat::GRAY . '[' . TextFormat::RED . 'DEBUG' . TextFormat::GRAY . '] ' . TextFormat::RESET . TextFormat::GRAY . $message);
	}

	public function runAway() : void{
		$loc = $this->getLocation();
		$pitch = $loc->pitch <= -25 || $loc->pitch >= 25 ? 0 : $loc->pitch;
		if($this->isLookingAt($loc)){
			$this->setRotation($loc->yaw + 180, $pitch);
		}
		//$this->move($this->motion->x, $this->motion->y, $this->motion->z);
		//$this->speed = 0.6;
	}

	public function getPots() : int{
		return $this->pots;
	}

	protected function tryChangeMovement() : void {}

	protected function onDeath() : void{
		if($this->hasGame()){
			$game = $this->getGame();
			if(($target = $this->getTargetEntity()) !== null) $game->getPlayer($target)->setRoundWinner();

			$game->endPhase();
		}
	}

	protected function onDeathUpdate(int $tickDiff) : bool{
		$this->deathTicks += $tickDiff;
		if($this->deathTicks >= 5 && !$this->sentDeathAn){
			foreach($this->getViewers() as $viewer){
				$viewer->getNetworkSession()->sendDataPacket(EmotePacket::create($this->getId(), "6d9f24c0-6246-4c92-8169-4648d1981cbb", "", "", EmotePacket::FLAG_MUTE_ANNOUNCEMENT));
			}
			$this->sentDeathAn = true;
		}
		if($this->deathTicks >= 60) return true;
		return false;
	}
	
	public function hasGame() : bool{
		return $this->game !== null;
	}
	
	public function setGame(Game $game) : void{
		$this->game = $game;
	}
	
	public function getGame() : ?Game{
		return $this->game;
	}

}