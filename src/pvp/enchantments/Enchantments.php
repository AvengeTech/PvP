<?php

namespace pvp\enchantments;

use pocketmine\event\{

	entity\EntityDamageByEntityEvent,
	entity\EntityShootBowEvent,
};
use pocketmine\item\enchantment\{
	EnchantmentInstance,
};
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\player\Player;
use pocketmine\item\{
	Item,
	Tool
};

use pvp\PvP;
use pvp\enchantments\type\{
	Enchantment,
	ArmorEnchantment,
	UniversalEnchantment
};
use pvp\enchantments\commands\{
	AddEnchant,
	Guide,
	AnimatorGuide,
	EditItem,
};
use pvp\enchantments\effects\Effects;

class Enchantments extends EnchantmentData {

	public $plugin;
	public $calls;

	public $effects;

	public $enchantments = [];
	public $nukecd = [];
	public $hbcd = [];

	public $acache = [];

	public $r_cooldown = [];

	public function __construct(PvP $plugin) {
		$this->plugin = $plugin;

		$this->setupEnchantments();

		$plugin->getServer()->getCommandMap()->registerAll("enchantments", [
			new AddEnchant($plugin, "addenchant", "Add an enchant to an item"),
			new Guide($plugin, "guide", "Open the Enchantment guide"),
			new AnimatorGuide($plugin, "animatorguide", "Open the Animator guide"),

			//new AnimatorTest($plugin, "animatortest", "Animator test (sn3ak only)"),
			new EditItem($plugin, "edititem", "Item editor (sn3ak only)"),
		]);

		/**for($i = 1; $i <= 5; $i++){
			ItemFactory::getInstance()->register(new RedeemableBook($i), true);
		}
		ItemFactory::getInstance()->register(new RedeemedBook(), true);*/

		$this->effects = new Effects($plugin, $this);
	}

	public function getEffects(): Effects {
		return $this->effects;
	}

	public function getCalls(): Calls {
		return $this->calls;
	}

	public function setupEnchantments(): void {
		foreach (self::ENCHANTMENTS as $id => $data) {
			if (
				$data["type"] == self::SLOT_ARMOR ||
				$data["type"] == self::SLOT_HEAD ||
				$data["type"] == self::SLOT_TORSO ||
				$data["type"] == self::SLOT_LEGS ||
				$data["type"] == self::SLOT_FEET
			) {
				$this->enchantments[$id] = new ArmorEnchantment($id);
			} elseif ($data["type"] == self::SLOT_ALL) {
				$this->enchantments[$id] = new UniversalEnchantment($id);
			} else {
				$this->enchantments[$id] = new Enchantment($id);
			}
		}
		$this->calls = new Calls();
	}

	public function tick(int $currentTick): void {
		foreach ($this->acache as $name => $data) {
			$player = $this->plugin->getServer()->getPlayerExact($name);
			if ($player instanceof Player) {
				foreach ($data as $id => $level) {
					if (isset($this->calls->task[$id])) {
						$this->calls->task[$id]($player, $currentTick, $level);
					}
				}
			} else {
				unset($this->acache[$name]);
			}
		}
	}

	public function process($event): void {
		if ($event->isCancelled()) return;

		if ($event instanceof EntityDamageByEntityEvent) {
			$hurt = $event->getEntity();
			$killer = $event->getDamager();

			if ($killer instanceof Player) {
				$khand = $killer->getInventory()->getItemInHand();
				if ($khand instanceof Tool) {
					if ($khand->hasEnchantments()) {
						foreach ($khand->getEnchantments() as $enchantment) {
							$ench = $this->getEWE($enchantment);
							if (
								$ench !== null &&
								$ench->handled() &&
								$ench->getType() == self::SLOT_SWORD
							) {
								if (isset($this->calls->event[$ench->getId()])) {
									$this->calls->event[$ench->getId()]($event, $enchantment->getLevel());
								}
							}
						}
					}
				}
			}

			if ($hurt instanceof Player) {
				$cache = $this->acache[$hurt->getName()] ?? [];
				foreach ($cache as $id => $level) {
					if (isset($this->calls->event[$id])) {
						$this->calls->event[$id]($event, $level);
					}
				}
			}

			if ($event->getBaseDamage() >= 15) $event->setBaseDamage(15);
		}

		if ($event instanceof EntityShootBowEvent) {
			$bow = $event->getBow();
			if ($bow->hasEnchantments()) {
				foreach ($bow->getEnchantments() as $enchantment) {
					$ench = $this->getEWE($enchantment);
					$slot = $ench->getSlot();
					if (
						$ench->handled() &&
						$ench->getType() == self::SLOT_BOW
					) {
						$this->calls->event[$ench->getId()]($event, $enchantment->getLevel());
					}
				}
			}
		}
	}

	public function getItemData(Item $item): ItemData {
		return new ItemData($item);
	}

	public function getEWE(EnchantmentInstance $enchantment): ?Enchantment {
		return $this->getEnchantment(EnchantmentIdMap::getInstance()->toId($enchantment->getType()));
	}

