<?php namespace pvp\hud;

use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;

use pvp\PvPPlayer;
use pvp\PvP;

class Hud{

	public $plugin;

	public $huds = [];

	public function __construct(PvP $plugin){
		$this->plugin = $plugin;
	}

	public function send(Player $player) : void {
		/** @var PvPPlayer $player */
		$hud = $this->huds[$player->getName()] = new HudObject($player);
		if(!$player->isFromProxy()){
			$hud->send();
		}else{
			PvP::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function() use($hud) : void{
				$hud->send();
			}), 20);
		}
	}

	public function tick() : void{
		foreach($this->huds as $name => $hud){
			$player = $this->plugin->getServer()->getPlayerExact($name);
			if($player instanceof Player && $player->isConnected()){
				$hud->update();
			}else{
				unset($this->huds[$name]);
			}
		}
	}

}