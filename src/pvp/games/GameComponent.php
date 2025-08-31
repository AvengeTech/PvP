<?php namespace pvp\games;

use pvp\games\GameManager;
use pvp\games\stat\{
	GameStatHandler
};
use pvp\games\type\Game;
use pvp\kits\Kit;

use core\session\component\{
	ComponentRequest,
	SaveableComponent
};
use core\session\mysqli\data\MySqlQuery;

class GameComponent extends SaveableComponent{

	public ?Game $game = null;
	public ?Kit $kit = null;
	public int $spawnpoint = -1;

	public array $gameStats = [];

	public function getName() : string{
		return "game";
	}

	public function inGame() : bool{
		return $this->getGame() !== null;
	}
	
	public function setGame(?Game $game = null) : void{
		$this->game = $game;
		$this->setKit();
	}

	public function getGame() : ?Game{
		return $this->game;
	}
	
	public function getKit() : ?Kit{
		return $this->kit;
	}
	
	public function hasKit() : bool{
		return $this->kit !== null;
	}
	
	public function setKit(?Kit $kit = null) : void{
		$this->kit = $kit;
	}
	
	public function getSpawnpointKey() : int{
		return $this->spawnpoint;
	}
	
	public function setSpawnpointKey(int $key = -1) : void{
		$this->spawnpoint = $key;
	}

	public function getGameStats() : array{
		return $this->gameStats;
	}

	public function getGameStat(Game $game) : ?GameStatHandler{
		return $this->gameStats[$game->getStatKey()] ?? null;
	}

	public function createTables() : void{
		$db = $this->getSession()->getSessionManager()->getDatabase();
		foreach([
			//"DROP TABLE IF EXISTS game_stats",
			"CREATE TABLE IF NOT EXISTS game_stats(
				xuid BIGINT(16) NOT NULL,
				game VARCHAR(36) NOT NULL,
				stat_tag VARCHAR(36) NOT NULL,
				type INT NOT NULL,
				kills INT NOT NULL DEFAULT 0,
				deaths INT NOT NULL DEFAULT 0,
				wins INT NOT NULL DEFAULT 0,
				losses INT NOT NULL DEFAULT 0,
				hits INT NOT NULL DEFAULT 0,
				highest_combo INT NOT NULL DEFAULT 0,
				highest_streak INT NOT NULL DEFAULT 0,
				extra1 INT NOT NULL DEFAULT 0,
				extra2 INT NOT NULL DEFAULT 0,
				extra3 INT NOT NULL DEFAULT 0,
				PRIMARY KEY(xuid, game, stat_tag, type)
			)",
			"CREATE TABLE IF NOT EXISTS match_stats(
				match_id VARCHAR(64) NOT NULL UNIQUE,
				xuid BIGINT(16) NOT NULL,
				game VARCHAR(36) NOT NULL,
				stat_tag VARCHAR(36) NOT NULL,
				round INT NOT NULL DEFAULT 0,
				kills INT NOT NULL DEFAULT 0,
				deaths INT NOT NULL DEFAULT 0,
				hits INT NOT NULL DEFAULT 0,
				highest_combo INT NOT NULL DEFAULT 0,
				highest_streak INT NOT NULL DEFAULT 0,
				extra1 INT NOT NULL DEFAULT 0,
				extra2 INT NOT NULL DEFAULT 0,
				extra3 INT NOT NULL DEFAULT 0
			)"
		] as $query) $db->query($query);
	}

	public function loadAsync() : void{
		$request = new ComponentRequest($this->getXuid(), $this->getName(), new MySqlQuery("main", "SELECT * FROM game_stats WHERE xuid=?", [$this->getXuid()]));
		$this->newRequest($request, ComponentRequest::TYPE_LOAD);
		parent::loadAsync();
	}

	public function finishLoadAsync(?ComponentRequest $request = null) : void{
		$result = $request->getQuery()->getResult();
		$rows = (array) $result->getRows();
		if(count($rows) > 0){
			$statGroups = [];
			foreach($rows as $row){
				$handler = GameManager::getInstance()->getHandlerBy($row["game"], $row["stat_tag"]);
				if($handler !== null){
					$key = $handler->getBaseGame()->getStatKey();
					$statGroups[$key][$row["type"]] = $row;
					var_dump($row["game"] . "_" . $row["stat_tag"]);
				}
			}
			foreach($statGroups as $key => $group){
				$ks = explode("_", $key);
				$handler = GameManager::getInstance()->getHandlerBy($ks[0], $ks[1]);
				if($handler !== null){
					$this->gameStats[$key] = new GameStatHandler($handler->getBaseGame(), $group[0], $group[1], $group[2]);
				}
			}
		}
		foreach(GameManager::getInstance()->getGameHandlers() as $handler){
			if(!isset($this->gameStats[($key = ($game = $handler->getBaseGame())->getStatKey())])){
				$this->gameStats[$key] = new GameStatHandler($game);
			}
		}

		parent::finishLoadAsync($request);
		echo $this->getName() . " component finished loading async", PHP_EOL;
	}

	public function verifyChange() : bool{
		$verify = $this->getChangeVerify();
		return false; //todo: dunno tbh
	}

	public function saveAsync() : void{
		//$this->setChangeVerify([
		//	"techits" => $this->getTechits(),
		//]);

		$player = $this->getPlayer();
		$request = new ComponentRequest($this->getXuid(), $this->getName(), []);
		foreach($this->getGameStats() as $stats){
			if($stats->hasChanged()){
				var_dump($stats->getGame()->getName() . " stats changed");
				foreach($stats->getAllStats() as $stat){
					$request->addQuery($stat->getQuery($this->getUser()));
				}
			}
		}

		$this->newRequest($request, ComponentRequest::TYPE_SAVE);
		parent::saveAsync();
	}

	public function finishSaveAsync() : void{
		parent::finishSaveAsync();

		echo $this->getName() . " component finished saving async", PHP_EOL;
	}

	public function save() : bool{
		$db = $this->getSession()->getSessionManager()->getDatabase();
		foreach($this->getGameStats() as $stats){
			if($stats->hasChanged()){
				foreach($stats->getAllStats() as $stat){
					$stat->mtSave($this->getUser(), $db);
				}
			}
		}

		echo $this->getName() . " component saved on main thread", PHP_EOL;
		return parent::save();
	}

}