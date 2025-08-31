<?php namespace pvp\leaderboards\types;

use pvp\PvP;

use core\user\User;
use core\utils\TextFormat;

class TechitsLeaderboard extends Leaderboard implements MysqlUpdate{

	public function getType() : string{
		return "techits";
	}

	public function calculate() : void{
		$texts = [];

		$texts[] = TextFormat::AQUA . TextFormat::BOLD . TextFormat::ICON_TOKEN . " Most Techits " . TextFormat::ICON_TOKEN;

		$top = [];
		
		$db = PvP::getInstance()->getDatabase("here");
		$stmt = $db->prepare("SELECT xuid, techits FROM techits ORDER BY techits DESC LIMIT 10");
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