<?php namespace pvp\leaderboards\types;

use core\Core;
use core\user\User;
use core\utils\TextFormat;

class VoteStreakLeaderboard extends Leaderboard implements MysqlUpdate{

	public function getType() : string{
		return "vote_streak";
	}

	public function calculate() : void{
		$texts = [];

		$texts[] = TextFormat::AQUA . TextFormat::BOLD . TextFormat::ICON_MINECOIN . " Highest Vote Streak " . TextFormat::ICON_MINECOIN;

		$top = [];
		
		$db = Core::getInstance()->database;
		$stmt = $db->prepare("SELECT xuid, highest FROM vote_streak ORDER BY highest DESC LIMIT 10");
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