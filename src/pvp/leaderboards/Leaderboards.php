<?php namespace pvp\leaderboards;

use pocketmine\player\Player;

use pvp\PvP;
use pvp\leaderboards\types\{
	MultiLeaderboard,

	ArenaLeaderboard,
	GameLeaderboard,

	MysqlUpdate
};

class Leaderboards{

	const UPDATE_TICKS = 600;

	public int $ticks = 0;

	public array $leaderboards = [];

	public array $left = [];

	public function __construct(public PvP $plugin){
		$albs = [];
		foreach(PvP::getInstance()->getArenas()->getArenas() as $arena){
			$albs[] = new ArenaLeaderboard($arena, "kills", "alltime");
			$albs[] = new ArenaLeaderboard($arena, "deaths", "alltime");
			$albs[] = new ArenaLeaderboard($arena, "combo", "alltime");
			$albs[] = new ArenaLeaderboard($arena, "streak", "alltime");
		}
		$this->leaderboards["arenas"] = new MultiLeaderboard($albs, "arenas");

		$glbs = [];
		foreach(PvP::getInstance()->getGames()->getGameManager()->getGameHandlers() as $handler){
			$game = $handler->getBaseGame();
			$glbs[] = new GameLeaderboard($game, "wins", 0);
			$glbs[] = new GameLeaderboard($game, "hits", 0);
			$glbs[] = new GameLeaderboard($game, "highest_combo", 0);
			$glbs[] = new GameLeaderboard($game, "highest_streak", 0);

			//$glbs[] = new GameLeaderboard($game, "wins", 1);
			//$glbs[] = new ArenaLeaderboard($game, "hits", 1);
			//$glbs[] = new ArenaLeaderboard($game, "highest_combo", 1);
			//$glbs[] = new ArenaLeaderboard($game, "highest_streak", 1);
		}
		$this->leaderboards["games"] = new MultiLeaderboard($glbs, "games");
		
		/**$this->leaderboards["pvp_kills"] = new PvPKillsLeaderboard();
		$this->leaderboards["mine_kills"] = new MineKillsLeaderboard();
		$this->leaderboards["grinder_kills"] = new GrinderKillsLeaderboard();
		$this->leaderboards["pvp_kdr"] = new PvPKdrLeaderboard();
		$this->leaderboards["mine_kdr"] = new MineKdrLeaderboard();

		$this->leaderboards["prestige"] = new PrestigeLeaderboard();
		$this->leaderboards["mined_blocks"] = new MinedBlocksLeaderboard();
		$this->leaderboards["techits"] = new TechitsLeaderboard();
		$this->leaderboards["bt_wins"] = new BlockTournamentWinsLeaderboard();
		$this->leaderboards["bt_mined"] = new BlockTournamentMinedBlocksLeaderboard();

		$this->leaderboards["gang_trophies"] = new GangTrophiesLeaderboard();
		$this->leaderboards["gang_battles"] = new GangBattlesLeaderboard();
		$this->leaderboards["gang_kills"] = new GangKillsLeaderboard();
		$this->leaderboards["gang_blocks"] = new GangBlocksLeaderboard();
		$this->leaderboards["gang_bank"] = new GangBankLeaderboard();

		$this->leaderboards["pvp_deaths"] = new PvPDeathsLeaderboard();
		$this->leaderboards["mine_deaths"] = new MineDeathsLeaderboard();

		$this->leaderboards["keys"] = new KeysLeaderboard();
		$this->leaderboards["keys_opened"] = new KeysOpenedLeaderboard();*/

		foreach($this->getLeaderboards() as $lb){
			if($lb instanceof MysqlUpdate){
				$lb->calculate();
			}
		}
	}

	public function getLeaderboards() : array{
		return $this->leaderboards;
	}

	public function tick() : void{
		$this->ticks++;
		if($this->ticks >= self::UPDATE_TICKS){
			$this->ticks = 0;
			foreach($this->getLeaderboards() as $key => $leaderboard){
				if($leaderboard instanceof MysqlUpdate){
					$leaderboard->calculate();
				}
			}
		}

		foreach($this->getLeaderboards() as $key => $leaderboard){
			if($leaderboard instanceof MultiLeaderboard){
				$leaderboard->tick();
			}
		}
	}
	
	public function changeLevel(Player $player, string $newlevel) : void{
		foreach($this->leaderboards as $leaderboard){
			$leaderboard->changeLevel($player, $newlevel);
		}
	}

	public function onJoin(Player $player) : void{
		unset($this->left[$player->getName()]);
		foreach($this->leaderboards as $leaderboard){
			if(!$leaderboard instanceof MysqlUpdate) $leaderboard->calculate();
			$leaderboard->spawn($player);
		}
	}

	public function onQuit(Player $player) : void{
		$this->left[$player->getName()] = true;
		foreach($this->leaderboards as $leaderboard){
			$leaderboard->despawn($player);
			if($leaderboard->isOn($player) && $leaderboard instanceof MysqlUpdate) $leaderboard->calculate();
		}
	}

}