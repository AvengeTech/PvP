<?php namespace pvp\games\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;

use core\utils\TextFormat;

use pvp\PvP;
use pvp\PvPPlayer;

class TpCommas extends Command{

	public function __construct(public PvP $plugin, string $name, string $description){
		parent::__construct($name, $description);
		$this->setPermission("pvp.staff");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		/** @var PvPPlayer $sender */
		if(!$sender instanceof Player || !$sender->isLoaded() || !$sender->isStaff()) return;

		if(count($args) === 0){
			echo "arg 0",PHP_EOL;
			$sender->sendMessage(TextFormat::RI . "Usage: /tpc x,y,z");
			return;
		}
		$coords = explode(",", array_shift($args));
		if(count($coords) !== 3){
			echo "not 3,",PHP_EOL;
			$sender->sendMessage(TextFormat::RI . "Usage: /tpc x,y,z");
			return;
		}
		$sender->teleport(new Vector3((float) $coords[0], (float) $coords[1], (float) $coords[2]));
		$sender->sendMessage(TextFormat::GI . "Teleported to " . $coords[0] . ", " . $coords[1] . ", " . $coords[2]);
	}

	public function getPlugin() : Plugin{
		return $this->plugin;
	}

}