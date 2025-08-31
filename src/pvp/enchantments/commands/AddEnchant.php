<?php namespace pvp\enchantments\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\plugin\Plugin;

use pocketmine\player\Player;
use pocketmine\item\enchantment\Enchantment as PMEnch;

use pvp\PvP;
use pvp\PvPPlayer;
use pvp\enchantments\{
	EnchantmentData
};
use pvp\enchantments\type\Enchantment;

use core\utils\TextFormat;

class AddEnchant extends Command{

	public $plugin;

	public function __construct(PvP $plugin, $name, $description){
		$this->plugin = $plugin;
		parent::__construct($name,$description);
		$this->setPermission("pvp.tier3");
		$this->setAliases(["addench"]);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		/** @var PvPPlayer $sender */
		if(!$sender instanceof Player){
			$sender->sendMessage(TextFormat::RI . "no");
			return false;
		}
		if(!PvP::getInstance()->isTestServer()){
			if(!$sender->isTier3()){
				$sender->sendMessage(TextFormat::RN . "You do not have permission to use this command");
				return false;
			}
		}

		if(count($args) < 1 || !$sender instanceof Player){
			$sender->sendMessage(TextFormat::RN . "Usage: /addenchant <id:name> [level=max]");
			return false;
		}

		$enchantment = null;
		$ench = array_shift($args);
		if(is_numeric($ench)){
			$enchant = PvP::getInstance()->getEnchantments()->getEnchantment($ench);
			if($enchant instanceof Enchantment)
				$enchantment = $enchant->getEnchantment();
		}else{
			$enchant = PvP::getInstance()->getEnchantments()->getEnchantmentByName($ench, true);
			if($enchant instanceof Enchantment)
				$enchantment = $enchant->getEnchantment();
		}
		if(!$enchantment instanceof PMEnch){
			$sender->sendMessage(TextFormat::RN . "Invalid enchantment provided!");
			return false;
		}

		$item = $sender->getInventory()->getItemInHand();
		if(!EnchantmentData::canEnchantWith($item, $enchant)){
			$sender->sendMessage(TextFormat::RI . "You cannot apply this enchantment to this item!");
			return false;
		}

		$max = $enchant->getMaxLevel();
		$level = empty($args) ? $max : (int) array_shift($args);

		if($level <= 0){
			$sender->sendMessage(TextFormat::RN . "Level must be between 1-" . $max . "!");
			return false;
		}


		$data = PvP::getInstance()->getEnchantments()->getItemData($item);
		$data->addEnchantment($enchantment, $level);
		$sender->getInventory()->setItemInHand($data->getItem());

		$sender->sendMessage(TextFormat::GI . "Item in hand enchanted!");
		return true;
	}

	public function getPlugin() : Plugin{
		return $this->plugin;
	}

}