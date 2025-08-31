<?php namespace pvp\combat\utils;

use pocketmine\Server;
use pocketmine\player\Player;

use pvp\PvPPlayer;
use pvp\combat\CombatComponent;

use core\utils\TextFormat;

class ModeManager{

	const DISABLED_COMMANDS = [
		"spawn", "hub", "crates", "reconnect",
		"koth", "arena",
		"island", "is", "i",
	];

	public int $combattime = 0;
	public ?string $hit = null;
	public bool $sendMsg = false;

	public function __construct(public CombatComponent $combatComponent){}

	public function getCombatComponent() : CombatComponent{
		return $this->combatComponent;
	}

	public function tick() : void{
		if($this->inCombat()){
			$this->getCombatComponent()->getPlayer()?->getXpManager()->setXpLevel(max(0, $this->combattime - time()));
			$this->getCombatComponent()->getPlayer()?->getXpManager()->setXpProgress(max(0, $this->combattime - time()) / 10);
		}

		if($this->combattime > 0 && $this->combattime <= time())
			$this->reset();
	}

	public function reset() : void{
		$this->combattime = 0;
		$this->setHit();
		$this->getCombatComponent()->getPlayer()?->getXpManager()->setXpLevel(0);
		$this->getCombatComponent()->getPlayer()?->getXpManager()->setXpProgress(0);
		if($this->sendMsg) $this->getCombatComponent()->getPlayer()?->sendMessage(TextFormat::YI . "You are no longer in combat mode!");
	}

	public function punish() : void{
		/** @var PvPPlayer $player */
		$player = $this->getCombatComponent()->getPlayer();
		$player->takeTechits(50);

		$hit = $this->getHit();
		if($hit instanceof Player){
			$hsession = $hit->getGameSession()->getCombat();
			$hsession->kill($player);
		}
	}

	public function getCombatTime() : int{
		return $this->combattime;
	}

	public function inCombat() : bool{
		return $this->combattime > 0 && $this->getHit() !== null;
	}

	public function setCombat(PvPPlayer $player, bool $sendMsg = true, int $time = 10) : void{
		$this->setHit($player);
		$this->combattime = time() + $time;
		$this->getCombatComponent()->getPlayer()?->getXpManager()->setXpLevel($time);
		$this->getCombatComponent()->getPlayer()?->getXpManager()->setXpProgress(1);
		$this->sendMsg = $sendMsg;
	}

	public function getHit() : ?PvPPlayer{
		return Server::getInstance()->getPlayerExact($this->hit);
	}

	public function setHit(?PvPPlayer $player = null) : void{
		if($player !== null){
			$this->hit = $player->getName();
		}else{
			$this->hit = null;
		}
	}

}