<?php namespace pvp\leaderboards\types;

use pvp\PvP;
use pvp\games\stat\GameStatHandler;
use pvp\games\type\Game;

use core\Core;
use core\session\mysqli\data\{
	MySqlQuery,
	MySqlRequest
};
use core\utils\TextFormat;

class GameLeaderboard extends Leaderboard implements MysqlUpdate{

	public function __construct(
		public Game $game,
		public string $statType = "kills",
		public int $duration = GameStatHandler::TYPE_ALLTIME,
		public int $size = 10
	){
		parent::__construct($size);
	}

	public function getGame() : Game{
		return $this->game;
	}

	public function getStatType() : string{
		return $this->statType;
	}

	public function getDuration() : int{
		return $this->duration;
	}

	public function getType() : string{
		return "game_" . $this->getGame()->getName();
	}

	public function calculate() : void{
		PvP::getInstance()->getSessionManager()->sendStrayRequest(new MySqlRequest("update_leaderboard_" . $this->getType() . "_" . $this->getStatType() . "_" . $this->getDuration(), new MySqlQuery(
			"main",
			"SELECT xuid, " . $this->getStatType() . " FROM game_stats WHERE game='" . $this->getGame()->getName() . "' AND stat_tag='" . $this->getGame()->getSettings()->getStatTag() . "' ORDER BY " . $this->getStatType() . " DESC LIMIT " . $this->getSize() . ";",
			[]
		)), function(MySqlRequest $request) : void{
			$rows = $request->getQuery()->getResult()->getRows();
			$xuids = [];
			foreach($rows as $row){
				$xuids[] = $row["xuid"];
			}
			Core::getInstance()->getUserPool()->useUsers($xuids, function(array $users) use($rows) : void{
				$title = match($this->getStatType()){
					"kills" => "Most Kills",
					"deaths" => "Most Deaths",
					"wins" => "Most Wins",
					"losses" => "Most Losses (L)",
					"hits" => "Most Hits",
					"highest_combo" => "Highest Combo",
					"highest_streak" => "Highest Streak",
				};
				$title = $title . " " . match($this->getDuration()){
					GameStatHandler::TYPE_ALLTIME => "(All time)",
					GameStatHandler::TYPE_WEEKLY => "(Weekly)",
					GameStatHandler::TYPE_MONTHLY => "(Monthly)",
				};

				$texts = [TextFormat::BOLD . TextFormat::YELLOW . "Game: " . $this->getGame()->getSettings()->getDisplayName(), TextFormat::AQUA . TextFormat::BOLD . TextFormat::EMOJI_CONTROLLER . " " . $title . " " . TextFormat::EMOJI_CONTROLLER];
				$i = 1;
				foreach($rows as $row){
					$texts[($gt = $users[$row["xuid"]]->getGamertag())] =
						TextFormat::RED . $i . ". " .
						TextFormat::YELLOW . $gt . " " . TextFormat::GRAY . "- " .
						TextFormat::AQUA . number_format($row[$this->getStatType()]);
					$i++;
				}
				$this->texts = $texts;
				$this->updateSpawnedTo();
			});
		});
	}

}