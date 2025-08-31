<?php

namespace pvp\enchantments;

use pocketmine\event\{
	entity\EntityDamageByEntityEvent,
	entity\EntityShootBowEvent,
};
use pocketmine\block\VanillaBlocks;

use pocketmine\entity\Location;
use pocketmine\world\{
	Position,
	particle\ExplodeParticle,
	particle\FlameParticle,
	particle\SplashParticle,
	particle\BlockBreakParticle as DestroyBlockParticle,

	sound\AnvilFallSound,
	sound\LaunchSound
};
use pocketmine\network\mcpe\protocol\{
	AddActorPacket,
};

use pocketmine\entity\effect\{
	EffectInstance,
	VanillaEffects
};
use pocketmine\entity\Entity;
use pocketmine\entity\Living;
use pocketmine\entity\projectile\Arrow;
use pocketmine\world\sound\ExplodeSound;
use pocketmine\player\Player;

use pvp\PvPPlayer;
use pvp\enchantments\entities\{
	bow\EnderArrow,
	bow\PerishArrow,
	bow\SniperArrow,
	bow\InstaShotArrow
};

use core\utils\{
	PlaySound
};

class Calls extends EnchantmentData {

	public $event = [];

	public $equip = [];
	public $unequip = [];

	public $task = [];

