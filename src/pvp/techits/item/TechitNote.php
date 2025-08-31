<?php

namespace pvp\techits\item;

use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\ItemUseResult;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\Enchantment as PMEnchantment;
use pocketmine\player\Player;
use pocketmine\math\Vector3;
use pocketmine\nbt\{
	NBT,
	tag\ListTag
};

use pvp\{
	PvP,
	PvPPlayer
};

use core\utils\TextFormat;

class TechitNote extends Item {

	public function __construct(ItemIdentifier $identifier, string $name = "Techit Note", array $enchantmentTags = []) {
		parent::__construct($identifier, $name, $enchantmentTags);
		$n = $this->getNamedTag();
		$n->setByte('isTechitNote', 1);
		$this->setNamedTag($n);
	}

	public function isInitiated(): bool {
		return (bool) $this->getNamedTag()->getByte("init", 0);
	}

	public function init(): void {
		$nbt = $this->getNamedTag();
		$nbt->setByte("init", 1);
		$this->setNamedTag($nbt);

		$this->setCustomName(TextFormat::RESET . TextFormat::AQUA . "Techit Note");
		$lores = [];
		$lores[] = TextFormat::GRAY . "This Techit Note is worth";
		$lores[] = TextFormat::AQUA . number_format($this->getTechits()) . " Techits! " . TextFormat::GRAY . "Tap the ground";
		$lores[] = TextFormat::GRAY . "to claim your Techits!";
		foreach ($lores as $key => $lore) $lores[$key] = TextFormat::RESET . $lore;

		$this->setLore($lores);

		$this->addEnchantment(new EnchantmentInstance(new PMEnchantment('internal', -1, 0x0, 0x0, 1)));
	}

	public function setup(int $techits = 1): void {
		$nbt = $this->getNamedTag();
		$nbt->setInt("techits", $techits);
		$this->setNamedTag($nbt);

		$this->init();
	}

	public function getTechits(): int {
		return $this->getNamedTag()->getInt("techits", 0);
	}

	public function onClickAir(Player $player, Vector3 $directionVector, array &$returnedItems): ItemUseResult {
		if ($this->getNamedTag()->getByte('isTechitNote', 0) != 1) return ItemUseResult::FAIL();
		/** @var PvPPlayer $player */
		$player->addTechits($this->getTechits());
		$player->sendMessage(TextFormat::GN . "Claimed " . TextFormat::AQUA . number_format($this->getTechits()) . " Techits!");
		$this->count--;
		return ItemUseResult::SUCCESS();
	}
}
