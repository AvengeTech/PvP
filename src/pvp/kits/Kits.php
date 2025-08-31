<?php namespace pvp\kits;

use core\utils\ItemRegistry;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\{
	ItemIdentifier,
	ItemTypeIds,
	VanillaItems
};

use pvp\PvP;
use pvp\enchantments\ItemData;
use pvp\item\{
	FlingBall,
	HealthPotItem
};
use pvp\kits\commands\KitCommand;

class Kits{

	public array $kits = [];

	public function __construct(public PvP $plugin){
		$ench = PvP::getInstance()->getEnchantments();
		
		$oitqbow = VanillaItems::BOW();
		$data = new ItemData($oitqbow);
		$data->addEnchantment($ench->getEnchantmentByName("insta shot")->getEnchantment(), 1);
		$data->setEffectId($ench->getEffects()->getEffectByName("R.I.P.")->getId());

		$pot = new HealthPotItem();
		$this->kits = [
			"spawn_pvp" => new Kit("spawn_pvp", [], [
				0 => VanillaItems::DIAMOND_HELMET(),
				1 => VanillaItems::DIAMOND_CHESTPLATE(),
				2 => VanillaItems::DIAMOND_LEGGINGS(),
				3 => VanillaItems::DIAMOND_BOOTS(),
			]),
			"sumo" => new Kit("sumo", [
				VanillaItems::STICK()
			], []),
			"oitq" => new Kit("oitq", [
				$oitqbow,
				VanillaItems::IRON_SWORD(),
				VanillaItems::ARROW()
			], []),
			"build" => new Kit("build", [
				VanillaItems::IRON_SWORD(),
				VanillaItems::IRON_PICKAXE(),
				VanillaBlocks::NETHERRACK()->asItem()->setCount(128),
				VanillaItems::ENDER_PEARL()->setCount(4),
			], [
				0 => VanillaItems::GOLDEN_HELMET(),
				1 => VanillaItems::IRON_CHESTPLATE(),
				3 => VanillaItems::LEATHER_BOOTS(),
			]),
			
			"sw_basic" => new Kit("basic", [
				VanillaItems::WOODEN_SWORD(),
				VanillaBlocks::COBBLESTONE()->asItem()->setCount(16)
			], [
				VanillaItems::LEATHER_CAP(),
				VanillaItems::GOLDEN_CHESTPLATE(),
			]),
			
			"sd" => new Kit("sd", [
				VanillaItems::DIAMOND_AXE(),
				ItemRegistry::FLING_BALL()->setCount(5)
			], []),
			
			"nodebuff" => new Kit("NoDebuff", [
				VanillaItems::DIAMOND_SWORD(),
				VanillaItems::ENDER_PEARL()->setCount(4),
				$pot,
				$pot,
				$pot,
				$pot,
				$pot,
				$pot,
				$pot,
				$pot,
				$pot,
				$pot,
				$pot,
				$pot,
				$pot,
				$pot,
				$pot,
				$pot,
			], [
				VanillaItems::DIAMOND_HELMET(),
				VanillaItems::DIAMOND_CHESTPLATE(),
				VanillaItems::DIAMOND_LEGGINGS(),
				VanillaItems::DIAMOND_BOOTS(),
			]),
		];

		$plugin->getServer()->getCommandMap()->register("kit", new KitCommand($plugin, "kit", "Equip a kit!"));
	}

	public function getKits() : array{
		return $this->kits;
	}

	public function getKit(string $name) : ?Kit{
		return $this->kits[$name] ?? null;
	}

	public function kitExists(string $name) : bool{
		return $this->getKit($name) !== null;
	}

}