<?php

namespace pvp\games\entity;

use core\AtPlayer;
use pocketmine\entity\Attribute;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\Entity;
use pocketmine\entity\Human;
use pocketmine\entity\Living;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\entity\Location;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\item\Sword;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\RayTraceResult;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

use core\utils\Ray;

use pvp\games\entity\type\BotSettings;
use pvp\games\type\Game;
use pvp\PvP;

abstract class Bot extends Human {

	public Game $game;

	protected float $jumpVelocity = 0.36;

	/** @var int */
	private $hitTick = 0;

	/** @var float */
	protected $speed = 0.47;

	/** @var bool */
	protected $noMovement = false;

	/** @var bool */
	protected $noAirAccelerate = false;

	/** @var ClosureTask */
	protected $kbTask;

	/** @var ClosureTask */
	private $noMoveTask;

	/** @var ClosureTask */
	private $airAccelerateTask;

	protected $canPearl = true;
	protected $noPearl = false;

	/** @var int */
	protected $combo = 0;

	/** @var Human */
	protected $target = "";

	protected bool $dummy = false;

	private float $lastHit;

	private string $name;

	protected int $difficulty = 0;

	protected bool $forceNoMovement = false;
	protected float $whenForcedNoMove = 0;

	/** @var bool */
	protected bool $noAttack = false;

	protected bool $canStrafe;
	protected float $reach;
	protected string $displayName;

	private string $strafeDir;

	public $attackCooldown = 0;

	protected function getInitialGravity(): float {
		return 0.0676;
	}

	/**
	 * Bot constructor
	 *
	 * @param Location $level
	 * @param Player $target
	 * @param BotSettings $settings
	 */
	public function __construct(Location $level, Player $target, BotSettings $settings) {
		parent::__construct($level, $target->getSkin());
		$this->target = $target;
		$this->name = "Bot_" . mt_rand();
		$this->strafeDir = ['r', 'l'][array_rand(['r', 'l'])];
		$this->lastHit = microtime(true);
		$this->constructor();
		[$this->reach, $this->canStrafe, $this->displayName, $this->game] = [$settings->reach, $settings->strafe, $settings->displayName, $settings->game];
		$this->setCanSaveWithChunk(false);
	}

	protected function onDeath(): void {
		$this->game?->getPlayer($this->target)->setRoundWinner();
		$this->game?->endPhase();
	}

	abstract public function constructor(): void;
	abstract public function postAttack(): void;
	abstract public function doTechnical();

	public function getName(): string {
		return $this->name;
	}

	public function getDisplayName(): string {
		return $this->displayName;
	}

	public function getTargetPlayer(): Player {
		return $this->target;
	}

	public function jump(): void {
		$motion = clone $this->motion;
		$motion->y = $this->getJumpVelocity();
		$this->setMotion($motion);
	}

