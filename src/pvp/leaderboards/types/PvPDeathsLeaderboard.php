<?php namespace pvp\leaderboards\types;

use pvp\PvP;

use core\user\User;
use core\utils\TextFormat;

class PvPDeathsLeaderboard extends Leaderboard implements MysqlUpdate{

	public function getType() : string{
		return "pvp_deaths";
	}

	public function calculate() : void{
		$texts = [];

		$texts[] = TextFormat::RED . TextFormat::BOLD . TextFormat::ICON_ARMOR . " Most /PvP Deaths " . TextFormat::ICON_ARMOR;

		$top = [];
		
		$db = PvP::getInstance()->getDatabase("here");
		$stmt = $db->prepare("SELECT xuid, pvp_deaths FROM combat_stats ORDER BY pvp_deaths DESC LIMIT 10");
		$stmt->bind_result($xuid, $total);
		if($stmt->execute()){
			while($stmt->fetch()){
				$top[$xuid] = $total;
			}
		}

		$i = 1;
		foreach($top as $xuid => $value){
			$name = (new User($xuid))->getGamertag();
			$texts[] = TextFormat::RED . $i . ". " . TextFormat::YELLOW . $name . " " . TextFormat::GRAY . "- " . TextFormat::AQUA . number_format($value);
			$i++;
		}

		$this->texts = $texts;

		$this->updateSpawnedTo();
	}

}