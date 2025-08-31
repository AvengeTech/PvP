<?php namespace pvp\levels;

use pvp\PvP;

class Levels{

	public PrizePool $prizePool;

	public function __construct(public PvP $plugin){
		$this->prizePool = new PrizePool([
			//todo: prizes
		]);
	}

	public function getPrizePool(): PrizePool {
		return $this->prizePool;
	}

}