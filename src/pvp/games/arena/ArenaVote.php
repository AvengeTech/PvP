<?php namespace pvp\games\arena;

use pocketmine\player\Player;

use pvp\games\type\Game;

class ArenaVote{

	public array $votes = [];

	public array $arenas = [];
	public ?Arena $votedArena = null;

	public function __construct(
		public Game $game,
		int $total = 3
	){
		if(count($arenas = $game->getHandler()->getArenas()) <= $total){
			foreach($arenas as $arena) $this->arenas[] = $arena;
		}else{
			for($i = 0; $i < $total; $i++){
				$arena = $game->getHandler()->getRandomArena($this->arenas);
				$this->arenas[$arena->getId()] = $arena;
			}
		}
	}

	public function getGame() : Game{
		return $this->game;
	}

	public function getArenas() : array{
		return $this->arenas;
	}

	public function getVotes() : array{
		return $this->votes;
	}
	
	public function vote(Player $player, Arena $arena) : void{
		$this->votes[$player->getName()] = $arena->getId();
	}
	
	public function hasVoted(Player $player) : bool{
		return isset($this->votes[$player->getName()]);
	}

	public function getArenaVotedFor(Player $player) : ?Arena{
		if(!$this->hasVoted($player)) return null;
		return $this->getGame()->getArena($this->votes[$player->getName()] ?? "no");
	}
	
	public function getVoteTotals() : array{
		$totals = [];
		foreach($this->getArenas() as $arena) $totals[$arena->getId()] = 0;
		foreach($this->votes as $player => $vote){
			$totals[$vote]++;
		}
		arsort($totals);
		return $totals;
	}
	
	public function getVoteTotal(Arena|string $arena) : int{
		return $this->getVoteTotals()[$arena instanceof Arena ? $arena->getId() : $arena] ?? 0;
	}
	
	public function getHighestVoted() : ?Arena{
		$arenas = [];
		$total = 0;
		foreach($this->getVoteTotals() as $arena => $ttl){
			if($ttl > $total){
				$arenas = [$this->getGame()->getHandler()->getArena($arena)];
				$total = $ttl;
			}elseif($ttl == $total){
				$arenas[] = $this->getGame()->getHandler()->getArena($arena);
			}
		}
		if(count($arenas) > 1){
			return $this->votedArena = $arenas[array_rand($arenas)];
		}elseif(count($arenas) === 0 || $total === 0){
			return $this->votedArena = $this->getArenas()[array_rand($this->getArenas())];
		}else{
			return $this->votedArena = array_shift($arenas);
		}
	}

	public function getVotedArena() : ?Arena{
		return $this->votedArena;
	}

	public function getDefaultArena() : ?Arena{
		return $this->votedArena ?? $this->getHighestVoted();
	}
	
	public function reset() : void{
		$this->votes = [];
		$this->votedArena = null;
	}

	public function __clone(){
		$this->votes = [];
		$this->votedArena = null;
	}

}