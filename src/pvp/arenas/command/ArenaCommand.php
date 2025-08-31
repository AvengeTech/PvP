<?php namespace pvp\arenas\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;

use pvp\PvP;
use pvp\arenas\ui\ArenaSelectUi;
use pvp\PvPPlayer;

use core\utils\TextFormat;

class ArenaCommand extends Command{

	public function __construct(public PvP $plugin, string $name, string $description){
		parent::__construct($name, $description);
		$this->setPermission("pvp.perm");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$sender instanceof PvPPlayer) return;

		$arenas = PvP::getInstance()->getArenas();
		if($arenas->inArena($sender)){
			$sender->sendMessage(TextFormat::RI . "You are already in an arena!");
			return;
		}
		
		if($sender->getGameSession()->getGame()->inGame()){
			$sender->sendMessage(TextFormat::RI . "You can't teleport to an arena while in a game!");
			return;
		}
		
		if(count($args) === 0 || !$sender->isStaff()){
			$sender->showModal(new ArenaSelectUi($sender));
			return;
		}

		//other game checks?

		if(count($args) > 0){
			$a = null;
			foreach($arenas->getArenas() as $arena){
				if($arena->getName() == $args[0]){
					$a = $arena;
					break;
				}
			}
			if($a == null){
				$sender->sendMessage(TextFormat::RI . "Arena with this name doesn't exist!");
				return;
			}
			if($a->isLocked() && !$sender->isStaff()){
				$sender->sendMessage(TextFormat::RI . "You must be a staff member to access this arena!");
				return;
			}

			//check games

			$a->teleportTo($sender);
			return;
		}

		$sender->sendMessage(TextFormat::RI . "No arenas are active. Maybe an error?");
	}

	public function getPlugin() : Plugin{
		return $this->plugin;
	}

}