<?php namespace pvp\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\plugin\Plugin;

use pocketmine\Server;
use pocketmine\player\{
	Player,
};

use pvp\PvP;
use pvp\PvPPlayer;

use core\utils\TextFormat;

class SpawnCommand extends Command{

	public function __construct(public PvP $plugin, string $name, string $description){
		parent::__construct($name, $description);
		$this->setPermission("pvp.perm");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		/** @var PvPPlayer $sender */
		if($sender instanceof Player && (empty($args) || !$sender->isTier3())){
			$gs = $sender->getGameSession()->getGame();
			if($gs->inGame()){
				$sender->sendMessage(TextFormat::RI . "You cannot use this command while in a game!");
				return;
			}

			if($sender->getGameSession()->getCombat()->getCombatMode()->inCombat()){
				$sender->sendMessage(TextFormat::RI . "You cannot go to spawn while in combat!");
				return;
			}

			$asession = $sender->getGameSession()->getArenas();
			if($asession->inArena()){
				$asession->setArena();
			}

			$sender->gotoSpawn();
			$sender->sendMessage(TextFormat::GN . "Teleported to spawn!");
			return;
		}

		/** @var PvPPlayer $player */
		$player = Server::getInstance()->getPlayerByPrefix(array_shift($args));
		if(!$player instanceof Player){
			$sender->sendMessage(TextFormat::RI . "Player not online!");
			return;
		}

		$player->gotoSpawn();
		$asession = $sender->getGameSession()->getArenas();
		if($asession->inArena()){
			$asession->setArena();
		}
		$sender->sendMessage(TextFormat::GN . "Teleported " . TextFormat::YELLOW . $player->getName() . TextFormat::GRAY . " to spawn!");
	}

	public function getPlugin() : Plugin{
		return $this->plugin;
	}

}