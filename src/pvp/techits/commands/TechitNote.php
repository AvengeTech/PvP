<?php namespace pvp\techits\commands;

use core\utils\ItemRegistry;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;

use pvp\{
	PvP,
	PvPPlayer as Player
};

use core\utils\TextFormat;

class TechitNote extends Command{

	public function __construct(public PvP $plugin, string $name, string $description){
		parent::__construct($name, $description);
		$this->setPermission("pvp.perm");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if($sender instanceof Player){
			$amount = (int) array_shift($args);

			if($amount <= 0){
				$sender->sendMessage(TextFormat::RN . "Amount must be at least 1!");
				return;
			}

			if($amount > $sender->getTechits()){
				$sender->sendMessage(TextFormat::RN . "You do not have enough Techits!");
				return;
			}

			$item = ItemRegistry::TECHIT_NOTE();
			$item->setup($amount);
			if(!$sender->getInventory()->canAddItem($item)){
				$sender->sendMessage(TextFormat::RN . "Your inventory is full!");
				return;
			}

			$sender->getInventory()->addItem($item);
			$sender->takeTechits($amount);
			$sender->sendMessage(TextFormat::GN . "Techit Note added to your inventory!");

			return;
		}
	}

	public function getPlugin() : Plugin{
		return $this->plugin;
	}

}