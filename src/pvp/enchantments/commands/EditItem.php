<?php namespace pvp\enchantments\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\plugin\Plugin;

use pocketmine\player\Player;

use pvp\PvP;
use pvp\PvPPlayer;
use pvp\enchantments\uis\StaffItemEditorUi;

use core\utils\TextFormat;

class EditItem extends Command{

	public $plugin;

	public function __construct(PvP $plugin, $name, $description){
		$this->plugin = $plugin;
		parent::__construct($name,$description);
		$this->setPermission("pvp.tier3");
		$this->setAliases(["ei"]);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) {
		/** @var PvPPlayer $sender */
		if(!$sender instanceof Player){
			$sender->sendMessage("no");
			return false;
		}
		if(!PvP::getInstance()->isTestServer()){
			if(!$sender->isTier3()){
				$sender->sendMessage(TextFormat::RN . "You do not have permission to use this command");
				return false;
			}
		}

		$sender->showModal(new StaffItemEditorUi($sender));
		return true;
	}

	public function getPlugin() : Plugin{
		return $this->plugin;
	}

}