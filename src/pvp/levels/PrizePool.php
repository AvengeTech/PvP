<?php namespace pvp\levels;

use pocketmine\player\Player;

class PrizePool{
	
	public function __construct(public array $prizes = []){}
	
	public function getPrizes() : array{
		return $this->prizes;
	}
	
	public function getPrize(int $level) : ?Prize{
		return $this->prizes[$level] ?? null;
	}

	public function checkPrizePool(Player $player, int $level) {
		// todo
	}
	
}