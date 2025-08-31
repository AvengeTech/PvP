<?php namespace pvp\leaderboards\types;

use pvp\PvP;
use pvp\arenas\Arena;

use core\Core;
use core\session\mysqli\data\{
	MySqlQuery,
	MySqlRequest
};
use core\utils\TextFormat;

class ArenaLeaderboard extends Leaderboard implements MysqlUpdate{

	public function __construct(
		public Arena $arena,
		public string $statType = "kills",
		public string $duration = "alltime",
		public int $size = 10
	){
		parent::__construct($size);
	}

	public function getArena() : Arena{
		return $this->arena;
	}

	public function getStatType() : string{
		return $this->statType;
	}

	public function getDuration() : string{
		return $this->duration;
	}

	public function getType() : string{
		return "arena_" . $this->getArena()->getId();
	}

	public function calculate() : void{
		PvP::getInstance()->getSessionManager()->sendStrayRequest(new MySqlRequest("update_leaderboard_" . $this->getType() . "_" . $this->getStatType() . "_" . $this->getDuration(), new MySqlQuery(
			"main",
			"SELECT xuid, " . $this->getStatType() . "_" . $this->getDuration() . " FROM arena_stats WHERE arena='" . $this->getArena()->getId() . "' ORDER BY " . $this->getStatType() . "_" . $this->getDuration() . " DESC LIMIT " . $this->getSize() . ";",
			[]
		)), function(MySqlRequest $request) : void{
			$rows = $request->getQuery()->getResult()->getRows();
			$xuids = [];
			foreach($rows as $row){
				$xuids[] = $row["xuid"];
			}
			Core::getInstance()->getUserPool()->useUsers($xuids, function(array $users) use($rows) : void{
				$title = (in_array($this->getStatType(), ["kills", "deaths"]) ? "Most" : "Highest") . " " . ucfirst($this->getStatType()) . " (" . ($this->getDuration() == "alltime" ? "All time" : ucfirst($this->getDuration())) . ")";

				$texts = [TextFormat::BOLD . TextFormat::YELLOW . "Arena: " . $this->getArena()->getName(), TextFormat::AQUA . TextFormat::BOLD . TextFormat::EMOJI_SKULL . " " . $title . " " . TextFormat::EMOJI_SKULL];
				$i = 1;
				foreach($rows as $row){
					$texts[($gt = $users[$row["xuid"]]->getGamertag())] =
						TextFormat::RED . $i . ". " .
						TextFormat::YELLOW . $gt . " " . TextFormat::GRAY . "- " .
						TextFormat::AQUA . number_format($row[$this->getStatType() . "_" . $this->getDuration()]);
					$i++;
				}
				$this->texts = $texts;
				$this->updateSpawnedTo();
			});
		});
	}

}