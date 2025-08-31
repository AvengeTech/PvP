<?php namespace pvp\games\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;

use pvp\PvP;
use pvp\PvPPlayer;
use pvp\games\GameManager;
use pvp\games\ui\GameSelectUi;

use core\utils\TextFormat;

class GameCommand extends Command{

	public function __construct(public PvP $plugin, string $name, string $description){
		parent::__construct($name, $description);
		$this->setPermission("pvp.perm");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		/** @var PvPPlayer $sender */
		if(!$sender instanceof Player || !$sender->isLoaded()) return;

		if($sender->getGameSession()->getGame()->inGame()){
			$sender->sendMessage(TextFormat::RI . "You are already in a game!");
			return;
		}

		$games = [];
		$prac = false;
		if(count($args) > 0){
			$games = GameManager::getInstance()->getHandlersBy(array_shift($args));
			$prac = array_shift($args) ?? false;
		}

		if(count($games) === 1){
			$game = array_shift($games);
			$game->getAvailableGame()->addPlayer($sender);
			return;
		}

		$sender->showModal(new GameSelectUi($sender, $prac, $games));	
	}

	public function getPlugin() : Plugin{
		return $this->plugin;
	}

}