	public function getEnchantment(int $id, int $level = 1): ?Enchantment {
		$enchantment = $this->enchantments[$id] ?? null;
		if ($enchantment !== null) {
			$enchantment = clone $enchantment;
			$enchantment->setLevel($level);
		}
		return $enchantment;
	}

	public function getEnchantments(int $rarity = self::RARITY_COMMON, bool $unobtainable = false): array {
		$enchantments = [];
		foreach ($this->enchantments as $enchantment) {
			if ($enchantment->getRarity() == $rarity) {
				if ($enchantment->isObtainable()) {
					$enchantments[$enchantment->getId()] = clone $enchantment;
				} else {
					if ($unobtainable) $enchantments[$enchantment->getId()] = clone $enchantment;
				}
			}
		}
		return $enchantments;
	}

	public function getRandomEnchantment(int $rarity = self::RARITY_COMMON): ?Enchantment {
		$enchantments = $this->getEnchantments($rarity);
		return $enchantments[array_rand($enchantments)];
	}

	public function getEnchantmentByName(string $name, bool $unobtainable = false): ?Enchantment {
		foreach ($this->enchantments as $ench) {
			if (
				strtolower($ench->getName()) == strtolower($name) &&
				($ench->isObtainable() || $unobtainable)
			) return $ench;
		}
		return null;
	}

	public function getRoman(int $number): string {
		if ($number < 0) return $number;

		$result = "";
		$roman_numerals = [
			"M" => 1000,
			"CM" => 900,
			"D" => 500,
			"CD" => 400,
			"C" => 100,
			"XC" => 90,
			"L" => 50,
			"XL" => 40,
			"X" => 10,
			"IX" => 9,
			"V" => 5,
			"IV" => 4,
			"I" => 1
		];
		foreach ($roman_numerals as $roman => $num) {
			$matches = intval($number / $num);
			$result .= str_repeat($roman, $matches);
			$number = $number % $num;
		}
		return $result;
	}

	public function calculateCache($e): void {
		if ($e instanceof Player) {
			$player = $e;
		} else {
			$player = $e->getEntity();
		}

		if (!$e instanceof Player) return;

		$this->plugin->getScheduler()->scheduleDelayedTask(new class($this, $player) extends \pocketmine\scheduler\Task {

			public $enchants;
			public $player;

			public function __construct(Enchantments $enchants, Player $player) {
				$this->enchants = $enchants;
				$this->player = $player;
			}

			public function onRun(): void {
				$player = $this->player;
				$enchants = $this->enchants;
				if (!$player instanceof Player || $player->isClosed()) return;

				$before = $enchants->acache[$player->getName()] ?? [];

				$new = [];
				foreach ($player->getArmorInventory()->getContents() as $i => $armor) {
					if ($armor->hasEnchantments()) {
						foreach ($armor->getEnchantments() as $en) {
							$eo = $enchants->getEWE($en);
							if ($eo !== null) {
								if ($eo instanceof ArmorEnchantment && $eo->isStackable()) {
									if (isset($new[$eo->getId()])) {
										if ($new[$eo->getId()] + $en->getLevel() > $eo->getMaxStackLevel()) {
											$new[$eo->getId()] += $eo->getMaxStackLevel();
										} else {
											$new[$eo->getId()] += $en->getLevel();
										}
									} else {
										$new[$eo->getId()] = $en->getLevel();
									}
									$new[$eo->getId()] = min($new[$eo->getId()], $eo->getMaxStackLevel());
								} else {
									if (isset($new[$eo->getId()])) {
										if ($new[$eo->getId()] < $en->getLevel()) {
											$new[$eo->getId()] = $en->getLevel();
										}
									} else {
										$new[$eo->getId()] = $en->getLevel();
									}
								}
							}
						}
					}
				}
				$enchants->acache[$player->getName()] = $new;

				foreach ($new as $id => $level) {
					if (!isset($before[$id])) $before[$id] = 0;
				}
				foreach ($before as $id => $level) {
					$lvl = $new[$id] ?? 0;
					if ($level < $lvl) {
						if (isset($enchants->calls->equip[$id])) {
							$enchants->calls->equip[$id]($player, $level, $lvl);
						}
					} elseif ($level > $lvl) {
						if (isset($enchants->calls->unequip[$id])) {
							$enchants->calls->unequip[$id]($player, $level, $lvl);
						}
					}
				}
			}
		}, 1);
	}

	public function hasCooldown(Player $player): bool {
		$cooldown = $this->r_cooldown[$player->getXuid()] ?? 0;
		return time() <= $cooldown;
	}

	public function getCooldown(Player $player): int {
		return $this->r_cooldown[$player->getXuid()] ?? 0;
	}

	public function getCooldownFormatted(Player $player): string {
		$seconds = $this->getCooldown($player) - time();
		$dtF = new \DateTime("@0");
		$dtT = new \DateTime("@$seconds");
		return $dtF->diff($dtT)->format("%a days, %h hours, %i minutes");
	}

	public function setCooldown(Player $player, int $cooldown): void {
		$this->r_cooldown[$player->getXuid()] = time() + $cooldown;
	}
}