	public function doMovement() {
		$position = $this->getTargetPlayer()->getPosition()->asVector3();
		if (!$this->noMovement && !$this->forceNoMovement) {
			$this->motion->z = ($this->getDirectionVector()->z * $this->getMovementSpeed());
			$this->motion->x = ($this->getDirectionVector()->x * $this->getMovementSpeed());
			if (!$this->isOnGround() && !$this->noAirAccelerate) {
				$this->motion->x *= (1 + $this->drag);
				$this->motion->z *= (1 + $this->drag);
			}
			if ($this->getDistance() < 4.5 && abs($this->getPosition()->getY() - $this->getTargetPlayer()->getPosition()->getY()) < 1 && $this->combo < 3 && $this->getHealth() >= 5 && $this->canStrafe) {
				$fakeLook = $this->fakeLookAt($position);
				if ($this->strafeDir == 'r') {
					$fakeLook->yaw += 37.5;
					$strafe = $this->locationToDirectionVector($fakeLook);
					$this->motion->z = ($strafe->z * $this->getMovementSpeed());
					$this->motion->x = ($strafe->x * $this->getMovementSpeed());
				} elseif ($this->strafeDir == 'l') {
					$fakeLook->yaw -= 37.5;
					$strafe = $this->locationToDirectionVector($fakeLook);
					$this->motion->z = ($strafe->z * $this->getMovementSpeed());
					$this->motion->x = ($strafe->x * $this->getMovementSpeed());
				}
			} elseif ($this->getDistance() > 3.5 && $this->canStrafe) {
				$this->strafeDir = ['r', 'l'][array_rand(['r', 'l'])];
			}
			$ping = 0.002 * $this->getTargetPlayer()->getNetworkSession()->getPing() + rand(0, 10) / 100;
			if (($this->getDistance() < 2.8 - $ping || $this->combo >= 3) && $this->isOnGround()) {
				$this->motion->x = 0;
				$this->motion->z = 0;
				$this->lookAt($this->getPosition()->subtractVector($this->locationToDirectionVector($this->fakeLookAt($position))->multiply(2)));
			} else {
				$this->lookAt($position);
			}
			if ($this->getDistance() > 3.3 - $ping && $this->getDistance() <= 4.75 - $ping && $this->getTargetPlayer()->getPosition()->getY() - 1 > $this->getEyePos()->getY()) {
				if ($this->isOnGround()) {
					parent::jump();
				}
			}
		}
		if (!$this->noMovement && !$this->forceNoMovement) {
			$this->setSprinting(true);
			$this->move($this->motion->x, $this->motion->y, $this->motion->z);
		} else {
			$this->setSprinting(false);
		}
	}

	public function dummyTask(int $tickDiff = 1): bool {
		$redbars = $this->getMaxHealth() - $this->getHealth();

		if ($redbars >= 0) {
			$health = str_repeat("§a|", (int)round($this->getHealth(), 0)) . str_repeat("§c|", (int)round($redbars, 0));
		} else {
			$health = str_repeat("§c|", 20);
		}
		$this->setScoreTag($health);

		$this->setHealth($this->getHealth() + 0.5);
		$this->getHungerManager()->setFood($this->getHungerManager()->getMaxFood());
		return parent::entityBaseTick($tickDiff);
	}

	public function entityBaseTick(int $tickDiff = 1): bool {
		if ($this->isOnGround()) $this->combo = 0;
		if ($this->isDummy()) return $this->dummyTask($tickDiff);
		if (!($this->game?->isStarted() ?? false)) return parent::entityBaseTick($tickDiff);
		if (microtime(true) - $this->whenForcedNoMove > 3.5) $this->forceNoMovement = false;
		$this->setNameTag($this->getDisplayName());
		$redbars = $this->getMaxHealth() - $this->getHealth();
		if ($redbars >= 0) {
			$health = str_repeat(TextFormat::GREEN . '|', (int)round($this->getHealth())) . str_repeat(TextFormat::RED . '|', (int)round($redbars));
		} else {
			$health = str_repeat(TextFormat::GREEN . '|', $this->getMaxHealth());
		}
		$this->setScoreTag($health);
		--$this->attackCooldown;
		if ($this->attackCooldown < 0) $this->attackCooldown = 0;
		$hasUpdate = parent::entityBaseTick($tickDiff);
		if (!$this->isAlive() || !$this->getTargetPlayer()->isOnline()) {
			if (!$this->closed) $this->flagForDespawn();
			return false;
		}
		if (!$this->getTargetPlayer() || !$this->getTargetPlayer()->isOnline()) return $hasUpdate;
		$this->doMovement();
		$this->doTechnical();
		if (!$this->noAttack) {
			$this->attackTargetPlayer();
		}
		$this->getTargetPlayer()->getHungerManager()->setFood($this->getTargetPlayer()->getHungerManager()->getMaxFood());
		$this->getHungerManager()->setFood($this->getHungerManager()->getMaxFood());
		return $hasUpdate;
	}

