<?php namespace pvp\enchantments\book;

use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemTypeIds;

use pvp\PvP;
use pvp\enchantments\type\Enchantment;
use pvp\enchantments\EnchantmentData;

use core\utils\TextFormat;

class RedeemedBook extends Item{

	public function __construct(){
		parent::__construct(new ItemIdentifier(ItemTypeIds::ENCHANTED_BOOK), "Redeemed Book");
	}

	public function setup(Enchantment $enchantment, int $cost = -1) : void{
		$cost = ($cost == -1 ? $enchantment->getRarity() * 5 + (mt_rand(1, 3) * mt_rand(1, 2)) : $cost);
		$nbt = $this->getNamedTag();
		$nbt->setIntArray("enchant", [$enchantment->getId(), $enchantment->getLevel()]);
		$nbt->setInt("applycost", $cost);
		$nbt->setString("UUID", \Ramsey\Uuid\Uuid::uuid4()->toString());
		$this->setNamedTag($nbt);

		$this->setCustomName(TextFormat::RESET . $enchantment->getLore());

		$lores = [];
		$lores[] = TextFormat::AQUA . EnchantmentData::SLOTS[$enchantment->getType()]["name"] . " enchantment";
		$lores[] = " ";
		$lores[] = TextFormat::YELLOW . "Apply cost: " . $cost . " XP Levels";
		$lores[] = " ";
		$lores[] = TextFormat::GRAY . "Bring this book to the " . TextFormat::DARK_PURPLE . TextFormat::BOLD . "Enchanter";
		$lores[] = TextFormat::GRAY . "at the " . TextFormat::WHITE . "Hangout" . TextFormat::GRAY . " to enchant an item!";
		foreach($lores as $key => $lore) $lores[$key] = TextFormat::RESET . $lore;

		$this->setLore($lores);
	}

	public function getEnchant() : ?Enchantment{
		$tag = $this->getNamedTag()->getIntArray("enchant");
		return PvP::getInstance()->getEnchantments()->getEnchantment($tag[0], $tag[1]);
	}

	public function getApplyCost() : int{
		return $this->getNamedTag()->getInt("applycost", 0);
	}

}