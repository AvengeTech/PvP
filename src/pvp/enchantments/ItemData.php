<?php namespace pvp\enchantments;

use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\player\Player;
use pocketmine\item\{
	Item,
	Bow,
	Axe, Pickaxe, Shovel,
	Sword,
	Shears,
	Tool,
	Durable
};
use pocketmine\item\enchantment\{
	Enchantment,
	EnchantmentInstance
};
use pocketmine\nbt\tag\CompoundTag;

use pvp\PvP;
use pvp\enchantments\effects\EffectClass;

use core\utils\TextFormat;

class ItemData{

	public $item;

	public function __construct(Item $item){
		$this->item = $item;
	}

	public function getItem() : Item{
		return $this->item;
	}

	//easy access
	public function getNamedTag() : ?CompoundTag{
		return $this->getItem()->getNamedTag();
	}

	public function setNamedTag(CompoundTag $nbt) : void{
		$this->getItem()->setNamedTag($nbt);
	}

	public function getName() : string{
		$item = $this->getItem();
		return $item->getCustomName() == null ? $item->getName() : $item->getCustomName();
	}

	public function setCustomName(string $name) : void{
		$this->getItem()->setCustomName(TextFormat::RESET . $name);
	}

	public function canEdit() : bool{
		return $this->getNamedTag()->getByte("editable", 1);
	}

	public function setEditable(bool $bool) : void{
		$nbt = $this->getNamedTag();
		$nbt->setByte("editable", (int) $bool);
		$this->setNamedTag($nbt);
	}

	//lore stuff
	public function isSigned() : bool{
		return $this->getSignature() !== "";
	}

	public function getSignature() : string{
		return $this->getNamedTag()->getString("signature", "");
	}

	public function sign($name) : void{
		if($name instanceof Player) $name = $name->getName(); //lul

		$nbt = $this->getNamedTag();
		$nbt->setString("signature", $name);
		$this->setNamedTag($nbt);

		$this->getItem()->setLore($this->calculateLores());
	}

	public function unsign() : void{
		$nbt = $this->getNamedTag();
		$nbt->removeTag("signature");
		$this->setNamedTag($nbt);

		$this->getItem()->setLore($this->calculateLores());
	}

	public function hasDeathMessage() : bool{
		return $this->getDeathMessage() !== "";
	}

	public function getDeathMessage() : string{
		return $this->getNamedTag()->getString("deathmessage", "");
	}

	public function setDeathMessage(string $message) : void{
		$nbt = $this->getNamedTag();
		$nbt->setString("deathmessage", $message);
		$this->setNamedTag($nbt);

		$this->getItem()->setLore($this->calculateLores());
	}

	public function getBlocksMined() : int{
		return $this->getNamedTag()->getInt("mined", 0);
	}

	public function addBlocksMined(int $amount = 1) : void{
		$this->setBlocksMined($this->getBlocksMined() + $amount);
	}

	public function setBlocksMined(int $amount = 1) : void{
		$nbt = $this->getNamedTag();
		$nbt->setInt("mined", $amount);
		$this->setNamedTag($nbt);

		$this->getItem()->setLore($this->calculateLores());

	}

	public function getKills() : int{
		return $this->getNamedTag()->getInt("kills", 0);
	}

	public function addKill() : void{
		$this->setKills($this->getKills() + 1);
	}

	public function setKills($amount = 1) : void{
		$nbt = $this->getNamedTag();
		$nbt->setInt("kills", $amount);
		$this->setNamedTag($nbt);

		$this->getItem()->setLore($this->calculateLores());
	}

	public function addEnchantment(Enchantment $ench, int $level) : void{
		$item = $this->getItem();

		$item->addEnchantment(new EnchantmentInstance($ench, $level));
		$item->setLore($this->calculateLores(true));
	}

	public function removeEnchantment(int $id, int $level) : void{
		$item = $this->getItem();

		$item->removeEnchantment(EnchantmentIdMap::getInstance()->fromId($id), $level);
		$item->setLore($this->calculateLores(true));
	}

	public function hasEffect() : bool{
		return $this->getEffectId() !== 0;
	}

	public function getEffectId() : int{
		return $this->getNamedTag()->getInt("effectid", 0);
	}

	public function setEffectId(int $id) : void{
		$nbt = $this->getNamedTag();
		$nbt->setInt("effectid", $id);
		$this->setNamedTag($nbt);

		$this->getItem()->setLore($this->calculateLores());
	}

	public function getEffect() : ?EffectClass{
		return PvP::getInstance()->getEnchantments()->getEffects()->getEffectById($this->getEffectId());
	}

	public function calculateLores(bool $force = false) : array{
		$item = $this->getItem();

		if(!$item instanceof Durable && !$force){
			return [];
		}

		$nl = [];

		if($this->hasDeathMessage()){
			$nl[] = TextFormat::RED . "Death message:";
			$nl[] = "  " . $this->getDeathMessage();
		}

		$enchantments = $item->getEnchantments();
		$elores = [
			EnchantmentData::RARITY_DIVINE => [],
			EnchantmentData::RARITY_LEGENDARY => [],
			EnchantmentData::RARITY_RARE => [],
			EnchantmentData::RARITY_UNCOMMON => [],
			EnchantmentData::RARITY_COMMON => [],
		];
		foreach($enchantments as $ench){
			$id = EnchantmentIdMap::getInstance()->toId($ench->getType());
			if($id >= 100){
				$e = PvP::getInstance()->getEnchantments()->getEnchantment($id);
				if($e !== null){
					$e->setLevel($ench->getLevel(), true);
					$elores[$ench->getType()->getRarity()][] = $e->getLore();
				}
			}
		}
		$enchl = [];
		foreach($elores as $rarity => $lore){
			foreach($lore as $l){
				$enchl[] = $l;
			}
		}
		if(count($enchl) > 0 && $this->hasDeathMessage()){
			$nl[] = " ";
		}
		foreach($enchl as $l) $nl[] = $l;

		if($this->hasEffect()){
			$effect = $this->getEffect();
			$nl[] = " ";
			if($item instanceof Sword){
				$nl[] = TextFormat::RED . "Death animation:";
			}elseif($item instanceof Pickaxe){
				$nl[] = TextFormat::RED . "Mining animation:";
			}elseif($item instanceof Bow){
				$nl[] = TextFormat::RED . "Bow animation:";
			}else{
				$nl[] = TextFormat::RED . "Animation:";
			}
			$nl[] = " " . $effect->getRarityColor() . $effect->getName();
		}

		if($item instanceof Tool){
			$nl[] = " ";
			if($item instanceof Axe || $item instanceof Pickaxe || $item instanceof Shears || $item instanceof Shovel){
				$nl[] = TextFormat::GRAY . "Blocks broken: " . number_format($this->getBlocksMined());
			}
			if($this->getKills() > 0) $nl[] = TextFormat::GRAY . "Player kills: " . number_format($this->getKills());
		}

		if($this->isSigned()){
			$nl[] = " ";
			$nl[] = TextFormat::GRAY . "Signed by: " . TextFormat::YELLOW . $this->getSignature();
		}

		foreach($nl as $key => $l) $nl[$key] = TextFormat::RESET . $l;

		return $nl;
	}

}