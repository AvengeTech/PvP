<?php namespace pvp\techits\commands;

use pocketmine\Server;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;

use pvp\{
	PvP,
	PvPPlayer as Player,
	PvPSession
};

use core\Core;
use core\user\User;
use core\utils\TextFormat;

class MyTechits extends Command{

	public function __construct(public PvP $plugin, string $name, string $description){
		parent::__construct($name, $description);
		$this->setPermission("pvp.perm");
		$this->setAliases(["mymoney", "techits", "money"]);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : void{
		if($sender instanceof Player){
			if(count($args) == 0 || !$sender->isStaff()){
				$sender->sendMessage(TextFormat::YN . "You have " . TextFormat::AQUA . $sender->getTechits() . " Techits");
				return;
			}
		}

		if(count($args) == 0){
			$sender->sendMessage(TextFormat::RI . "Please enter a username!");
			return;
		}

		$name = array_shift($args);
		$player = Server::getInstance()->getPlayerByPrefix($name);
		if($player instanceof Player){
			$name = $player->getName();
		}

		Core::getInstance()->getUserPool()->useUser($name, function(User $user) use($sender) : void{
			if(!$user->valid()){
				$sender->sendMessage(TextFormat::RI . "Player never seen!");
				return;
			}
			PvP::getInstance()->getSessionManager()->useSession($user, function(PvPSession $session) use($sender, $user) : void{
				$sender->sendMessage(TextFormat::YN . "Player " . TextFormat::YELLOW . $user->getGamertag() . TextFormat::GRAY . " has " . TextFormat::AQUA . $session->getTechits()->getTechits() . " techits");
			});
		});
	}

	public function getPlugin() : Plugin{
		return $this->plugin;
	}

}