	public function __construct() {
		$this->event = [
			self::ZEUS => function (EntityDamageByEntityEvent $e, $level) {
				/** @var PvPPlayer $hurt */
				/** @var PvPPlayer $killer */
				$hurt = $e->getEntity();
				$killer = $e->getDamager();
				$chance = mt_rand(1, 100) <= ($level * 5);
				if ($chance) {
					$this->strikeLightning($hurt->getPosition());
					$e->setBaseDamage($e->getBaseDamage() * (($level / 5) + 1));
				}
			},
			self::KEY_THEFT => function (EntityDamageByEntityEvent $e, $level) {
				return;
				switch ($level) {
					default:
					case 1:
						$chance = mt_rand(1, 15) == 1;
						break;
					case 2:
						$chance = mt_rand(1, 10) == 1;
						break;
					case 3:
						$chance = mt_rand(1, 5) == 1;
						break;
				}


				/*
				$player = $e->getEntity();
				$killer = $e->getDamager();
				if($chance && $e->getFinalDamage() >= $player->getHealth()){
					if($player instanceof Player){
						$stole = [
							"iron" => 0,
							"gold" => 0,
							"diamond" => 0,
							"emerald" => 0,
							"vote" => 0
						];

						$mb = PvP::getInstance()->getMysteryBoxes();

						for($i = 1; $i <= $max = mt_rand(1, 3); $i++){
							$keytype = $this->getRandomKeyType($player, $stole);
							if($keytype !== false){
								$mb->getSessionManager()->getSession($player)->takeKeys($keytype, ($amt = mt_rand(1, $level)));
								$mb->getSessionManager()->getSession($killer)->addKeys($keytype, $amt);
								$stole[$keytype]++;
							}
						}

						$count = 0;
						foreach($stole as $type => $amount){
							if($amount <= 0){
								unset($stole[$type]);
							}else{
								$count += $amount;
							}
						}

						if($count > 0 && $killer instanceof Player){
							$killer->sendMessage(TextFormat::AQUA . "Stole " . TextFormat::YELLOW . $count . " keys " . TextFormat::AQUA . "from " . TextFormat::RED . $player->getName() . ":");
							foreach($stole as $type => $amount){
								$killer->sendMessage(TextFormat::GRAY . " - " . TextFormat::GREEN . "x" . $amount . " " . $type . " keys");
							}
						}
					}
				}*/
			},
			self::LIFESTEAL => function (EntityDamageByEntityEvent $e, $level) {
				switch ($level) {
					default:
					case 1:
						$chance = mt_rand(1, 100) <= 10;
						break;
					case 2:
						$chance = mt_rand(1, 100) <= 15;
						break;
				}

				if ($chance) {
					$e->getDamager()->setHealth($e->getDamager()->getHealth() + ($e->getBaseDamage() / 2));
				}
			},
			self::KABOOM => function (EntityDamageByEntityEvent $e, $level) {
				switch ($level) {
					default:
					case 1:
						$chance = mt_rand(1, 100) <= 3;
						break;
					case 2:
						$chance = mt_rand(1, 100) <= 4;
						break;
					case 3:
						$chance = mt_rand(1, 100) <= 8;
						break;
				}

				if ($chance) {
					$this->explosion($e->getEntity()->getPosition(), $level);
					$e->setBaseDamage($e->getBaseDamage() * (($level / 5) + 1));
					$e->setKnockback($e->getKnockback() * 1.5);
				}
			},
			self::HADES => function (EntityDamageByEntityEvent $e, $level) {
				switch ($level) {
					default:
					case 1:
						$chance = mt_rand(1, 100) <= 5;
						break;
					case 2:
						$chance = mt_rand(1, 100) <= 9;
						break;
					case 3:
						$chance = mt_rand(1, 100) <= 15;
						break;
				}

				if ($chance) {
					$killer = $e->getDamager();
					for ($i = 1; $i <= $level * 3; $i++) {
						$killer->getWorld()->addParticle($killer->getPosition()->add(mt_rand(-10, 10) * 0.1, mt_rand(0, 20) * 0.1, mt_rand(-10, 10) * 0.1), new FlameParticle());
					}
					$e->getEntity()->setOnFire($level * mt_rand(1, 2));
					$e->setBaseDamage($e->getBaseDamage() + ($level * 0.8));
				}
			},
			self::OOF => function (EntityDamageByEntityEvent $e, $level) {
				if (mt_rand(1, 3) == 1) {
					/** @var PvPPlayer $entity */
					$entity = $e->getEntity();
					foreach ($e->getEntity()->getViewers() as $viewer) {
						/** @var PvPPlayer $viewer */
						$viewer->playSound("random.hurt", $e->getEntity()->getPosition());
					}
					if ($entity instanceof Player) $entity->playSound("random.hurt");
				}
			},
			self::FROST => function (EntityDamageByEntityEvent $e, $level) {
				switch ($level) {
					default:
					case 1:
						$chance = mt_rand(1, 100) <= 4;
						break;
					case 2:
						$chance = mt_rand(1, 100) <= 8;
						break;
					case 3:
						$chance = mt_rand(1, 100) <= 12;
						break;
				}

				if ($chance) {
					if (($entity = $e->getEntity()) instanceof Living) {
						/** @var Living $entity */
						$entity->getEffects()->add(new EffectInstance(VanillaEffects::SLOWNESS(), 20 * ($level * 4)));
					}
				}
			},
			self::DAZE => function (EntityDamageByEntityEvent $e, $level) {
				switch ($level) {
					default:
					case 1:
						$chance = mt_rand(1, 100) <= 5;
						break;
					case 2:
						$chance = mt_rand(1, 100) <= 8;
						break;
					case 3:
						$chance = mt_rand(1, 100) <= 13;
						break;
				}

				if ($chance) {
					if (($entity = $e->getEntity()) instanceof Living) {
						/** @var Living $entity */
						$entity->getEffects()->add(new EffectInstance(VanillaEffects::NAUSEA(), 20 * ($level + (2 * $level)), $level - 1));
					}
				}
			},
			self::POISON => function (EntityDamageByEntityEvent $e, $level) {
				switch ($level) {
					default:
					case 1:
						$chance = mt_rand(1, 100) <= 5;
						break;
					case 2:
						$chance = mt_rand(1, 100) <= 8;
						break;
				}

				if ($chance) {
					if (($entity = $e->getEntity()) instanceof Living) {
						/** @var Living $entity */
						$entity->getEffects()->add(new EffectInstance(VanillaEffects::POISON(), 20 * ($level * 3), $level - 1));
					}
				}
			},
			self::UPLIFT => function (EntityDamageByEntityEvent $e, $level) {
				if (mt_rand(1, 5) == 1) {
					$e->setBaseDamage($e->getBaseDamage() + 1);
					$e->setKnockback($e->getKnockback() * mt_rand(2, 3));
				}
			},
			self::BLEED => function (EntityDamageByEntityEvent $e, $level) {
				$player = $e->getEntity();
				if (!$player instanceof Player) return;

				$player->getWorld()->addParticle($player->getPosition(), new DestroyBlockParticle(VanillaBlocks::REDSTONE()));

				switch ($level) {
					default:
					case 1:
						$chance = mt_rand(1, 100) <= 5;
						break;
					case 2:
						$chance = mt_rand(1, 100) <= 8;
						break;
					case 3:
						$chance = mt_rand(1, 100) <= 12;
						break;
				}

				if ($chance && $e->getDamager() instanceof Player) {
					/** @var PvPPlayer $player */
					$player->bleed($e->getDamager(), mt_rand(40, 80) * $level);
				}
			},

			self::STARVATION => function (EntityDamageByEntityEvent $e, $level) {
				$chance = mt_rand(1, 100) <= $level * 5;
				if ($chance) {
					$en = $e->getEntity();
					if ($en instanceof Player && $en->getHungerManager()->getFood() > 0) {
						$en->getHungerManager()->setFood($en->getHungerManager()->getFood() - 1);
					}
				}
			},
			self::ELECTRIFY => function (EntityDamageByEntityEvent $e, $level) {
				$chance = mt_rand(1, 100) <= ($level == 1 ? 4 : 8);
				if ($chance) {
					if (($entity = $e->getEntity()) instanceof Living) {
						/** @var Living $entity */
						$this->strikeLightning($e->getEntity()->getPosition());
						$entity->getEffects()->add(new EffectInstance(VanillaEffects::SLOWNESS(), ($level == 1 ? 1 : 2) * 20, $level == 1 ? 3 : 4));
					}
				}
			},
			self::PIERCE => function (EntityDamageByEntityEvent $e, $level) {
				$chance = mt_rand(1, 100) <= ($level == 1 ? 3 : ($level == 2 ? 5 : 9));
				if ($chance) {
					$e->getEntity()->getWorld()->addSound($e->getEntity()->getPosition(), new AnvilFallSound());
					$e->setBaseDamage($e->getBaseDamage() * ($level == 1 ? 1.1 : 1.2));
				}
			},
			self::DECAY => function (EntityDamageByEntityEvent $e, $level) {
				$chance = mt_rand(1, 100) <= $level * 4;
				if ($chance) {
					if (($entity = $e->getEntity()) instanceof Living) {
						/** @var Living $entity */
						$entity->getEffects()->add(new EffectInstance(VanillaEffects::WITHER(), 10 * 20, 1));
					}
				}
			},
			self::COMBO => function (EntityDamageByEntityEvent $e, $level) {
				$killer = $e->getDamager();
				if ($killer instanceof Player) {
					/** @var PvPPlayer $killer */
					if (($c = $killer->getCombo()) % 3 == 0) {
						$d = max(3, floor($c / 3) / 10);
						$e->setBaseDamage($e->getBaseDamage() * (1 + $d));
					}
				}
			},
			self::TIDES => function (EntityDamageByEntityEvent $e, $level) {
				$chance = mt_rand(1, 100) <= $level * 6;
				if ($chance) {
					$e->getEntity()->getWorld()->addSound($e->getEntity()->getPosition(), new PlaySound($e->getEntity()->getPosition(), "random.splash"));
					for ($i = 0; $i < mt_rand(15, 20); $i++) {
						$e->getEntity()->getWorld()->addParticle($e->getEntity()->getPosition()->add(mt_rand(-10, 10) / 10, 0, mt_rand(-10, 10) / 10), new SplashParticle());
					}
					$e->setKnockback($e->getKnockback() * (1 + ($level / 4)));
					$e->setBaseDamage($e->getBaseDamage() + 1);
				}
			},

			self::TRIPLE_THREAT => function (EntityShootBowEvent $e, $level) {
				$player = $e->getEntity();
				$arrow = $e->getProjectile();
				$force = $e->getForce();

				$yaw = $arrow->getLocation()->getYaw();

				$yadd = 20;
				for ($i = 1; $i <= 2; $i++) {
					if ($i == 1) {
						$ya = -$yadd;
					} else {
						$ya = $yadd;
					}
					$y = -sin(deg2rad($player->getLocation()->pitch));
					$xz = cos(deg2rad($player->getLocation()->pitch));

					$arrow = new PerishArrow(new Location(
						-$xz * sin(deg2rad($player->getLocation()->yaw + $ya)),
						$y,
						$xz * cos(deg2rad($player->getLocation()->yaw + $ya)),
						$player->getWorld(),
						($player->getLocation()->yaw > 180 ? 360 : 0) - $player->getLocation()->yaw + $ya,
						-$player->getLocation()->yaw
					), $player, $force == 2);
					$arrow->setMotion($arrow->getMotion()->multiply($force));
					$arrow->spawnToAll();
				}
			},
			self::RELOCATE => function (EntityShootBowEvent $e, $level) {
				$proj = $e->getProjectile();
				$e->setProjectile(new EnderArrow(new Location($proj->getPosition()->x, $proj->getPosition()->y, $proj->getPosition()->z, $proj->getWorld(), $proj->getLocation()->yaw, $proj->getLocation()->pitch), $e->getEntity(), false));
				$e->getProjectile()->setMotion($proj->getMotion());
				$e->setForce($e->getForce() * 0.5);
			},
			self::SNIPER => function (EntityShootBowEvent $e, $level) {
				/** @var Arrow $proj */
				$proj = $e->getProjectile();
				$e->setProjectile(new SniperArrow(new Location($proj->getPosition()->x, $proj->getPosition()->y, $proj->getPosition()->z, $proj->getWorld(), $proj->getLocation()->yaw, $proj->getLocation()->pitch), $e->getEntity(), $proj->isCritical()));
				$e->getProjectile()->setMotion($proj->getMotion());
			},
			self::INSTA_SHOT => function (EntityShootBowEvent $e, $level) {
				/** @var Arrow $proj */
				$proj = $e->getProjectile();
				$e->setProjectile(new InstaShotArrow(new Location($proj->getPosition()->x, $proj->getPosition()->y, $proj->getPosition()->z, $proj->getWorld(), $proj->getLocation()->yaw, $proj->getLocation()->pitch), $e->getEntity(), $proj->isCritical()));
				$e->getProjectile()->setMotion($proj->getMotion());
			},

			self::CROUCH => function (EntityDamageByEntityEvent $e, $level) {
				$player = $e->getEntity();

				if (!($player instanceof Player) || !$player->isSneaking()) return;

				switch ($level) {
					default:
					case 1:
						$chance = mt_rand(1, 100) <= 6;
						break;
					case 2:
						$chance = mt_rand(1, 100) <= 12;
						break;
					case 3:
						$chance = mt_rand(1, 100) <= 15;
						break;
					case 4:
						$chance = mt_rand(1, 100) <= 18;
						break;
				}

				if ($chance) {
					$e->setBaseDamage($e->getBaseDamage() / (($level / 2) + 1));
				}
			},
			self::SCORCH => function (EntityDamageByEntityEvent $e, $level) {
				$killer = $e->getDamager();
				switch ($level) {
					default:
					case 1:
						$chance = mt_rand(1, 100) <= 3;
						break;
					case 2:
						$chance = mt_rand(1, 100) <= 6;
						break;
					case 3:
						$chance = mt_rand(1, 100) <= 8;
						break;
					case 4:
						$chance = mt_rand(1, 100) <= 10;
						break;
					case 5:
						$chance = mt_rand(1, 100) <= 12;
						break;
				}

				if ($chance) {
					$killer->setOnFire(mt_rand(2, $level + 2));
				}
				$e->getEntity()->extinguish();
			},
			self::THORNS => function (EntityDamageByEntityEvent $e, $level) {
				switch ($level) {
					default:
					case 1:
						$chance = mt_rand(1, 20) == 1;
						break;
					case 2:
						$chance = mt_rand(1, 15) == 1;
						break;
					case 3:
						$chance = mt_rand(1, 10) == 1;
						break;
					case 4:
						$chance = mt_rand(1, 5) == 1;
						break;
				}

				$killer = $e->getDamager();
				if ($chance) {
					//$this->hitAs($e->getEntity(), $killer, ($level / 2) + 1);
				}
			},
			self::SHOCKWAVE => function (EntityDamageByEntityEvent $e, $level) {
				switch ($level) {
					default:
					case 1:
						$chance = mt_rand(1, 100) <= 8;
						break;
					case 2:
						$chance = mt_rand(1, 100) <= 15;
						break;
				}

				$killer = $e->getDamager();
				if ($chance) {
					$this->strikeLightning($killer->getPosition());
					foreach ($killer->getViewers() as $viewer) {
						if ($viewer->getPosition()->distance($killer->getPosition()) < 6) {
							$this->repel($viewer, $killer);
						}
					}
				}
			},
			self::ADRENALINE => function (EntityDamageByEntityEvent $e, $level) {
				if ($e->getEntity()->getHealth() - $e->getBaseDamage() <= 5) {
					if (($entity = $e->getEntity()) instanceof Living) {
						/** @var Living $entity */
						$entity->getEffects()->add(new EffectInstance(VanillaEffects::SPEED(), 10 * 20, 1));
					}
				}
			},

			self::SNARE => function (EntityDamageByEntityEvent $e, $level) {
				$killer = $e->getDamager();
				$chance = mt_rand(1, 100) <= 20;
				if ($chance && $e->getEntity() instanceof Player) {
					$this->drag($e->getEntity(), $killer);
					$killer->getWorld()->addSound($killer->getPosition(), new LaunchSound(), $killer->getViewers());
				}
			},
			self::RAGE => function (EntityDamageByEntityEvent $e, $level) {
				$chance = mt_rand(1, 100) <= ($level * 5);
				if ($chance) {
					if (($entity = $e->getEntity()) instanceof Living) {
						/** @var Living $entity */
						$entity->getEffects()->add(new EffectInstance(VanillaEffects::STRENGTH(), 20 * ($level * mt_rand(1, 2))));
						$entity->getEffects()->add(new EffectInstance(VanillaEffects::RESISTANCE(), 20 * ($level * mt_rand(1, 2))));
					}
				}
			},
			self::SORCERY => function (EntityDamageByEntityEvent $e, $level) {
				$killer = $e->getDamager();
				$chance = mt_rand(1, 100) <= ($level == 1 ? 4 : ($level == 2 ? 9 : 12));
				if ($chance) {
					if ($killer instanceof Living) {
						$bad = [
							VanillaEffects::SLOWNESS(),
							VanillaEffects::MINING_FATIGUE(),
							VanillaEffects::NAUSEA(),
							VanillaEffects::BLINDNESS(),
							VanillaEffects::HUNGER(),
							VanillaEffects::WEAKNESS(),
							VanillaEffects::POISON(),
							VanillaEffects::FATAL_POISON(),
							VanillaEffects::WITHER(),
						];
						$effect = new EffectInstance($bad[array_rand($bad)], 20 * ($level * 4));
						$killer->getEffects()->add($effect);
						$e->getEntity()->getWorld()->addSound($e->getEntity()->getPosition(), new PlaySound($e->getEntity()->getPosition(), "mob.evocation_illager.cast_spell"));
					}
				}
			},
			self::BLESSING => function (EntityDamageByEntityEvent $e, $level) {
				$killer = $e->getDamager();
				$chance = mt_rand(1, 100) <= ($level == 1 ? 3 : ($level == 2 ? 6 : 9));
				if ($chance) {
					if ($killer instanceof Living && ($entity = $e->getEntity()) instanceof Living) {
						/** @var Living $entity */
						$bad = [
							VanillaEffects::SLOWNESS(),
							VanillaEffects::MINING_FATIGUE(),
							VanillaEffects::NAUSEA(),
							VanillaEffects::BLINDNESS(),
							VanillaEffects::HUNGER(),
							VanillaEffects::WEAKNESS(),
							VanillaEffects::POISON(),
							VanillaEffects::FATAL_POISON(),
							VanillaEffects::WITHER(),
						];
						foreach ($killer->getEffects()->all() as $effect) {
							if (in_array($effect->getType(), $bad)) {
								$entity->getEffects()->remove($effect->getType());
								$killer->getEffects()->add($effect);
							}
						}
					}
				}
			},
			self::DODGE => function (EntityDamageByEntityEvent $e, $level) {
				$chance = mt_rand(1, 100) <= ($level == 1 ? 5 : 10);
				if ($chance) {
					($pl = $e->getEntity())->getWorld()->addSound($pl->getPosition(), new PlaySound($pl->getPosition(), "mob.wither.hurt"));
					$e->cancel();
				}
			},
			self::GODLY_RETRIBUTION => function (EntityDamageByEntityEvent $e, $level) {
				/** @var Living $pl */
				if (($pl = $e->getEntity()) instanceof Living && $pl->getHealth() - $e->getBaseDamage() <= 5 && !$pl->getEffects()->has(VanillaEffects::STRENGTH())) {
					$pl->getWorld()->addSound($pl->getPosition(), new PlaySound($pl->getPosition(), "mob.wither.ambient"));
					$pl->getEffects()->add(new EffectInstance(VanillaEffects::STRENGTH(), 10 * 20));
					$pl->getEffects()->add(new EffectInstance(VanillaEffects::REGENERATION(), 10 * 20, 1));
				}
			},

		];

		/* Armor specific */
		$this->equip = [
			self::OVERLORD => function (Player $player, $beforelevel, $afterlevel) {
				$player->setMaxHealth(20 + ($afterlevel * 2));

				if ($player->getHealth() >= 20 + ($beforelevel * 2)) {
					$player->setHealth($player->getMaxHealth());
				}
			},

			self::GLOWING => function (Player $player, $beforelevel, $afterlevel) {
				$player->getEffects()->add(new EffectInstance(VanillaEffects::NIGHT_VISION(), 20 * 99999, 0, false));
			},

			self::GEARS => function (Player $player, $beforelevel, $afterlevel) {
				$player->getEffects()->add(new EffectInstance(VanillaEffects::SPEED(), 20 * 99999, $afterlevel - 1, false));
			},
			self::BUNNY => function (Player $player, $beforelevel, $afterlevel) {
				$player->getEffects()->add(new EffectInstance(VanillaEffects::JUMP_BOOST(), 20 * 99999, $afterlevel - 1, false));
			},
		];

		$this->unequip = [
			self::OVERLORD => function (Player $player, $beforelevel, $afterlevel) {
				$player->setMaxHealth(20 + ($afterlevel * 2));
			},

			self::GLOWING => function (Player $player, $beforelevel, $afterlevel) {
				$player->getEffects()->remove(VanillaEffects::NIGHT_VISION());
			},

			self::GEARS => function (Player $player, $beforelevel, $afterlevel) {
				$player->getEffects()->remove(VanillaEffects::SPEED());
			},
			self::BUNNY => function (Player $player, $beforelevel, $afterlevel) {
				$player->getEffects()->remove(VanillaEffects::JUMP_BOOST());
			},
		];

		$this->task = [
			self::GLOWING => function (Player $player, $currentTick, $level) {
				if (!$player->getEffects()->has(VanillaEffects::NIGHT_VISION())) $player->getEffects()->add(new EffectInstance(VanillaEffects::NIGHT_VISION(), 20 * 99999, 0, false));
			},

			self::SCORCH => function (Player $player, $currentTick, $level) {
				if ($player->isOnFire()) $player->extinguish();
			},
			self::GEARS => function (Player $player, $currentTick, $level) {
				if (!$player->getEffects()->has(VanillaEffects::SPEED())) $player->getEffects()->add(new EffectInstance(VanillaEffects::SPEED(), 20 * 99999, $level - 1, false));
			},
			self::BUNNY => function (Player $player, $currentTick, $level) {
				if (!$player->getEffects()->has(VanillaEffects::JUMP_BOOST())) $player->getEffects()->add(new EffectInstance(VanillaEffects::JUMP_BOOST(), 20 * 99999, $level - 1, false));
			},
		];
	}

