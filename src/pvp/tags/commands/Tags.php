<?php namespace pvp\tags\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;

use pvp\PvP;
use pvp\PvPPlayer;
use pvp\tags\uis\TagSelector;

class Tags extends Command{

	public function __construct(public PvP $plugin, string $name, string $description){
		parent::__construct($name, $description);
		$this->setPermission("pvp.perm");
		$this->setAliases(["tag", "t"]);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : void{
		/** @var PvPPlayer $sender */
		if($sender instanceof Player) $sender->showModal(new TagSelector($sender));
	}

	public function getPlugin() : Plugin{
		return $this->plugin;
	}

}