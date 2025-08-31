<?php namespace pvp\leaderboards\types;

use core\user\User;
use core\utils\TextFormat;

use pvp\PvP;

class PvPKdrLeaderboard extends Leaderboard implements MysqlUpdate{

	public function getType() : string{
		return "pvp_kdr";
	}

	public function calculate() : void{
		$texts = [];

		$texts[] = TextFormat::RED . TextFormat::BOLD . TextFormat::ICON_ARMOR . " Highest /PvP KDR " . TextFormat::ICON_ARMOR;

		$top = [];
		
		$db = PvP::getInstance()->getDatabase("here");
		$stmt = $db->prepare("SELECT xuid, pvp_kills / pvp_deaths FROM combat_stats ORDER BY pvp_kills / pvp_deaths DESC LIMIT 10");
		$stmt->bind_result($xuid, $total);
		if($stmt->execute()){
			while($stmt->fetch()){
				$top[$xuid] = $total;
			}
		}

		$i = 1;
		foreach($top as $xuid => $value){
			$name = (new User($xuid))->getGamertag();
			$texts[] = TextFormat::RED . $i . ". " . TextFormat::YELLOW . $name . " " . TextFormat::GRAY . "- " . TextFormat::AQUA . $value;
			$i++;
		}

		$this->texts = $texts;

		$this->updateSpawnedTo();
	}

}