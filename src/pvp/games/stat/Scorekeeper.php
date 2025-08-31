<?php namespace pvp\games\stat;

use pocketmine\player\Player;

use pvp\games\type\GameSettings;
use pvp\PvPPlayer;

/**
 * @method PvPPlayer getPlayer()
 */
class Scorekeeper{

	const BASE_PARTICIPATION_XP = 10;
	const BASE_KILL_XP = 5;
	const ROUND_WINNER_XP = 50;
	
	public array $score = [];
	
	public int $round = 0;
	
	public bool $eliminated = false;
	
	public bool $roundWinner = false;

	public function __construct(public GameSettings $settings, public ?Player $player = null){
		$this->score["kills"] = 0;
		$this->score["hits"] = 0;

		$this->score["combo"] = 0;
		$this->score["highest_combo"] = 0;

		$this->score["streak"] = 0;
		$this->score["highest_streak"] = 0;

		if($settings->hasRespawns()){
			$this->score["lives"] = $settings->getLives();
			$this->score["deaths"] = 0;
		}
	}

	public function getSettings() : GameSettings{
		return $this->settings;
	}
	
	public function getPlayer() : ?Player{
		return $this->player;
	}

	public function getScore(string $name) : int{
		return $this->score[$name] ?? -1;
	}

	public function setScore(string $name, int $value) : void{
		$this->score[$name] = $value;
	}

	public function addScore(string $name, int $value = 1) : int{
		$this->setScore($name, ($total = $this->getScore($name) + $value));
		return $total;
	}

	public function takeScore(string $name, int $value = 1) : int{
		$this->setScore($name, ($total = $this->getScore($name) - $value));
		return $total;
	}

	public function isEliminated() : bool{
		return $this->eliminated;
	}

	public function setEliminated(bool $eliminated = true) : void{
		$this->eliminated = $eliminated;
	}
	
	public function getRound() : int{
		return $this->round;
	}
	
	public function setRound(int $round) : void{
		$this->round = $round;
	}
	
	public function isRoundWinner() : bool{
		return $this->roundWinner;
	}
	
	public function setRoundWinner(bool $winner = true) : void{
		$this->roundWinner = $winner;
	}

	public function getTotalXp() : int{
		$xp = self::BASE_PARTICIPATION_XP * $this->getSettings()->getXpkr();
		$xp += self::BASE_KILL_XP * $this->getScore("kills") * $this->getSettings()->getXpkr();
		if($this->isRoundWinner()) $xp += self::ROUND_WINNER_XP * $this->getSettings()->getXpkr();

		return $xp;
	}

	public function getXpBreakdown() : array{
		$breakdown = [];
		$breakdown["kills"] = self::BASE_KILL_XP * $this->getScore("kills") * $this->getSettings()->getXpkr();
		if($this->isRoundWinner())
			$breakdown["winner"] = self::ROUND_WINNER_XP * $this->getSettings()->getXpkr();

		return $breakdown;
	}

	public function reward() : void{
		$xp = $this->getTotalXp();
		
		$player = $this->getPlayer();
		/** @var PvPPlayer $player */
		$player?->getGameSession()?->getLevels()->addExperience($xp);
	}

}