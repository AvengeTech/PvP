<?php namespace pvp\enchantments\book;

use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemTypeIds;
use pocketmine\player\Player;
use pocketmine\item\Item;

use pvp\PvP;
use pvp\enchantments\{
	EnchantmentData,
	type\Enchantment
};

use core\utils\TextFormat;
use pocketmine\item\enchantment\Enchantment as PMEnchantment;
use pocketmine\item\enchantment\EnchantmentInstance;

class RedeemableBook extends Item{

	private int $meta;

	public function __construct(int $meta = 1){
		parent::__construct(new ItemIdentifier(ItemTypeIds::BOOK), $this->getRarityName($meta));
		$this->meta = $meta;
	}

	public function redeem(Player $player) : void{
		$s = $this->succeeded();

		$enchant = ($s ? $this->getSuccessEnchant() : $this->getFailEnchant());
		if($enchant == null){
			$s = !$s;
			switch($s){
				case true:
					$enchant = $this->getSuccessEnchant();
					break;
				case false:
					$enchant = $this->getFailEnchant();
					break;
			}
		}
		$book = new RedeemedBook();
		$book->setup($enchant, ($this->getRarity() * mt_rand(9, 10)) + mt_rand(0, 5));
		$player->getInventory()->setItemInHand($book);

		$msg = TextFormat::YN . "Successfully redeemed your enchantment book! Received the ";
		if($s){
			$msg .= TextFormat::GREEN . "Success enchantment";
		}else{
			$msg .= TextFormat::RED . "Fallover enchantment";
		}
		$player->sendMessage($msg);
	}

	public function isInitiated() : bool{
		return (bool) $this->getNamedTag()->getByte("init", 0);
	}

	public function init() : void{
		$nbt = $this->getNamedTag();
		$nbt->setByte("init", 1);
		$this->setNamedTag($nbt);

		if($nbt->getInt("successrate", -1) == -1 && $nbt->getInt("failrate", -1) == -1){
			return;
		}

		$lores = [];
		$lores[] = TextFormat::GREEN . $this->getSuccessRate() . "% " . TextFormat::WHITE . "success rate";
		$lores[] = TextFormat::RED . $this->getFailRate() . "% " . TextFormat::WHITE . "fallover rate";
		$lores[] = " ";
		$lores[] = TextFormat::GREEN . "Success Enchantment:";
		$lores[] = "  " . $this->getSuccessEnchant()->getLore();
		$lores[] = " ";
		$lores[] = TextFormat::RED . "Fallover Enchantment:";
		$lores[] = "  " . $this->getFailEnchant()->getLore();
		$lores[] = " ";
		$lores[] = TextFormat::YELLOW . "Redeem cost: " . $this->getRedeemCost() . " XP Levels";
		$lores[] = " ";
		$lores[] = TextFormat::GRAY . "Right-Click to redeem this book! You";
		$lores[] = TextFormat::GRAY . "have a chance to either receive";
		$lores[] = TextFormat::GRAY . "the " . TextFormat::GREEN . "Success Enchantment" . TextFormat::GRAY . " (better),";
		$lores[] = TextFormat::GRAY . "or the " . TextFormat::RED . "Fallover Enchantment";
		$lores[] = TextFormat::GRAY . "(worse)";
		foreach($lores as $key => $lore) $lores[$key] = TextFormat::RESET . $lore;

		$this->setLore($lores);

		$this->addEnchantment(new EnchantmentInstance(new PMEnchantment('internal', -1, 0x0, 0x0, 1)));
	}

	public function getRarity() : int{
		return $this->meta;
	}

	public function getRarityName(int $rarity = -1) : string{
		if($rarity == -1) $rarity = $this->getRarity();
		switch($rarity){
			case EnchantmentData::RARITY_COMMON:
				return TextFormat::GREEN . "Common Book";
			case EnchantmentData::RARITY_UNCOMMON:
				return TextFormat::DARK_GREEN . "Uncommon Book";
			case EnchantmentData::RARITY_RARE:
				return TextFormat::YELLOW . "Rare Book";
			case EnchantmentData::RARITY_LEGENDARY:
				return TextFormat::GOLD . "Legendary Book";
			case EnchantmentData::RARITY_DIVINE:
				return TextFormat::RED . "Divine Book";
		}
		return " ";
	}

