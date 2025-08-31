<?php

namespace pvp\games\entity;

use pocketmine\entity\Human;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\Server;
use pocketmine\scheduler\ClosureTask;
use pocketmine\entity\Location;
use pocketmine\entity\projectile\EnderPearl as EnderPearlEntity;
use pocketmine\item\EnderPearl as EnderPearlItem;
use pocketmine\item\VanillaItems;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\PotionType;

use core\utils\ItemRegistry;
use pvp\entity\HealthPot;
use pvp\item\HealthPotItem;
use pvp\PvP;

class NodebuffBot extends Bot {

	/** @var int */
	private $hitTick = 0;

	/** @var int */
	private $neededPots = 0;

	/** @var int */
	private $lastPotAttempt = 0;

	/** @var int */
	private $lastPearlAttempt = 0;

	/** @var int */
	private $potionsRemaining = 33;

	/** @var int */
	private $pearlsRemaining = 16;

	public $agro = false;

	public $canAgro = true;

	private $noPot = false;

	private bool $forceNoPearl = false;

	public function constructor(): void {
		$this->giveItems($this);
		$this->giveItems($this->getTargetPlayer());
	}

	public function giveItems(Human $entity): void {
		for ($i = 0; $i <= 35; ++$i) {
			$entity->getInventory()->setItem($i, ItemRegistry::HEALTH_POT());
		}
		$sword = VanillaItems::DIAMOND_SWORD();
		$sword->addEnchantment(new EnchantmentInstance(VanillaEnchantments::FIRE_ASPECT()));
		$entity->getInventory()->setItem(0, $sword);
		$entity->getInventory()->setItem(1, VanillaItems::ENDER_PEARL()->setCount(16));
		$unbreak = function (Item $i): Item {
			$i->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 3));
			return $i;
		};
		$entity->getArmorInventory()->setHelmet($unbreak(VanillaItems::DIAMOND_HELMET()));
		$entity->getArmorInventory()->setChestplate($unbreak(VanillaItems::DIAMOND_CHESTPLATE()));
		$entity->getArmorInventory()->setLeggings($unbreak(VanillaItems::DIAMOND_LEGGINGS()));
		$entity->getArmorInventory()->setBoots($unbreak(VanillaItems::DIAMOND_BOOTS()));
		$entity->getInventory()->setHeldItemIndex(0);
	}

	public function postAttack(): void {
	}

	private ?float $firstPearlWait = null;

	public function doTechnical() {
		$this->firstPearlWait ??= microtime(true);
		if ($this->getHealth() < 5) {
			if ($this->potionsRemaining > 0 && !$this->noPot) {
				$this->pot();
			}
		} else {
			if ($this->getTargetPlayer() === null) {
				$this->flagForDespawn();
				return false;
			} elseif ($this->neededPots === 1) {
				if ($this->potionsRemaining > 0 && !$this->noPot) {
					$this->pot();
				}
			}
		}
		if (Server::getInstance()->getTick() - $this->hitTick >= 60) {
			if ($this->getHealth() <= 10 && !$this->noPot) {
				$this->pot();
			}
		}
		if (($this->combo > 6 || $this->getHealth() <= 3) && !$this->noPearl) {
			if ($this->getHealth() < $this->getTargetPlayer()->getHealth() || $this->getHealth() <= 3 || !($this->canAgro && $this->pearlsRemaining > 5)) $this->safePearl();
			else $this->pearl(true);
		}
		if (($this->getTargetPlayer()->getHealth() < 5 || $this->getDistance() > 20) && $this->getHealth() > 6 && !$this->noPearl && microtime(true) - $this->firstPearlWait > 5) {
			$this->pearl();
		}
	}

	#region PEARLS
	public function safePearl($refillPots = true) {
		if (!$this->isAlive() || (Server::getInstance()->getTick() - $this->lastPearlAttempt < 20)) return;
		$this->lastPearlAttempt = Server::getInstance()->getTick();
		$potsLeft = 0;
		foreach ($this->getInventory()->getContents() as $_ => $item) {
			if ($item instanceof HealthPotItem) $potsLeft++;
		}
		if ($potsLeft < 1) return;
		$refillPots = $refillPots && $potsLeft > 0;
		if ($this->noPearl || $this->forceNoPearl) {
			if ($refillPots) {
				$this->noAttack = true;
				$this->forceNoMovement = true;
				$this->whenForcedNoMove = microtime(true);
				$viablePots = [];
				$need = [];
				foreach ($this->getInventory()->getContents(true) as $slot => $pot) {
					if ($pot instanceof HealthPotItem) $viablePots[] = $slot;
					if ($slot > 1 && $slot < 9 && $pot->equals(VanillaItems::AIR())) $need[] = $slot;
				}
				foreach ($need as $point => $toFill) {
					if (!isset($viablePots[$point])) {
						continue;
					}
					$potItem = $this->getInventory()->getItem($viablePots[$point]);
					if ($potItem instanceof HealthPotItem) {
						$this->getInventory()->setItem($viablePots[$point], VanillaItems::AIR());
						$this->getInventory()->setItem($toFill, $potItem);
					}
				}
				$scope = $this;
				PvP::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($scope): void {
					$scope->noPot = $scope->forceNoMovement = $scope->noAttack = false;
					if ($scope->neededPots > 0) {
						$scope->pot();
						$scope->neededPots--;
					}
				}), round(7.5 * min(count($need), count($viablePots))));
			}
			return;
		}
		$this->noPearl = true;
		$this->setCanPearl(false);
		$this->canAgro = false;
		if ($this->pearlsRemaining <= 0 || !($this->getInventory()->getItem(1) instanceof \pocketmine\item\EnderPearl)) {
			$this->noPearl = true;
			$this->forceNoPearl = true;
			$this->setCanPearl(false);
			$this->canAgro = false;
			if ($refillPots) {
				$this->noAttack = true;
				$this->forceNoMovement = true;
				$this->whenForcedNoMove = microtime(true);
				$viablePots = [];
				$need = [];
				foreach ($this->getInventory()->getContents(true) as $slot => $pot) {
					if ($pot instanceof HealthPotItem) $viablePots[] = $slot;
					if ($slot > 1 && $slot < 9 && $pot->equals(VanillaItems::AIR())) $need[] = $slot;
				}
				foreach ($need as $point => $toFill) {
					if (!isset($viablePots[$point])) continue;
					$potItem = $this->getInventory()->getItem($viablePots[$point]);
					if ($potItem instanceof HealthPotItem) {
						$this->getInventory()->setItem($viablePots[$point], VanillaItems::AIR());
						$this->getInventory()->setItem($toFill, $potItem);
					}
				}
				$scope = $this;
				PvP::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($scope): void {
					$scope->noPot = $scope->forceNoMovement = $scope->noAttack = false;
					if ($scope->neededPots > 0) {
						$scope->pot();
						$scope->neededPots--;
					}
				}), round(7.5 * min(count($need), count($viablePots))));
			}
			return;
		}
		$this->getInventory()->setHeldItemIndex(1);
		$item = $this->getInventory()->getItemInHand();
		$pearlTo = $this->getPosition()->subtractVector($this->locationToDirectionVector($this->fakeLookAt($this->getTargetPlayer()->getLocation()))->multiply(2));
		$pearlTo->y += (($this->getTargetPlayer()->getPosition()->getY() + 2) - $this->getPosition()->getY()) / 4;
		$this->lookAt($pearlTo);
		if ($item instanceof EnderPearlItem) {
			$pearl = new EnderPearlEntity(Location::fromObject($this->getEyePos()->addVector($this->getDirectionVector()->multiply(0.25)), $this->getWorld(), $this->getLocation()->getYaw(), $this->getLocation()->getPitch()), $this);
			$pearl->spawnToAll();
			$pearl->setMotion($this->getDirectionVector()->multiply($item->getThrowForce()));
			$item->pop();
		}
		$this->noAttack = true;
		$this->noPearl = true;
		$this->noPot = true;
		if ($refillPots) {
			$this->forceNoPearl = true;
			$this->noAttack = true;
			$this->forceNoMovement = true;
			$this->whenForcedNoMove = microtime(true);
			$viablePots = [];
			$need = [];
			foreach ($this->getInventory()->getContents(true) as $slot => $pot) {
				if ($pot instanceof HealthPotItem) $viablePots[] = $slot;
				if ($slot > 1 && $slot < 9 && $pot->equals(VanillaItems::AIR())) $need[] = $slot;
			}
			foreach ($need as $point => $toFill) {
				if (!isset($viablePots[$point])) continue;
				$potItem = $this->getInventory()->getItem($viablePots[$point]);
				if ($potItem instanceof HealthPotItem) {
					$this->getInventory()->setItem($viablePots[$point], VanillaItems::AIR());
					$this->getInventory()->setItem($toFill, $potItem);
				}
			}
			$scope = $this;
			PvP::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($scope): void {
				$scope->noPot = $scope->forceNoMovement = $scope->noAttack = $scope->forceNoPearl = false;
				if ($scope->neededPots > 0) {
					$scope->pot();
					$scope->neededPots--;
				}
			}), round(7.5 * min(count($need), count($viablePots))));
			PvP::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($scope): void {
				$scope->noPearl = false;
				$scope->setCanPearl(true);
				$scope->canPearl = true;
			}), 10 * 20);
		} else {
			$scope = $this;
			PvP::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($scope): void {
				$scope->noAttack = false;
			}), 8);
			PvP::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($scope): void {
				$scope->noPot = false;
			}), 15);
			PvP::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($scope): void {
				$scope->noPearl = false;
				$scope->setCanPearl(true);
				$scope->canPearl = true;
			}), 10 * 20);
			$this->setCanPearl(false);
		}
		$this->pearlsRemaining--;
	}

	public function pearl(bool $agro = false) {
		if (!$this->isAlive() || (Server::getInstance()->getTick() - $this->lastPearlAttempt < 20)) return;
		$this->lastPearlAttempt = Server::getInstance()->getTick();
		if ($this->pearlsRemaining <= 0) {
			$this->noPearl = true;
			$this->setCanPearl(false);
			$this->canAgro = false;
			return;
		}
		if ($this->noPearl || $this->forceNoPearl) return;
		$this->noPearl = true;
		$this->setCanPearl(false);
		$this->agro = $agro;
		if ($this->agro) $this->canAgro = false;
		$this->getInventory()->setHeldItemIndex(1);
		$item = $this->getInventory()->getItemInHand();
		$pearlTo = Location::fromObject($this->getTargetPlayer()->getEyePos(), $this->getTargetPlayer()->getWorld(), $this->getTargetPlayer()->getLocation()->getYaw(), $this->getTargetPlayer()->getLocation()->getPitch());
		$this->lookAt($pearlTo);
		if ($item instanceof EnderPearlItem) {
			$this->setRotation($this->getLocation()->getYaw(), $this->getLocation()->getPitch() - ($this->getDistance() / $item->getThrowForce() * 2));
			$pearl = new EnderPearlEntity(Location::fromObject($this->getEyePos()->addVector($this->getDirectionVector()->multiply(0.25)), $this->getWorld(), $this->getLocation()->getYaw(), $this->getLocation()->getPitch()), $this);
			$pearl->spawnToAll();
			$pearl->setMotion($this->getDirectionVector()->multiply($item->getThrowForce()));
			$item->pop();
		}
		$this->noPot = true;
		$this->noAttack = true;
		$this->noPearl = true;
		$scope = $this;
		PvP::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($scope): void {
			$scope->noAttack = false;
		}), 8);
		PvP::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($scope): void {
			$scope->noPot = false;
		}), 30);
		PvP::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($scope): void {
			$scope->noPearl = false;
			$scope->setCanPearl(true);
			$scope->canPearl = true;
		}), 10 * 20);
		$this->setCanPearl(false);
		$this->agro = $agro;
		if ($this->agro) $this->canAgro = false;
		$this->pearlsRemaining--;
	}
	#endregion

	#region POTS
	public function pot(): void {
		if (!$this->isAlive() || (Server::getInstance()->getTick() - $this->lastPotAttempt < 12)) return;
		$this->lastPotAttempt = Server::getInstance()->getTick();
		if ($this->getLocation()->yaw < 0) {
			$this->getLocation()->yaw = abs($this->getLocation()->yaw);
		} elseif ($this->getLocation()->yaw == 0) {
			$this->getLocation()->yaw = -180;
		} else {
			$this->getLocation()->yaw = -$this->getLocation()->yaw;
		}
		$potsLeft = 0;
		foreach ($this->getInventory()->getContents() as $_ => $item) {
			if ($item instanceof HealthPotItem) $potsLeft++;
		}
		if ($potsLeft < 1 || $this->noPot) return;
		$this->noPot = true;
		$this->noAttack = true;
		$heldSlot = null;
		foreach ($this->getInventory()->getContents(true) as $slot => $pot) {
			if ($slot > 1 && $slot < 9) {
				if ($pot instanceof HealthPotItem) {
					$heldSlot = $slot;
					break;
				}
			}
		}
		++$this->neededPots;
		if (is_null($heldSlot)) {
			$this->safePearl(true);
			return;
		}
		$this->forceNoPearl = true;
		$this->getInventory()->setHeldItemIndex($heldSlot);
		$entity = new HealthPot(new Location($this->getPosition()->getX(), $this->getPosition()->getY() + 1.5, $this->getPosition()->getZ(), $this->getWorld(), $this->getLocation()->getYaw(), $this->getLocation()->getPitch()), $this, PotionType::STRONG_HEALING());
		$entity->setMotion(new Vector3(($this->motion->x / 1.25), $this->motion->y - 0.15, ($this->motion->z / 1.25)));
		$this->getInventory()->removeItem($this->getInventory()->getItemInHand());
		$entity->spawnToAll();
		$scope = $this;
		PvP::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($entity, $scope): void {
			$scope->lookAt($entity->getPosition()->asVector3());
		}), 1);
		--$this->potionsRemaining;
		--$this->neededPots;
		PvP::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($scope): void {
			$scope->noPot = false;
		}), 25);
		PvP::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($scope): void {
			$scope->forceNoPearl = false;
		}), 40);
		PvP::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($scope): void {
			$scope->noAttack = false;
		}), 15);
	}
	#endregion
}
