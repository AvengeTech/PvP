<?php

namespace pvp\games\entity;

use pocketmine\entity\Human;
use pocketmine\item\Sword;
use pocketmine\scheduler\ClosureTask;
use pocketmine\item\VanillaItems;
use pocketmine\entity\animation\ConsumingItemAnimation;
use pocketmine\item\GoldenAppleEnchanted;

use pvp\PvP;

class GodBot extends Bot
{

	/** @var int */
	private $egapsLeft = 6;

	/** @var float */
	private $lastGap;

	public $attackCooldown = 0;

	public function constructor(): void
	{
		$this->giveItems($this);
		$this->giveItems($this->getTargetPlayer());
	}

	public function giveItems(Human $entity): void
	{
		/** @var Sword */
		$sword = VanillaItems::NETHERITE_SWORD();
		//add enchantments to sword
		$entity->getInventory()->setHeldItemIndex(0);
		$entity->getInventory()->setItemInHand($sword);
		$entity->getInventory()->setItem(1, VanillaItems::ENCHANTED_GOLDEN_APPLE()->setCount(6));
		$armorInv = $entity->getArmorInventory();
		$armorInv->setHelmet(
			VanillaItems::NETHERITE_HELMET() //add enchantments
		);
		$armorInv->setChestplate(
			VanillaItems::NETHERITE_CHESTPLATE() //add enchantments
		);
		$armorInv->setLeggings(
			VanillaItems::NETHERITE_LEGGINGS() //add enchantments
		);
		$armorInv->setBoots(
			VanillaItems::NETHERITE_BOOTS() //add enchantments
		);
	}

	public function postAttack(): void
	{
		/*
			Activate CE's here
		*/
	}

	public function doTechnical()
	{
		if ($this->egapAvailable()) {
			$this->egap();
		}
	}

	public function egapAvailable(): bool
	{
		return microtime(true) - ($this->lastGap ?? (microtime(true) - 35)) >= 27.5 && ($this->combo > 0 || $this->getHealth() < $this->getMaxHealth());
	}

	public function egap()
	{
		$this->lastGap = microtime(true) + (35 / 20);
		$this->noAttack = true;
		$this->getInventory()->setHeldItemIndex(1);
		if (!($this->getInventory()->getItemInHand() instanceof GoldenAppleEnchanted)) return;
		$task = PvP::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(function (): void {
			if ($this->getInventory()->getItemInHand()->getTypeId() !== 0) $this->broadcastAnimation(new ConsumingItemAnimation($this, $this->getInventory()->getItemInHand()));
		}), 7);
		PvP::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($task): void {
			if (!$task->isCancelled()) $task->cancel();
			if (!$this->isAlive()) return;
			$this->getInventory()->removeItem(VanillaItems::ENCHANTED_GOLDEN_APPLE());
			$this->applyConsumptionResults(VanillaItems::ENCHANTED_GOLDEN_APPLE());
			$this->egapsLeft--;
			$this->noAttack = false;
			return;
		}), 38);
	}
}
