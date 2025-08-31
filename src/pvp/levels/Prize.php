<?php namespace pvp\levels;

use pocketmine\player\Player;

class Prize{
	
	public function __construct(
		public string $name,
		public \Closure $closure
	){}
	
	public function getName() : string{
		return $this->name;
	}
	
	public function getClosure() : \Closure{
		return $this->closure;
	}
	
	public function give(Player $player) : void{
		$this->getClosure()($player);
	}
}