	public function strikeLightning(Position $pos): void {
		$pos->getWorld()->addSound($pos, new PlaySound($pos, "ambient.weather.lightning.impact"));
		$pk = new AddActorPacket();
		$pk->type = "minecraft:lightning_bolt";
		$pk->entityRuntimeId = Entity::nextRuntimeId();
		$pk->position = $pos->asVector3();
		$pk->yaw = $pk->pitch = 0;
		foreach ($pos->getWorld()->getPlayers() as $p) {
			$p->getNetworkSession()->sendDataPacket($pk);
		}
	}

	public function explosion(Position $pos, int $size = 2) {
		$pos->getWorld()->addParticle($pos, new ExplodeParticle());
		$pos->getWorld()->addSound($pos, new ExplodeSound, $pos->getWorld()->getViewersForPosition($pos));
	}

	public function drag(Player $to, Living $from): void {
		$t = $from->getPosition()->asVector3();
		$dv = $to->getPosition()->asVector3()->subtract($t->x, $t->y, $t->z)->normalize();
		$from->knockback($dv->x, $dv->z, 0.45);
	}

	public function repel(Player $to, Entity $from, float $force = 0.8): void {
		$t = $to->getPosition()->asVector3();
		$dv = $from->getPosition()->asVector3()->subtract($t->x, $t->y, $t->z)->normalize();
		$to->knockback($dv->x, $dv->z, $force);
	}

	public function getRandomKeyType(Player $player, array $takingalready = [], int $tries = 0) {
		return false;
		/*if ($tries >= 10) return false;
		$type = ["iron", "gold", "diamond", "emerald", "vote"][mt_rand(0, 4)];
		$amt = PvP::getInstance()->getMysteryBoxes()->getSessionManager()->getSession($player)->getKeys($type);
		if (($amt - $takingalready[$type]) <= 0 && $tries < 10) {
			$tries++;
			$type = $this->getRandomKeyType($player, $takingalready, $tries);
		}
		return $type;*/
	}

	public function hitAs(Entity $killer, Entity $hit, float $damage) {
		$hit->attack(new EntityDamageByEntityEvent($killer, $hit, 1, $damage, [], 0.4));
	}
}
