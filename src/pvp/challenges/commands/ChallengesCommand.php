<?php namespace pvp\challenges\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\player\Player;
use pocketmine\plugin\Plugin;

use pvp\PvP;
use pvp\challenges\ui\ChallengeUi;
use pvp\PvPPlayer;

use core\utils\TextFormat;

class ChallengesCommand extends Command{

	public $plugin;

	public function __construct(PvP $plugin, $name, $description){
		$this->plugin = $plugin;
		parent::__construct($name,$description);
		$this->setPermission("pvp.perm");
		$this->setAliases(["c", "challenge", "chal"]);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$sender instanceof Player) return;
		/** @var PvPPlayer $sender */
		/**$isession = SkyBlock::getInstance()->getIslands()->getSessionManager()->getSession($sender);
		if(!$isession->hasIsland()){
			$sender->sendMessage(TextFormat::RI . "You must have an island to use this command!");
			return false;
		}*/
		$csession = PvP::getInstance()->getChallenges()->getSessionManager()->getSession($sender);
		switch(array_shift($args)){
			default:
				$sender->showModal(new ChallengeUi($sender));
				break;
			case "ca":
				if(!SkyBlock::getInstance()->isTestServer()){
					$sender->showModal(new ChallengeUi($sender));
					break;
				}
				foreach($csession->levelSessions as $sess){
					foreach($sess->getChallenges() as $challenge){
						$challenge->setCompleted();
					}
				}
				$sender->sendMessage(TextFormat::GI . "Force completed all your challenges!");
				break;
		}
	}

	public function getPlugin() : Plugin{
		return $this->plugin;
	}

}