<?php namespace pvp;

use pocketmine\scheduler\Task;

class TickTask extends Task{

	public $plugin;
	public $runs = 0;

	public function __construct(PvP $plugin){
		$this->plugin = $plugin;
	}

	public function onRun() : void{
		$this->runs++;
		
		$this->plugin->getGames()->tick();

		if($this->runs %20 == 0){
			$this->plugin->getArenas()->tick();
			$this->plugin->getHud()->tick();
			$this->plugin->getLeaderboards()->tick();
		}

		if($this->runs %5 == 0){
			$this->plugin->getEnchantments()->tick($this->runs);
			$this->plugin->getSessionManager()?->tick();
		}
	}

}