<?php namespace pvp\techits\commands;

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

class AddTechits extends Command{

	public function __construct(public PvP $plugin, string $name, string $description){
		parent::__construct($name, $description);
		$this->setPermission("pvp.tier3");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : void{
		if($sender instanceof Player){
			if(!$sender->isTier3()){
				$sender->sendMessage(TextFormat::RI . "You cannot use this command.");
				return;
			}
		}
		if(count($args) != 2){
			$sender->sendMessage(TextFormat::RI . "Usage: /addtechits <player> <amount>");
			return;
		}

		$name = array_shift($args);
		$amount = (int) array_shift($args);

		$player = $this->plugin->getServer()->getPlayerByPrefix($name);
		if($player instanceof Player){
			$name = $player->getName();
		}

		if($amount <= 0 || $amount > 100000000){
			$sender->sendMessage(TextFormat::RI . "Amount must be between 0 and 100,000,000!");
			return;
		}

		Core::getInstance()->getUserPool()->useUser($name, function(User $user) use($sender, $amount) : void{
			if(!$user->valid()){
				$sender->sendMessage(TextFormat::RI . "Player never seen!");
				return;
			}
			PvP::getInstance()->getSessionManager()->useSession($user, function(PvPSession $session) use($sender, $user, $amount) : void{
				$session->getTechits()->addTechits($amount);
				if(!$user->validPlayer()){
					$session->getTechits()->saveAsync();
				}else{
					$user->getPlayer()->sendMessage(TextFormat::GI . "You have earned " . TextFormat::AQUA . $amount . " Techits" . TextFormat::GRAY . "!");
				}
				$sender->sendMessage(TextFormat::GI . "Successfully gave " . TextFormat::YELLOW . $user->getGamertag() . TextFormat::AQUA . " " . $amount . " Techits" . TextFormat::GRAY."!");
			});
		});
	}

	public function getPlugin() : Plugin{
		return $this->plugin;
	}

}