	public function generate(int $successrate = -1, int $cost = -1) : void{
		$this->setCustomName(TextFormat::RESET . $this->getRarityName());

		$r = $this->getRarity();

		if($r !== EnchantmentData::RARITY_DIVINE){
			$enchants = array_merge(
				$r != EnchantmentData::RARITY_COMMON ? PvP::getInstance()->getEnchantments()->getEnchantments($r - 1) : [],
				PvP::getInstance()->getEnchantments()->getEnchantments($r)
			);
		}else{
			$enchants = PvP::getInstance()->getEnchantments()->getEnchantments(EnchantmentData::RARITY_DIVINE, true);
		}

		shuffle($enchants);

		foreach($enchants as $key => $enchant){
			if($enchant->getRarity() == $this->getRarity()){
				$enchant->setLevel(mt_rand(1, $enchant->getMaxLevel()));
				$enchants[$key] = $enchant;
			}elseif($enchant->getRarity() < $this->getRarity()){
				if(mt_rand(0, 1) == 1){
					$enchant->setLevel(mt_rand(1, $enchant->getMaxLevel()));
					$enchants[$key] = $enchant;
				}
			}
		}

		foreach($enchants as $key => $enchant){
			if($enchant->getRarity() == $this->getRarity()){
				unset($enchants[$key]);
				$this->setSuccessEnchant($enchant->getId(), $enchant->getLevel());
				break;
			}
		}

		shuffle($enchants);

		foreach($enchants as $key => $enchant){
			if($enchant->getRarity() <= $enchant->getRarity()){
				unset($enchants[$key]);
				$this->setFailEnchant($enchant->getId(), $enchant->getLevel());
				break;
			}
		}

		$this->setSuccessRate($successrate == -1 ? $this->genRate() : $successrate);

		$this->setRedeemCost($cost == -1 ? $this->getRarity() * 5 + (mt_rand(1, 3) * mt_rand(1, 2)) : $cost);

		$this->init();
	}

	public function genRate() : int{
		return mt_rand(40, 90);
	}

	public function getMaxStackSize() : int{
		return 1;
	}

	public function getSuccessRate() : int{
		return $this->getNamedTag()->getInt("successrate");
	}

	public function setSuccessRate(int $rate) : void{
		$nbt = $this->getNamedTag();
		$nbt->setInt("successrate", $rate);
		$this->setNamedTag($nbt);

		$this->setFailRate(100 - $rate);
	}

	public function getFailRate() : int{
		return $this->getNamedTag()->getInt("failrate");
	}

	private function setFailRate(int $rate) : void{
		$nbt = $this->getNamedTag();
		$nbt->setInt("failrate", $rate);
		$this->setNamedTag($nbt);	
	}

	public function getSuccessEnchant() : ?Enchantment{
		$tag = $this->getNamedTag()->getIntArray("successenchant");
		return PvP::getInstance()->getEnchantments()->getEnchantment($tag[0], $tag[1]);
	}

	public function setSuccessEnchant(int $id, int $level) : void{
		$nbt = $this->getNamedTag();
		$nbt->setIntArray("successenchant", [$id, $level]);
		$this->setNamedTag($nbt);
	}

	public function getFailEnchant() : ?Enchantment{
		$tag = $this->getNamedTag()->getIntArray("failenchant");
		return PvP::getInstance()->getEnchantments()->getEnchantment($tag[0], $tag[1]);
	}

	public function setFailEnchant(int $id, int $level) : void{
		$nbt = $this->getNamedTag();
		$nbt->setIntArray("failenchant", [$id, $level]);
		$this->setNamedTag($nbt);
	}

	public function getRedeemCost() : int{
		return $this->getNamedTag()->getInt("redeemcost");
	}

	public function setRedeemCost(int $cost) : void{
		$nbt = $this->getNamedTag();
		$nbt->setInt("redeemcost", $cost);
		$this->setNamedTag($nbt);
	}

	public function succeeded() : bool{
		if($this->getSuccessRate() > $this->getFailRate()){
			return mt_rand(1, 100) <= $this->getSuccessRate();
		}else{
			return mt_rand(1, 100) >= $this->getSuccessRate();
		}
	}

}