	public function fakeLookAt(Vector3 $target): Location {
		$location = new Location($target->x, $target->y, $target->z, $this->location->world, $this->location->yaw, $this->location->pitch);
		$horizontal = sqrt(($target->x - $this->location->x) ** 2 + ($target->z - $this->location->z) ** 2);
		$vertical = $target->y - ($this->location->y + $this->getEyeHeight());
		$location->pitch = -atan2($vertical, $horizontal) / M_PI * 180; //negative is up, positive is down

		$xDist = $target->x - $this->location->x;
		$zDist = $target->z - $this->location->z;
		$location->yaw = atan2($zDist, $xDist) / M_PI * 180 - 90;
		if ($location->yaw < 0) {
			$location->yaw += 360.0;
		}
		return $location;
	}

	public function fakeRotate(float $pitch = 0, float $yaw = 0, ?Location $current = null): Location {
		$clone = clone ($current ??= $this->getLocation());
		$clone->pitch += $pitch;
		$clone->yaw += $yaw;
		return $clone;
	}

	public function setCanPearl(bool $value) {
		$this->canPearl = $value;
		$this->noPearl = !$value;
	}

	public function locationToDirectionVector(Location $location): Vector3 {
		$y = -sin(deg2rad($location->pitch));
		$xz = cos(deg2rad($location->pitch));
		$x = -$xz * sin(deg2rad($location->yaw));
		$z = $xz * cos(deg2rad($location->yaw));

		return (new Vector3($x, $y, $z))->normalize();
	}

	public function setDummy(bool $v) {
		$this->dummy = $v;
	}

	public function isDummy() {
		return $this->dummy;
	}

	/**
	 * @param Entity $attacker
	 * @param float $damage
	 * @param float $x
	 * @param float $z
	 * @param float $base
	 */
	public function knockBack(float $x, float $z, float $force = Living::DEFAULT_KNOCKBACK_FORCE, ?float $verticalLimit = Living::DEFAULT_KNOCKBACK_VERTICAL_LIMIT): void {
		if ($x == 0 && $z == 0) {
			if (isset($this->noMoveTask) && !is_null($this->noMoveTask->getHandler())) $this->noMoveTask->getHandler()->cancel();
			if (isset($this->airAccelerateTask) && !is_null($this->airAccelerateTask->getHandler())) $this->airAccelerateTask->getHandler()->cancel();
			if (isset($this->kbTask) && !is_null($this->kbTask->getHandler())) $this->kbTask->getHandler()->cancel();
			$this->noAirAccelerate = false;
			$this->noMovement = false;
			$this->noAttack = false;
			return;
		}
		$force = (0.7 / 0.4) * $force;
		$verticalLimit = (0.4875 / 0.4) * $verticalLimit;
		$f = sqrt($x * $x + $z * $z);
		if ($f <= 0) {
			return;
		}
		if (mt_rand() / mt_getrandmax() > $this->getAttributeMap()->get(Attribute::KNOCKBACK_RESISTANCE)->getValue()) {
			$f = 1 / $f;

			$motion = clone $this->motion;

			$motion->x /= 2;
			$motion->y /= 2;
			$motion->z /= 2;
			$motion->x += $x * $f * $force;
			$motion->y += $force;
			$motion->z += $z * $f * $force;

			$verticalLimit ??= $force;
			if ($motion->y > $verticalLimit) {
				$motion->y = $verticalLimit;
			}

			$this->setMotion($motion);
			if (isset($this->noMoveTask) && !is_null($this->noMoveTask->getHandler())) $this->noMoveTask->getHandler()->cancel();
			if (isset($this->airAccelerateTask) && !is_null($this->airAccelerateTask->getHandler())) $this->airAccelerateTask->getHandler()->cancel();
			if (isset($this->kbTask) && !is_null($this->kbTask->getHandler())) $this->kbTask->getHandler()->cancel();
			$this->noMoveTask = new ClosureTask(function (): void {
				$this->noMovement = false;
				$this->kbTask->getHandler()?->cancel();
			});
			$this->airAccelerateTask = new ClosureTask(function (): void {
				$this->noAirAccelerate = false;
			});
			$this->kbTask = new ClosureTask(function (): void {
				$this->motion->x *= 0.625;
				$this->motion->z *= 0.625;
			});
			$this->noMovement = true;
			$this->noAirAccelerate = true;
			PvP::getInstance()->getScheduler()->scheduleRepeatingTask($this->kbTask, 2);
			PvP::getInstance()->getScheduler()->scheduleDelayedTask($this->noMoveTask, 8);
			PvP::getInstance()->getScheduler()->scheduleDelayedTask($this->airAccelerateTask, 12);
		}
		$this->combo++;
		$this->lastHit = microtime(true);
	}

