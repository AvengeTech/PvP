<?php namespace pvp\enchantments\effects\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\plugin\Plugin;
use pocketmine\player\Player;

use pvp\PvP;
use pvp\enchantments\effects\items\EffectItem;

use core\utils\TextFormat;
use pvp\PvPPlayer;

class GiveAnimator extends Command{

	public $plugin;

	public function __construct(PvP $plugin, $name, $description){
		$this->plugin = $plugin;
		parent::__construct($name,$description);
		$this->setPermission("pvp.tier3");
		$this->setAliases(["ga"]);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		/** @var PvPPlayer $sender */
		if($sender instanceof Player){
			if(!$sender->isTier3()){
				$sender->sendMessage(TextFormat::RN . "You do not have permission to use this command");
				return false;
			}

			if(count($args) < 1){
				$sender->sendMessage(TextFormat::RN . "Usage: /giveanimator <id:name> [cost]");
				return false;
			}

			$id = array_shift($args);
			if(is_numeric($id)){
				$effect = PvP::getInstance()->getEnchantments()->getEffects()->getEffectById($id);
			}else{
				$effect = PvP::getInstance()->getEnchantments()->getEffects()->getEffectByName($id);
			}
			if($effect === null){
				$sender->sendMessage(TextFormat::RN . "Invalid effect id!");
				return false;
			}

			$item = new EffectItem($effect->getRarity());
			$item->setup($effect, (int)(array_shift($args) ?? -1));
			$sender->getInventory()->addItem($item);

			$sender->sendMessage(TextFormat::GI . "Gave yourself '" . TextFormat::YELLOW . $effect->getName() . TextFormat::GRAY . "' animator");
			return true;
		}
	}

	public function getPlugin() : Plugin{
		return $this->plugin;
	}

}