<?php namespace pvp\games\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;

use pvp\PvP;
use pvp\PvPPlayer;
use pvp\games\entity\PracticeBot as PracticeBotEntity;

use core\utils\TextFormat;

class PracticeBot extends Command{

	public function __construct(public PvP $plugin, string $name, string $description){
		parent::__construct($name, $description);
		$this->setPermission("pvp.perm");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		/** @var PvPPlayer $sender */
		if(!$sender instanceof Player || !$sender->isTier3()) return;


		$bot = new PracticeBotEntity($sender->getLocation(), $sender->getSkin());
		$bot->setTargetEntity($sender);
		$bot->spawnToAll();
		$bot->started = true;

		$sender->sendMessage(TextFormat::GI . "Spawned practice bot!");
	}

	public function getPlugin() : Plugin{
		return $this->plugin;
	}

}