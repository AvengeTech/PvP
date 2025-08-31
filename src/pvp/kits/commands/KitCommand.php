<?php namespace pvp\kits\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\player\Player;
use pocketmine\plugin\Plugin;

use pvp\PvP;

use core\utils\TextFormat;
use pvp\PvPPlayer;

class KitCommand extends Command{

	public $plugin;

	public function __construct(PvP $plugin, $name, $description){
		$this->plugin = $plugin;
		parent::__construct($name,$description);
		$this->setPermission("pvp.perm");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		/** @var PvPPlayer $sender */
		if($sender instanceof Player){
			if(!$sender->isTier3()){
				$sender->sendMessage(TextFormat::RI . "You cannot use this command");
				return false;
			}
			if(count($args) != 1){
				$sender->sendMessage(TextFormat::RI . "Usage: /kit <name>");
				//$sender->sendMessage(PvP::getInstance()->getKits()->getKitListString($sender));
				return false;
			}
			$kit = PvP::getInstance()->getKits()->getKit(strtolower(array_shift($args)));
			if($kit === null){
				$sender->sendMessage(TextFormat::RI . "Invalid kit!");
				return false;
			}

			$kit->equip($sender);

			$sender->sendMessage(TextFormat::GI . "Successfully equipped the " . $kit->getName() . " kit.");
			return true;
		}
	}

	public function getPlugin() : Plugin{
		return $this->plugin;
	}

}