	public function attackTargetPlayer(): void {
		$ping = rand(0, 60) / 100;
		if ($this->getTargetPlayer() instanceof Player) {
			$ping = 0.002 * $this->getTargetPlayer()->getNetworkSession()->getPing() + rand(0, 10) / 100;
		}
		$xDist = $this->getTargetPlayer()->getPosition()->x - $this->location->x;
		$zDist = $this->getTargetPlayer()->getPosition()->z - $this->location->z;
		$yaw = atan2($zDist, $xDist) / M_PI * 180 - 90;
		if ($yaw < 0) {
			$yaw += 360.0;
		}
		$canHit = false;
		$nausea = $this->getEffects()->get(VanillaEffects::NAUSEA())?->getEffectLevel() ?? 0;
		$this->lookAt($this->getTargetPlayer()->getPosition()->asVector3());
		$this->location->yaw += ($nausea * (rand(-3, 3) / 2));
		$this->location->pitch += ($nausea * (rand(-3, 3) / 2));
		if ($this->getPosition()->getY() > $this->getTargetPlayer()->getPosition()->getY() + 0.1) $this->lookAt($this->getTargetPlayer()->getEyePos()->asVector3()->subtract(0, 0.15, 0));
		$target = new AxisAlignedBB($this->getTargetPlayer()->getPosition()->x - ($this->getTargetPlayer()->size->getWidth() / 2), $this->getTargetPlayer()->getPosition()->y, $this->getTargetPlayer()->getPosition()->z - ($this->getTargetPlayer()->size->getWidth() / 2), $this->getTargetPlayer()->getPosition()->x + ($this->getTargetPlayer()->size->getWidth() / 2), $this->getTargetPlayer()->getPosition()->y + $this->getTargetPlayer()->size->getHeight(), $this->getTargetPlayer()->getPosition()->z + ($this->getTargetPlayer()->size->getWidth() / 2));
		$target->expand(0.05 + ($ping * 0.0045), 0.05 + ($ping * 0.0045), 0.05 + ($ping * 0.0045));
		$playerBox = new AxisAlignedBB($this->getPosition()->x - ($this->size->getWidth() / 2), $this->getPosition()->y, $this->getPosition()->z - ($this->size->getWidth() / 2), $this->getPosition()->x + ($this->size->getWidth() / 2), $this->getPosition()->y + $this->size->getHeight(), $this->getPosition()->z + ($this->size->getWidth() / 2));
		$headLocation = $this->getLocation();
		$headLocation->yaw = $this->getLocation()->yaw;
		$totalMotion = sqrt((sqrt(($this->getMotion()->x ** 2) + ($this->getMotion()->z ** 2)) ** 2) + ($this->getMotion()->y ** 2));
		$rayOrigin = $this->getEyePos()->addVector($this->locationToDirectionVector($headLocation)->multiply($totalMotion));
		$ray = new Ray($rayOrigin, $this->locationToDirectionVector($headLocation));
		$tracing = null;
		if ($target->intersectsWith($playerBox)) {
			$canHit = true;
			$tracing = new RayTraceResult($target, Facing::UP, $this->getPosition());
		}
		$target = new AxisAlignedBB($this->getTargetPlayer()->getPosition()->x - ($this->getTargetPlayer()->size->getWidth() / 2), $this->getTargetPlayer()->getPosition()->y, $this->getTargetPlayer()->getPosition()->z - ($this->getTargetPlayer()->size->getWidth() / 2), $this->getTargetPlayer()->getPosition()->x + ($this->getTargetPlayer()->size->getWidth() / 2), $this->getTargetPlayer()->getPosition()->y + $this->getTargetPlayer()->size->getHeight(), $this->getTargetPlayer()->getPosition()->z + ($this->getTargetPlayer()->size->getWidth() / 2));
		if ($this->getPosition()->getY() + 0.25 > $this->getTargetPlayer()->getEyePos()->getY() - 0.25) $target->contract($target->getXLength() / 3.8, $target->getYLength() / 4, $target->getZLength() / 3.8);
		if ($this->getTargetPlayer()->getPosition()->getY() > $this->getPosition()->getY() + 0.2) $target->expand($target->getXLength() / 6, $target->getYLength() / 6, $target->getZLength() / 6);
		/** @var RayTraceResult */
		if (is_null($tracing)) $tracing = $target->calculateIntercept($ray->getOrigin(), $ray->traverse(10));
		$canHit = $canHit || !is_null($tracing);
		if (Server::getInstance()->getTick() - $this->hitTick >= 14 / 20 && $this->getDistance() < 6 && !$this->noAttack) {
			$this->getInventory()->setHeldItemIndex(0);
			$this->broadcastAnimation(new \pocketmine\entity\animation\ArmSwingAnimation($this));
		}
		if ($canHit && $this->getEyePos()->distance($tracing->getHitVector()) <= $this->reach - $ping && !$this->noAttack) {
			if (Server::getInstance()->getTick() - $this->hitTick >= 14 / 20) {
				$event = new EntityDamageByEntityEvent($this, $this->getTargetPlayer(), EntityDamageByEntityEvent::CAUSE_ENTITY_ATTACK, $this->getInventory()->getItemInHand() instanceof Sword ? $this->getInventory()->getItemInHand()->getAttackPoints() : 0.5);
				$this->getTargetPlayer()->attack($event);

				if ($event->isCancelled()) {
					$this->getTargetPlayer()->broadcastSound(new \pocketmine\world\sound\EntityAttackNoDamageSound());
				} else {
					$this->combo = 0;
					$this->postAttack();
				}
				$this->hitTick = Server::getInstance()->getTick();
			}
		}
	}

