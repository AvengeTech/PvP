<?php namespace pvp\enchantments\type;

use pocketmine\utils\TextFormat;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\item\Item;
use pocketmine\item\enchantment\{
	Enchantment as PMEnch,
};

use pvp\PvP;
use pvp\enchantments\EnchantmentData;

class Enchantment{

	public $id;
	public $level = 1;

	public function __construct(int $id){
		$this->id = $id;
		if($this->handled())
			EnchantmentIdMap::getInstance()->register($id, new PMEnch($this->getName(), $this->getRarity(), $this->getType(), 0x0, 1000));
	}

	public function apply(Item $item, int $level) : void{

	}

	public function getEnchantment() : PMEnch{
		return EnchantmentIdMap::getInstance()->fromId($this->getId());
	}

	public function getId() : int{
		return $this->id;
	}

	public function getRuntimeId() : int{
		return spl_object_id($this->getEnchantment());
	}

	public function getMaxLevel() : int{
		return EnchantmentData::ENCHANTMENTS[$this->getId()]["maxLevel"] ?? 1;
	}

	public function getRarity() : int{
		return EnchantmentData::ENCHANTMENTS[$this->getId()]["rarity"];
	}

	public function getType() : int{
		return EnchantmentData::ENCHANTMENTS[$this->getId()]["type"];
	}

	public function hasType($flag) : bool{
		return ($this->getEType() & $flag) !== 0;
	}

	public function getSlot() : int{
		return $this->getType();
	}

	public function getEType() : int{
		return EnchantmentData::typeToEtype($this->getType());
	}

	public function getName() : string{
		return EnchantmentData::ENCHANTMENTS[$this->getId()]["name"];
	}

	public function handled() : bool{
		return $this->getId() > 70;
	}

	public function getLore() : string{
		return TextFormat::RESET . EnchantmentData::rarityColor($this->getRarity()) . $this->getName() . " " . PvP::getInstance()->getEnchantments()->getRoman($this->getLevel());
	}

	public function getRarityColor() : string{
		switch($this->getRarity()){
			case 1:
				return TextFormat::GREEN;
			case 2:
				return TextFormat::DARK_GREEN;
			case 3:
				return TextFormat::YELLOW;
			case 4:
				return TextFormat::GOLD;
			case 5:
				return TextFormat::RED;
		}
		return "";
	}

	public function getRarityName() : string{
		switch($this->getRarity()){
			case 1:
				return "Common";
			case 2:
				return "Uncommon";
			case 3:
				return "Rare";
			case 4:
				return "Legendary";
			case 5:
				return "Divine";
		}
		return "";
	}

	public function getDescription() : string{
		return EnchantmentData::ENCHANTMENTS[$this->getId()]["description"];
	}

	public function isObtainable() : bool{
		return EnchantmentData::ENCHANTMENTS[$this->getId()]["obtainable"] ?? true;
	}

	public function getLevel() : int{
		return $this->level;
	}

	public function setLevel(int $level, bool $forceOver = false) : void{
		$this->level = $level;
		if($this->getLevel() > $this->getMaxLevel() && !$forceOver){
			$this->level = $this->getMaxLevel();
		}
	}

}