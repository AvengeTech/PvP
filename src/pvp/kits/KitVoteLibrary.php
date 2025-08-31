<?php namespace pvp\kits;

use pocketmine\player\Player;

class KitVoteLibrary extends KitLibrary{
	
	public array $votes = [];
	public ?Kit $votedKit = null;
	
	public function getVotes() : array{
		return $this->votes;
	}
	
	public function vote(Player $player, Kit $kit) : void{
		$this->votes[$player->getName()] = strtolower($kit->getName());
	}
	
	public function hasVoted(Player $player) : bool{
		return isset($this->votes[$player->getName()]);
	}
	
	public function getKitVotedFor(Player $player) : ?Kit{
		if(!$this->hasVoted($player)) return null;
		return $this->getKit($this->votes[$player->getName()] ?? "no");
	}
	
	public function getVoteTotals() : array{
		$totals = [];
		foreach($this->getKits() as $name => $kit) $totals[strtolower($name)] = 0;
		foreach($this->votes as $player => $vote){
			$totals[strtolower($vote)]++;
		}
		arsort($totals);
		return $totals;
	}
	
	public function getVoteTotal(Kit|string $kit) : int{
		return $this->getVoteTotals()[$kit instanceof Kit ? strtolower($kit->getName()) : $kit] ?? 0;
	}
	
	public function getHighestVoted() : ?Kit{
		$kits = [];
		$total = 0;
		foreach($this->getVoteTotals() as $kit => $ttl){
			if($ttl > $total){
				$kits = [$this->getKit($kit)];
				$total = $ttl;
			}elseif($ttl == $total){
				$kits[] = $this->getKit($kit);
			}
		}
		if(count($kits) > 1){
			return $this->votedKit = $kits[mt_rand(0, count($kits) - 1)];
		}else{
			return $this->votedKit = array_shift($kits);
		}
	}

	public function getDefaultKit() : ?Kit{
		return $this->votedKit ?? $this->getHighestVoted();
	}
	
	public function reset() : void{
		$this->votes = [];
		$this->votedKit = null;
	}

	public function __clone(){
		$this->votes = [];
		$this->votedKit = null;
	}
	
}