	/**
	 * @return bool
	 */
	public function canMove(): bool {
		return $this->noMovement;
	}

	/**
	 * @return float
	 */
	public function getHitTick(): float {
		return $this->hitTick;
	}

	public function getSpeed(): float {
		return $this->speed;
	}

	/**
	 * @param Vector3 $target
	 * @return bool
	 */
	public function isLookingAt(Vector3 $target): bool {
		$horizontal = sqrt(($target->x - $this->getPosition()->x) ** 2 + ($target->z - $this->getPosition()->z) ** 2);
		$vertical = $target->y - $this->getPosition()->y;
		$expectedPitch = -atan2($vertical, $horizontal) / M_PI * 180; //negative is up, positive is down

		$xDist = $target->x - $this->getPosition()->x;
		$zDist = $target->z - $this->getPosition()->z;
		$expectedYaw = atan2($zDist, $xDist) / M_PI * 180 - 90;
		if ($expectedYaw < 0) {
			$expectedYaw += 360.0;
		}

		return abs($expectedPitch - $this->getLocation()->getPitch()) <= 5 && abs($expectedYaw - $this->getLocation()->getYaw()) <= 10;
	}

	public function setDifficulty(int $dif) {
		$this->difficulty = $dif;
		$this->speed -= $dif * 0.06;
	}

	public function getDistance(): float {
		return $this->getEyePos()->distance($this->getTargetPlayer()->getPosition()) + ($this->difficulty * 0.25);
	}

	public function setNoClientPredictions(bool $value = true): void {
		$this->forceNoMovement = $value;
		$this->whenForcedNoMove = microtime(true);
	}

	public function hasNoClientPredictions(): bool {
		return $this->forceNoMovement;
	}
}
